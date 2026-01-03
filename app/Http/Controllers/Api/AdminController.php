<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Récupérer les statistiques du tableau de bord admin
     * 
     * GET /api/admin/dashboard
     */
    public function dashboard()
    {
        $today = Carbon::today();
        
        // Statistiques globales
        $stats = [
            'total_employees' => User::where('role', 'employee')->count(),
            'present_today' => Attendance::whereDate('date', $today)
                ->where('status', 'present')
                ->distinct('user_id')
                ->count('user_id'),
            'late_today' => Attendance::whereDate('date', $today)
                ->where('status', 'late')
                ->distinct('user_id')
                ->count('user_id'),
            'absent_today' => User::where('role', 'employee')
                ->whereDoesntHave('attendances', function($query) use ($today) {
                    $query->whereDate('date', $today);
                })
                ->count(),
            'pending_permissions' => \App\Models\Permission::where('status', 'pending')->count(),
        ];

        // Derniers pointages
        $recent_attendances = Attendance::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function($attendance) {
                return [
                    'id' => $attendance->id,
                    'user' => $attendance->user->name,
                    'date' => $attendance->date->format('d/m/Y'),
                    'check_in' => $attendance->check_in,
                    'check_out' => $attendance->check_out,
                    'status' => $attendance->status,
                ];
            });

        // Statistiques des présences des 7 derniers jours
        $attendance_stats = Attendance::select(
                DB::raw('DATE(date) as date'),
                DB::raw('count(case when status = "present" then 1 end) as present'),
                DB::raw('count(case when status = "late" then 1 end) as late'),
                DB::raw('count(case when status = "absent" then 1 end) as absent')
            )
            ->where('date', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_attendances' => $recent_attendances,
                'attendance_stats' => $attendance_stats,
            ]
        ]);
    }

    /**
     * Récupérer la liste des pointages avec filtres
     * 
     * GET /api/admin/attendances?start_date=...&end_date=...&user_id=...
     */
    public function attendances(Request $request)
    {
        $query = Attendance::with('user')
            ->when($request->start_date, function($q) use ($request) {
                return $q->whereDate('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function($q) use ($request) {
                return $q->whereDate('date', '<=', $request->end_date);
            })
            ->when($request->user_id, function($q) use ($request) {
                return $q->where('user_id', $request->user_id);
            })
            ->orderBy('date', 'desc')
            ->orderBy('check_in', 'desc');

        $attendances = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $attendances
        ]);
    }

    /**
     * Générer un rapport pour un employé
     * 
     * GET /api/admin/reports/employee/{userId}
     */
    public function employeeReport($userId)
    {
        $user = User::findOrFail($userId);
        
        $report = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'matricule' => $user->matricule,
                'department' => $user->department,
            ],
            'stats' => [
                'total_days' => $user->attendances()->count(),
                'present_days' => $user->attendances()->where('status', 'present')->count(),
                'late_days' => $user->attendances()->where('status', 'late')->count(),
                'absent_days' => $user->attendances()->where('status', 'absent')->count(),
                'average_work_hours' => $user->attendances()
                    ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, check_in, check_out)) as avg_hours'))
                    ->whereNotNull('check_out')
                    ->value('avg_hours'),
            ],
            'recent_permissions' => $user->permissions()
                ->latest()
                ->take(5)
                ->get()
                ->map(function($permission) {
                    return [
                        'type' => $permission->type,
                        'start_date' => $permission->start_date->format('d/m/Y H:i'),
                        'end_date' => $permission->end_date->format('d/m/Y H:i'),
                        'status' => $permission->status,
                        'reason' => $permission->reason,
                    ];
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $report
        ]);
    }
}