<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Connexion d'un utilisateur
     * 
     * POST /api/auth/login
     * Body : { "email": "...", "password": "..." }
     */
    public function login(Request $request)
    {
        // 1. Valider les données reçues
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // 2. Chercher l'utilisateur par email
        $user = User::where('email', $request->email)->first();

        // 3. Vérifier si l'utilisateur existe et si le mot de passe est correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        // 4. Créer un token d'authentification (badge numérique)
        $token = $user->createToken('auth-token')->plainTextToken;

        // 5. Retourner la réponse avec le token et les infos utilisateur
        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'matricule' => $user->matricule,
                    'department' => $user->department,
                ],
                'token' => $token,
            ],
        ], 200);
    }

    /**
     * Déconnexion de l'utilisateur
     * 
     * POST /api/auth/logout
     * Headers : Authorization: Bearer {token}
     */
    public function logout(Request $request)
    {
        // Supprimer le token actuel de l'utilisateur
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie',
        ], 200);
    }

    /**
     * Récupérer les informations de l'utilisateur connecté
     * 
     * GET /api/auth/user
     * Headers : Authorization: Bearer {token}
     */
    public function user(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'matricule' => $user->matricule,
                'phone' => $user->phone,
                'department' => $user->department,
                'work_start_time' => $user->work_start_time,
                'work_end_time' => $user->work_end_time,
            ],
        ], 200);
    }
}