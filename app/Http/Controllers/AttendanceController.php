<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AttendanceController extends Controller
{
    /**
     * Afficher la page de gestion des pointages
     */
    public function index()
    {
        try {
            $user = auth()->user();
            $today = now()->format('Y-m-d');
            
            // Vérifier d'abord en base de données locale
            $todayAttendance = \App\Models\Attendance::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->first();

            // Si pas de pointage local, vérifier dans l'API
            if (!$todayAttendance) {
                $response = Http::withToken(session('api_token'))
                    ->timeout(10) // Timeout réduit à 10 secondes
                    ->get(url('/api/attendance/history'), [
                        'month' => now()->month,
                        'year' => now()->year,
                        'per_page' => 1 // On ne récupère que le dernier pointage
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $latestAttendance = collect($data['data']['attendances'] ?? [])
                        ->first(function ($attendance) use ($today) {
                            return $attendance['date'] === now()->format('d/m/Y');
                        });

                    if ($latestAttendance) {
                        // Créer un enregistrement local à partir des données de l'API
                        $todayAttendance = new \App\Models\Attendance([
                            'user_id' => $user->id,
                            'date' => now()->format('Y-m-d'),
                            'check_in' => $latestAttendance['check_in'],
                            'check_out' => $latestAttendance['check_out'],
                            'status' => $latestAttendance['status']
                        ]);
                        $todayAttendance->save();
                    }
                }
            }

            return view('attendance.index', [
                'todayAttendance' => $todayAttendance
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Pointer l'arrivée
     */
    public function checkIn(Request $request)
    {
        try {
            $user = auth()->user();
            $today = now()->format('Y-m-d');
            
            // Désactiver temporairement la journalisation des requêtes SQL
            \DB::connection()->disableQueryLog();
            
            // Utiliser une transaction pour s'assurer que tout se passe bien
            return \DB::transaction(function () use ($user, $today) {
                // Vérifier s'il y a déjà un pointage aujourd'hui
                $existingAttendance = \App\Models\Attendance::where('user_id', $user->id)
                    ->whereDate('date', $today)
                    ->first();

                if ($existingAttendance) {
                    return redirect()->back()->with('error', 'Vous avez déjà pointé aujourd\'hui.');
                }

                // Créer un nouveau pointage
                $attendance = new \App\Models\Attendance([
                    'user_id' => $user->id,
                    'date' => $today,
                    'check_in' => now(),
                    'status' => 'present'
                ]);
                
                if (!$attendance->save()) {
                    throw new \Exception('Échec de l\'enregistrement du pointage.');
                }

                // Mettre à jour le cache si nécessaire
                \Cache::forget("user_{$user->id}_attendance_{$today}");

                return redirect()->route('dashboard')->with('success', 'Pointage enregistré avec succès !');
            });

        } catch (\Exception $e) {
            \Log::error('Erreur lors du pointage d\'arrivée: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue lors de l\'enregistrement du pointage. Veuillez réessayer.');
        }
    }

    /**
     * Pointer le départ
     */
    public function checkOut(Request $request)
    {
        try {
            $user = auth()->user();
            $today = now()->format('Y-m-d');
            
            // Désactiver temporairement la journalisation des requêtes SQL
            \DB::connection()->disableQueryLog();
            
            // Utiliser une transaction pour s'assurer que tout se passe bien
            return \DB::transaction(function () use ($user, $today) {
                // Vérifier s'il y a un pointage d'arrivée aujourd'hui
                $attendance = \App\Models\Attendance::where('user_id', $user->id)
                    ->whereDate('date', $today)
                    ->first();

                if (!$attendance) {
                    return redirect()->back()->with('error', 'Vous devez d\'abord pointer votre arrivée.');
                }

                // Vérifier si le départ n'a pas déjà été pointé
                if ($attendance->check_out) {
                    return redirect()->back()->with('error', 'Vous avez déjà pointé votre départ aujourd\'hui.');
                }

                // Mettre à jour l'heure de départ
                $attendance->check_out = now();
                
                if (!$attendance->save()) {
                    throw new \Exception('Échec de l\'enregistrement du pointage de départ.');
                }

                // Mettre à jour le cache si nécessaire
                \Cache::forget("user_{$user->id}_attendance_{$today}");

                return redirect()->route('dashboard')
                    ->with('success', 'Pointage de départ enregistré avec succès');
            });

        } catch (\Exception $e) {
            \Log::error('Erreur lors du pointage de départ: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue lors de l\'enregistrement du pointage de départ. Veuillez réessayer.');
        }
    }

    /**
     * Afficher l'historique des pointages
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function history(Request $request)
    {
        try {
            // Vérification de l'authentification
            if (!auth()->check()) {
                return response('Non authentifié', 401);
            }
            
            // Données de test minimales
            $pagination = new \stdClass();
            $pagination->total = 0;
            $pagination->per_page = 10;
            $pagination->current_page = 1;
            $pagination->last_page = 1;
            $pagination->from = 0;
            $pagination->to = 0;

            $data = [
                'attendances' => [],
                'statistics' => (object)[
                    'present_days' => 0,
                    'late_days' => 0,
                    'absent_days' => 0,
                    'total_days' => 0
                ],
                'pagination' => $pagination,
                'month' => now()->month,
                'year' => now()->year,
                'months' => [
                    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                ],
                'years' => range(now()->year - 5, now()->year),
                'currentMonth' => now()->month,
                'currentYear' => now()->year,
                'monthName' => 'Janvier',
                'hasSearched' => false
            ];
            
            // Retourner une réponse JSON pour le débogage
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $data
                ]);
            }
            
            // Retourner la vue avec les données
            return view('attendance.history', $data);

            // Vérifier si la date demandée est dans le futur
            $requestedDate = Carbon::createFromDate($year, $month, 1);
            if ($requestedDate->isFuture()) {
                return redirect()->route('attendance.history', [
                    'month' => now()->month,
                    'year' => now()->year
                ]);
            }

            // Préparation des données pour la vue
            $months = [
                1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
            ];

            $years = range(now()->year - 5, now()->year);
            $currentYear = (int) date('Y');
            if (!in_array($currentYear, $years)) {
                $years[] = $currentYear;
                rsort($years);
            }

            // Récupération des données d'historique depuis l'API avec gestion d'erreur améliorée
            try {
                $response = Http::withToken(session('api_token'))
                    ->timeout(30) // Réduire le timeout à 30 secondes
                    ->retry(2, 100) // Essayer 2 fois avec 100ms d'intervalle
                    ->get(url('/api/attendance/history'), [
                        'month' => $month,
                        'year' => $year,
                        'per_page' => $perPage,
                        'page' => $page
                    ]);

                if (!$response->successful()) {
                    throw new \Exception('Erreur lors de la récupération des données');
                }

                $data = $response->json();
                $pagination = $data['data']['pagination'] ?? null;
                $attendances = $data['data']['attendances'] ?? [];
                $statistics = $data['data']['statistics'] ?? [
                    'present_days' => 0,
                    'late_days' => 0,
                    'absent_days' => 0,
                    'total_days' => 0
                ];
                
            } catch (\Exception $e) {
                // En cas d'erreur, charger uniquement les données essentielles
                $attendances = [];
                $pagination = [
                    'total' => 0,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'last_page' => 1,
                    'from' => 0,
                    'to' => 0
                ];
                $statistics = [
                    'present_days' => 0,
                    'late_days' => 0,
                    'absent_days' => 0,
                    'total_days' => 0
                ];
                
                // Afficher un message d'erreur à l'utilisateur
                $errorMessage = 'Impossible de charger les données. Veuillez réessayer avec une plage de dates plus petite.';
                return view('attendance.history', [
                    'attendances' => $attendances,
                    'statistics' => $statistics,
                    'pagination' => (object)$pagination,
                    'month' => (int) $month,
                    'year' => (int) $year,
                    'months' => $months,
                    'years' => $years,
                    'currentMonth' => now()->month,
                    'currentYear' => now()->year,
                    'monthName' => $months[$month] ?? '',
                    'hasSearched' => $request->has('month') || $request->has('year'),
                    'error' => $errorMessage
                ]);
            }

            return view('attendance.history', [
                'attendances' => $attendances,
                'statistics' => $statistics,
                'pagination' => (object)$pagination,
                'month' => (int) $month,
                'year' => (int) $year,
                'months' => $months,
                'years' => $years,
                'currentMonth' => now()->month,
                'currentYear' => now()->year,
                'monthName' => $months[$month] ?? '',
                'hasSearched' => $request->has('month') || $request->has('year')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('employee.history', [
                'month' => now()->month,
                'year' => now()->year
            ])->withErrors($e->errors());
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }
}
