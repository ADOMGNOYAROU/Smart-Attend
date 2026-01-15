<?php

namespace App\Http\Controllers;

use App\Models\QRCode;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QRCodeController extends Controller
{
    /**
     * Affiche le formulaire pour générer un code QR
     */
    public function showGenerator()
    {
        return view('qrcode.generate');
    }

    /**
     * Affiche la page de scan d'un code QR
     */
    public function showScan($code)
    {
        $qrCode = QRCode::where('code', $code)->firstOrFail();
        
        return view('qrcode.scan', [
            'qrCode' => $qrCode,
            'isValid' => $qrCode->isValid()
        ]);
    }

    /**
     * Génère un nouveau code QR
     */
    public function generate(Request $request)
    {
        $validity = (int) $request->input('validity', 15); // Conversion en entier pour éviter les erreurs de type
        
        $qrCode = QRCode::generate(Auth::id(), $validity);
        
        // Générer l'URL complète pour scanner le code
        $scanUrl = route('qrcode.scan', $qrCode->code);
        
        return view('qrcode.show', [
            'qrCode' => $qrCode,
            'scanUrl' => $scanUrl,
            'expiresAt' => $qrCode->expires_at->format('H:i:s')
        ]);
    }

    /**
     * Traite le scan d'un code QR (API endpoint pour l'application mobile)
     */
    public function processScan($code, Request $request)
    {
        // Vérifier l'authentification via l'API token
        $user = $request->user() ?? Auth::guard('api')->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié.'
            ], 401);
        }
        
        $qrCode = QRCode::where('code', $code)->first();
        
        if (!$qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Code QR invalide.'
            ], 404);
        }
        
        // Vérifier si le code est toujours valide
        if (!$qrCode->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce code QR a expiré ou a déjà été utilisé.'
            ], 400);
        }
        
        // Vérifier si l'utilisateur a déjà pointé aujourd'hui
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();
        
        $now = now();
        $isCheckIn = true;
        
        if ($attendance) {
            // Si l'utilisateur a déjà pointé aujourd'hui, on enregistre le départ
            if ($attendance->check_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous avez déjà pointé votre départ aujourd\'hui.'
                ], 400);
            }
            
            $attendance->update([
                'check_out' => $now->toTimeString(),
                'status' => $this->determineStatus($user, $attendance->check_in, $now->toTimeString())
            ]);
            
            $isCheckIn = false;
        } else {
            // Sinon, on enregistre l'arrivée
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'check_in' => $now->toTimeString(),
                'status' => $this->determineStatus($user, $now->toTimeString())
            ]);
        }
        
        // Marquer le code comme utilisé
        $qrCode->markAsUsed();
        
        return response()->json([
            'success' => true,
            'message' => $isCheckIn ? 'Pointage d\'arrivée enregistré avec succès.' : 'Pointage de départ enregistré avec succès.',
            'isCheckIn' => $isCheckIn,
            'time' => $now->format('H:i:s'),
            'date' => $now->format('d/m/Y'),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'matricule' => $user->matricule
            ]
        ]);
    }
    
    /**
     * Détermine le statut de présence en fonction de l'heure d'arrivée
     */
    private function determineStatus($user, $checkIn, $checkOut = null)
    {
        $workStart = \Carbon\Carbon::parse($user->work_start_time);
        $checkInTime = \Carbon\Carbon::parse($checkIn);
        
        // Si l'utilisateur arrive après l'heure de début + 15 minutes, il est en retard
        if ($checkInTime->diffInMinutes($workStart, false) < -15) {
            return 'late';
        }
        
        return 'present';
    }
}
