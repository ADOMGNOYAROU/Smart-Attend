<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                throw new \Exception('Utilisateur non authentifié');
            }

            // Récupérer les données en une seule requête optimisée
            $data = Cache::remember('dashboard_data_' . $user->id, now()->addMinutes(5), function () use ($user) {
                $today = now()->toDateString();
                $monthStart = now()->startOfMonth()->toDateString();
                $monthEnd = now()->endOfMonth()->toDateString();

                // Récupérer le pointage du jour
                $todayAttendance = Attendance::where('user_id', $user->id)
                    ->whereDate('date', $today)
                    ->first();

                // Récupérer l'historique du mois en cours avec pagination
                $history = Attendance::where('user_id', $user->id)
                    ->whereBetween('date', [$monthStart, $monthEnd])
                    ->orderBy('date', 'desc')
                    ->get()
                    ->groupBy(function($date) {
                        return Carbon::parse($date->date)->format('Y-m-d');
                    });

                return [
                    'todayAttendance' => $todayAttendance,
                    'history' => $history
                ];
            });

            return view('dashboard', [
                'user' => $user,
                'todayAttendance' => $data['todayAttendance'],
                'history' => $data['history']
            ]);

        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Session expirée. Veuillez vous reconnecter.');
        }
    }
}
