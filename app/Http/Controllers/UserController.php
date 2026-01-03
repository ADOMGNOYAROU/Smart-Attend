<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Afficher la liste des utilisateurs
     */
    public function index()
    {
        $users = User::latest()->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Afficher le formulaire de création d'utilisateur
     */
    public function create()
    {
        $roles = [
            'admin' => 'Administrateur',
            'manager' => 'Manager',
            'employee' => 'Employé'
        ];
        
        $departments = [
            'IT' => 'IT',
            'RH' => 'Ressources Humaines',
            'Comptabilité' => 'Comptabilité',
            'Commercial' => 'Commercial',
            'Direction' => 'Direction',
            'Autre' => 'Autre'
        ];
        
        return view('users.create', compact('roles', 'departments'));
    }

    /**
     * Enregistrer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,employee',
            'matricule' => 'required|string|max:50|unique:users',
            'phone' => 'nullable|string|max:20',
            'department' => 'required|string|max:100',
            'work_start_time' => 'required|date_format:H:i',
            'work_end_time' => 'required|date_format:H:i|after:work_start_time',
        ]);

        // Création de l'utilisateur
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'matricule' => $validated['matricule'],
            'phone' => $validated['phone'],
            'department' => $validated['department'],
            'work_start_time' => $validated['work_start_time'],
            'work_end_time' => $validated['work_end_time'],
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Afficher les détails d'un utilisateur
     */
    public function show(User $user)
    {
        $user->loadCount(['attendances', 'permissions']);
        return view('users.show', compact('user'));
    }

    /**
     * Afficher le formulaire d'édition d'un utilisateur
     */
    public function edit(User $user)
    {
        $roles = [
            'admin' => 'Administrateur',
            'manager' => 'Manager',
            'employee' => 'Employé'
        ];
        
        $departments = [
            'IT' => 'IT',
            'RH' => 'Ressources Humaines',
            'Comptabilité' => 'Comptabilité',
            'Commercial' => 'Commercial',
            'Direction' => 'Direction',
            'Autre' => 'Autre'
        ];
        
        return view('users.edit', compact('user', 'roles', 'departments'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,employee',
            'matricule' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'department' => 'required|string|max:100',
            'work_start_time' => 'required|date_format:H:i',
            'work_end_time' => 'required|date_format:H:i|after:work_start_time',
        ]);

        // Mise à jour des informations de base
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'matricule' => $validated['matricule'],
            'phone' => $validated['phone'],
            'department' => $validated['department'],
            'work_start_time' => $validated['work_start_time'],
            'work_end_time' => $validated['work_end_time'],
        ]);

        // Mise à jour du mot de passe si fourni
        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password'])
            ]);
        }

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        // Empêcher la suppression de son propre compte
        if (auth()->id() === $user->id) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Afficher le formulaire de modification du profil
     */
    public function profile()
    {
        $user = auth()->user();
        return view('users.profile', compact('user'));
    }

    /**
     * Mettre à jour le profil de l'utilisateur connecté
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => 'nullable|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Mise à jour des informations de base
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
        ]);

        // Mise à jour du mot de passe si fourni
        if (!empty($validated['new_password'])) {
            $user->update([
                'password' => Hash::make($validated['new_password'])
            ]);
        }

        // Gestion de l'avatar
        if ($request->hasFile('avatar')) {
            // Supprimer l'ancien avatar si existe
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            // Enregistrer le nouvel avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);
        }

        return redirect()
            ->route('profile')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Afficher le tableau de bord de l'utilisateur
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Statistiques pour l'utilisateur
        $stats = [
            'total_attendances' => $user->attendances()->count(),
            'late_count' => $user->attendances()->where('status', 'late')->count(),
            'permissions_pending' => $user->permissions()->where('status', 'pending')->count(),
            'permissions_approved' => $user->permissions()->where('status', 'approved')->count(),
        ];
        
        // Derniers pointages
        $recent_attendances = $user->attendances()
            ->latest('date')
            ->take(5)
            ->get();
            
        // Dernières demandes
        $recent_permissions = $user->permissions()
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('user', 'stats', 'recent_attendances', 'recent_permissions'));
    }
}
