<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PermissionController extends Controller
{
    /**
     * Créer une demande de permission (Employé)
     * 
     * POST /api/permissions
     * Headers : Authorization: Bearer {token}
     * Body : { "type": "retard", "start_date": "...", ... }
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // 1. Valider les données
        $validated = $request->validate([
            'type' => 'required|in:retard,absence,sortie_anticipee,teletravail,mission_exterieure',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10',
            'justification_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // Max 2MB
        ]);

        // 2. Gérer l'upload du fichier justificatif (si fourni)
        $filePath = null;
        if ($request->hasFile('justification_file')) {
            $file = $request->file('justification_file');
            $fileName = time() . '_' . $user->matricule . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('justifications', $fileName, 'public');
        }

        // 3. Créer la demande
        $permission = Permission::create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'justification_file' => $filePath,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Demande de permission créée avec succès.',
            'data' => [
                'permission_id' => $permission->id,
                'type' => $permission->typeLabel(),
                'status' => $permission->statusLabel(),
                'duration' => $permission->duration() . ' jour(s)',
            ],
        ], 201);
    }

    /**
     * Lister les demandes de l'utilisateur connecté (Employé)
     * 
     * GET /api/permissions/my-requests?status=pending
     * Headers : Authorization: Bearer {token}
     */
    public function myRequests(Request $request)
    {
        $user = $request->user();
        $status = $request->input('status'); // Filtre optionnel

        $query = Permission::where('user_id', $user->id);

        if ($status) {
            $query->where('status', $status);
        }

        $permissions = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'type' => $permission->typeLabel(),
                    'start_date' => $permission->start_date->format('d/m/Y H:i'),
                    'end_date' => $permission->end_date->format('d/m/Y H:i'),
                    'duration' => $permission->duration() . ' jour(s)',
                    'reason' => $permission->reason,
                    'status' => $permission->statusLabel(),
                    'has_justification' => $permission->hasJustification(),
                    'admin_comment' => $permission->admin_comment,
                    'created_at' => $permission->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ], 200);
    }

    /**
     * Lister toutes les demandes (Admin uniquement)
     * 
     * GET /api/admin/permissions?status=pending
     * Headers : Authorization: Bearer {token}
     */
    public function index(Request $request)
    {
        // Vérifier que l'utilisateur est admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }

        $status = $request->input('status');

        $query = Permission::with('user'); // Charger la relation user

        if ($status) {
            $query->where('status', $status);
        }

        $permissions = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'employee' => [
                        'id' => $permission->user->id,
                        'name' => $permission->user->name,
                        'matricule' => $permission->user->matricule,
                        'department' => $permission->user->department,
                    ],
                    'type' => $permission->typeLabel(),
                    'start_date' => $permission->start_date->format('d/m/Y H:i'),
                    'end_date' => $permission->end_date->format('d/m/Y H:i'),
                    'duration' => $permission->duration() . ' jour(s)',
                    'reason' => $permission->reason,
                    'status' => $permission->statusLabel(),
                    'has_justification' => $permission->hasJustification(),
                    'justification_url' => $permission->hasJustification() 
                        ? asset('storage/' . $permission->justification_file) 
                        : null,
                    'created_at' => $permission->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ], 200);
    }

    /**
     * Approuver une demande (Admin uniquement)
     * 
     * POST /api/admin/permissions/{id}/approve
     * Headers : Authorization: Bearer {token}
     * Body : { "comment": "..." } (optionnel)
     */
    public function approve(Request $request, $id)
    {
        $admin = $request->user();

        if (!$admin->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }

        $permission = Permission::findOrFail($id);

        if (!$permission->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande a déjà été traitée.',
            ], 400);
        }

        $comment = $request->input('comment');
        $permission->approve($admin, $comment);

        return response()->json([
            'success' => true,
            'message' => 'Demande approuvée avec succès.',
            'data' => [
                'permission_id' => $permission->id,
                'status' => $permission->statusLabel(),
                'processed_by' => $admin->name,
            ],
        ], 200);
    }

    /**
     * Rejeter une demande (Admin uniquement)
     * 
     * POST /api/admin/permissions/{id}/reject
     * Headers : Authorization: Bearer {token}
     * Body : { "comment": "..." } (obligatoire)
     */
    public function reject(Request $request, $id)
    {
        $admin = $request->user();

        if (!$admin->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }

        $validated = $request->validate([
            'comment' => 'required|string|min:5',
        ]);

        $permission = Permission::findOrFail($id);

        if (!$permission->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande a déjà été traitée.',
            ], 400);
        }

        $permission->reject($admin, $validated['comment']);

        return response()->json([
            'success' => true,
            'message' => 'Demande rejetée.',
            'data' => [
                'permission_id' => $permission->id,
                'status' => $permission->statusLabel(),
                'processed_by' => $admin->name,
            ],
        ], 200);
    }
}