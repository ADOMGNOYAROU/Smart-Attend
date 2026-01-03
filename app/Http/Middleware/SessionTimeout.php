<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        // Si l'utilisateur est connecté
        if (Auth::check()) {
            $lastActivity = Session::get('last_activity');
            $sessionLifetime = config('session.lifetime') * 60; // en secondes

            // Si la session a expiré
            if (time() - $lastActivity > $sessionLifetime) {
                Auth::logout();
                Session::flush();
                return redirect('/login')->with('session_expired', 'Votre session a expiré. Veuillez vous reconnecter.');
            }

            // Mettre à jour le temps d'activité
            Session::put('last_activity', time());
        }

        return $next($request);
    }
}
