<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Traiter la tentative de connexion
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Tenter de se connecter via l'API
        $response = Http::post(url('/api/auth/login'), [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Stocker le token dans la session
            session(['api_token' => $data['data']['token']]);
            
            // Rediriger vers le tableau de bord
            return redirect()->intended('/dashboard');
        }

        // En cas d'échec de l'authentification
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Déconnecter l'utilisateur
     */
    public function logout(Request $request)
    {
        // Appeler l'API pour se déconnecter
        if (session('api_token')) {
            Http::withToken(session('api_token'))->post(url('/api/auth/logout'));
        }
        
        // Supprimer la session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}
