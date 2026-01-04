<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    /**
     * Afficher la page de test
     */
    public function testAuth()
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non authentifié',
                'user' => null
            ]);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur authentifié avec succès',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin ?? false
            ]
        ]);
    }
}
