<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    /**
     * Afficher la liste des demandes de permission en attente
     */
    public function index()
    {
        $permissions = Permission::where('status', 'pending')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Afficher une demande spécifique
     */
    public function show(string $id)
    {
        $permission = Permission::with('user')->findOrFail($id);
        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Traiter la réponse de l'administrateur (approuver/rejeter)
     */
    public function respond(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_response' => 'required_if:status,rejected|string|max:1000|nullable'
        ]);

        $permission = Permission::findOrFail($id);
        
        $permission->update([
            'status' => $request->status,
            'admin_response' => $request->admin_response,
            'processed_by' => Auth::id(),
            'processed_at' => now()
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'La demande a été traitée avec succès.');
    }

    /**
     * Afficher l'historique des demandes traitées
     */
    public function history()
    {
        $permissions = Permission::whereIn('status', ['approved', 'rejected'])
            ->with(['user', 'processedBy'])
            ->orderBy('processed_at', 'desc')
            ->paginate(15);

        return view('admin.permissions.history', compact('permissions'));
    }
}
