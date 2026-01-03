<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Pointer l'arrivée
     * 
     * POST /api/attendance/check-in
     * Headers : Authorization: Bearer {token}
     */
    public function checkIn(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        // 1. Vérifier si l'utilisateur a déjà pointé aujourd'hui
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà pointé votre arrivée aujourd\'hui.',
                'data' => [
                    'check_in' => $existingAttendance->check_in,
                ],
            ], 400); // 400 = Bad Request
        }

        // 2. Récupérer l'heure actuelle du serveur
        $checkInTime = Carbon::now();

        // 3. Déterminer le statut (présent ou en retard)
        $workStartTime = Carbon::parse($user->work_start_time);
        $toleranceMinutes = 5; // Tolérance de 5 minutes

        $status = $checkInTime->greaterThan($workStartTime->addMinutes($toleranceMinutes))
            ? 'late'
            : 'present';

        // 4. Créer le pointage
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in' => $checkInTime->format('H:i:s'),
            'status' => $status,
        ]);

        // 5. Calculer les minutes de retard (si applicable)
        $lateMinutes = $status === 'late' ? $attendance->lateMinutes() : 0;

        // 6. Retourner la réponse
        return response()->json([
            'success' => true,
            'message' => $status === 'late' 
                ? "Arrivée enregistrée avec {$lateMinutes} minutes de retard."
                : 'Arrivée enregistrée avec succès.',
            'data' => [
                'attendance_id' => $attendance->id,
                'check_in' => $attendance->check_in,
                'status' => $status,
                'late_minutes' => $lateMinutes,
            ],
        ], 201); // 201 = Created
    }

    /**
     * Pointer le départ
     * 
     * POST /api/attendance/check-out
     * Headers : Authorization: Bearer {token}
     */
    public function checkOut(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        // 1. Vérifier si l'utilisateur a pointé son arrivée aujourd'hui
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez d\'abord pointer votre arrivée.',
            ], 400);
        }

        // 2. Vérifier si le départ n'a pas déjà été pointé
        if ($attendance->check_out) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà pointé votre départ aujourd\'hui.',
                'data' => [
                    'check_out' => $attendance->check_out,
                ],
            ], 400);
        }

        // 3. Enregistrer l'heure de départ
        $checkOutTime = Carbon::now();
        $attendance->update([
            'check_out' => $checkOutTime->format('H:i:s'),
        ]);

        // 4. Calculer la durée de travail
        $workDuration = $attendance->workDuration();
        $formattedDuration = $attendance->formattedWorkDuration();

        // 5. Retourner la réponse
        return response()->json([
            'success' => true,
            'message' => 'Départ enregistré avec succès.',
            'data' => [
                'attendance_id' => $attendance->id,
                'check_in' => $attendance->check_in,
                'check_out' => $attendance->check_out,
                'work_duration' => $formattedDuration,
                'work_duration_hours' => round($workDuration, 2),
            ],
        ], 200);
    }

    /**
     * Récupérer l'historique de présence de l'utilisateur connecté
     * 
     * GET /api/attendance/history?month=1&year=2024
     * Headers : Authorization: Bearer {token}
     */
    public function history(Request $request)
    {
        $user = $request->user();
        
        // Paramètres optionnels (mois et année)
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $perPage = $request->input('per_page', 15); // 15 éléments par page par défaut

        // Calculer la date de début et de fin pour le mois demandé
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        // Utiliser une seule requête pour les statistiques et la pagination
        $query = \DB::table('attendances')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate]);

        // Sous-requête pour les statistiques
        $stats = (clone $query)
            ->selectRaw('COUNT(*) as total_days')
            ->selectRaw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0) as present_days')
            ->selectRaw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0) as late_days')
            ->selectRaw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0) as absent_days')
            ->first();

        // Pagination manuelle pour de meilleures performances
        $total = (clone $query)->count();
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        
        $attendances = $query->orderBy('date', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->map(function ($attendance) {
                $attendance->date = \Carbon\Carbon::parse($attendance->date)->format('d/m/Y');
                $attendance->work_duration = $this->formatWorkDuration($attendance->check_in, $attendance->check_out);
                $attendance->late_minutes = $attendance->status === 'late' ? $this->calculateLateMinutes($attendance->check_in) : 0;
                return $attendance;
            });

        $lastPage = ceil($total / $perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'attendances' => $attendances,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => (int)$page,
                    'last_page' => $lastPage,
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total),
                ],
                'statistics' => [
                    'total_days' => (int) ($stats->total_days ?? 0),
                    'present_days' => (int) ($stats->present_days ?? 0),
                    'late_days' => (int) ($stats->late_days ?? 0),
                    'absent_days' => (int) ($stats->absent_days ?? 0),
                ],
                'period' => [
                    'month' => $month,
                    'year' => $year,
                ],
            ],
        ], 200);
    }

    /**
     * Formate la durée de travail
     */
    private function formatWorkDuration($checkIn, $checkOut)
    {
        if (empty($checkIn) || empty($checkOut)) {
            return '--:--';
        }

        $start = \Carbon\Carbon::parse($checkIn);
        $end = \Carbon\Carbon::parse($checkOut);
        $diff = $start->diff($end);
        
        return sprintf('%dh %02dmin', $diff->h, $diff->i);
    }

    /**
     * Calcule les minutes de retard
     */
    private function calculateLateMinutes($checkIn)
    {
        if (empty($checkIn)) {
            return 0;
        }

        $checkInTime = \Carbon\Carbon::parse($checkIn);
        $workStartTime = \Carbon\Carbon::parse('09:00:00');
        
        if ($checkInTime->greaterThan($workStartTime)) {
            return $checkInTime->diffInMinutes($workStartTime);
        }
        
        return 0;
    }

    /**
     * Récupérer le pointage du jour de l'utilisateur
     * 
     * GET /api/attendance/today
     * Headers : Authorization: Bearer {token}
     */
    public function today(Request $request)
    {
        $user = $request->user();
        $attendance = $user->todayAttendance();

        if (!$attendance) {
            return response()->json([
                'success' => true,
                'message' => 'Aucun pointage aujourd\'hui.',
                'data' => null,
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $attendance->id,
                'date' => $attendance->date->format('d/m/Y'),
                'check_in' => $attendance->check_in,
                'check_out' => $attendance->check_out,
                'status' => $attendance->status,
                'has_checked_out' => $attendance->hasCheckedOut(),
                'work_duration' => $attendance->formattedWorkDuration(),
            ],
        ], 200);
    }
}