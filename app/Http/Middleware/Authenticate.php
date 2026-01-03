<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Authenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            // Effacer toutes les données de session
            Session::flush();
            Auth::logout();
            
            // Rediriger vers la page de connexion avec un message
            return redirect()->route('login')
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        }

        // Mettre à jour le timestamp de dernière activité
        $request->session()->put('last_activity', time());

        // Vérifier l'inactivité (30 minutes d'inactivité)
        $maxIdleTime = 30 * 60; // 30 minutes en secondes
        $lastActivity = $request->session()->get('last_activity');

        if (time() - $lastActivity > $maxIdleTime) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('error', 'Vous avez été déconnecté en raison d\'une inactivité prolongée.');
        }

        return $next($request);
    }
}