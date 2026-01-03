<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLogin()
    {
        // Si l'utilisateur est déjà connecté, le rediriger vers le tableau de bord
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Traiter la tentative de connexion
     */
    public function login(Request $request)
    {
        // Valider les champs du formulaire
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Tenter l'authentification
        if (Auth::attempt($credentials, $request->has('remember'))) {
            // Régénérer la session pour éviter les attaques de fixation de session
            $request->session()->regenerate();
            
            // Définir le temps d'activité initial
            $request->session()->put('last_activity', time());
            
            // Vérifier si l'utilisateur est actif
            if (!Auth::user()->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Votre compte est désactivé. Veuillez contacter l\'administrateur.',
                ]);
            }
            
            // Mettre à jour la dernière connexion
            $user = Auth::user();
            $user->last_login_at = now();
            $user->save();
            
            // Rediriger vers l'URL prévue ou le tableau de bord par défaut
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Connexion réussie !');
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * Déconnecter l'utilisateur
     */
    public function logout(Request $request)
    {
        // Enregistrer l'heure de déconnexion
        if (Auth::check()) {
            $user = Auth::user();
            $user->last_logout_at = now();
            $user->save();
        }
        
        // Déconnecter l'utilisateur
        Auth::logout();
        
        // Invalider complètement la session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Effacer tous les cookies de session
        $cookie = \Cookie::forget(\Illuminate\Session\Middleware\StartSession::class);
        
        // Rediriger vers la page de connexion avec un message
        return redirect()->route('login')
            ->withCookie($cookie)
            ->with('status', 'Vous avez été déconnecté avec succès.');
    }
}
