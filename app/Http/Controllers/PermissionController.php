<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    /**
     * Afficher le formulaire de demande de permission
     */
    public function index()
    {
        return view('permissions.index');
    }

    /**
     * Enregistrer une nouvelle demande de permission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'type' => 'required|in:retard,absence,sortie_anticipee,teletravail,mission_exterieure',
        ]);

        $permission = new Permission([
            'user_id' => Auth::id(),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'type' => $validated['type'],
            'status' => 'pending',
        ]);

        $permission->save();

        return redirect()
            ->route('permissions.my-requests')
            ->with('success', 'Votre demande de permission a été enregistrée avec succès.');
    }

    /**
     * Afficher les demandes de l'utilisateur connecté
     */
    public function myRequests()
    {
        $permissions = Auth::user()->permissions()
            ->orderBy('created_at', 'desc')
            ->paginate(10); // 10 éléments par page

        return view('permissions.my-requests', compact('permissions'));
    }
}
