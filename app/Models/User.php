<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Les attributs assignables en masse
     * 
     * Ces colonnes peuvent être remplies avec User::create([...])
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'matricule',
        'phone',
        'department',
        'work_start_time',
        'work_end_time',
        'is_active',
        'is_admin',
        'avatar',
    ];
    
    /**
     * Les attributs avec des valeurs par défaut
     *
     * @var array
     */
    protected $attributes = [
        'is_admin' => false,
    ];

    /**
     * Les attributs à cacher (sécurité)
     * 
     * Ces colonnes ne seront JAMAIS envoyées au frontend
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversion automatique des types
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Laravel 10+ : hashe automatiquement
        'work_start_time' => 'datetime:H:i',
        'work_end_time' => 'datetime:H:i',
        'is_admin' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS AVEC LES AUTRES TABLES
    |--------------------------------------------------------------------------
    */

    /**
     * Un utilisateur a plusieurs pointages (attendances)
     * 
     * Relation : 1 user → N attendances
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Un utilisateur a plusieurs demandes de permission
     * 
     * Relation : 1 user → N permissions
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * Les permissions traitées par cet utilisateur (si admin)
     * 
     * Relation : 1 admin → N permissions traitées
     */
    public function processedPermissions()
    {
        return $this->hasMany(Permission::class, 'processed_by');
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTHODES UTILITAIRES
    |--------------------------------------------------------------------------
    */

    /**
     * Vérifier si l'utilisateur est un administrateur
     */
    /**
     * Vérifier si l'utilisateur est un administrateur
     */
    public function isAdmin()
    {
        return $this->is_admin === true || $this->role === 'admin';
    }

    /**
     * Vérifier si l'utilisateur est un contrôleur
     */
    public function isControleur()
    {
        return $this->role === 'controleur';
    }

    /**
     * Vérifier si l'utilisateur est un employé
     */
    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    /**
     * Récupérer le pointage du jour
     */
    public function todayAttendance()
    {
        return $this->attendances()
                    ->whereDate('date', today())
                    ->first();
    }

    /**
     * Vérifier si l'utilisateur a déjà pointé aujourd'hui
     */
    public function hasCheckedInToday(): bool
    {
        return $this->todayAttendance() !== null;
    }

    /**
     * Récupérer les pointages du mois en cours
     */
    public function currentMonthAttendances()
    {
        return $this->attendances()
                    ->whereYear('date', now()->year)
                    ->whereMonth('date', now()->month)
                    ->orderBy('date', 'desc')
                    ->get();
    }

    /**
     * Calculer le nombre de retards dans le mois
     */
    public function lateCountThisMonth(): int
    {
        return $this->attendances()
                    ->whereYear('date', now()->year)
                    ->whereMonth('date', now()->month)
                    ->where('status', 'late')
                    ->count();
    }

    /**
     * Calculer le nombre d'absences dans le mois
     */
    public function absentCountThisMonth(): int
    {
        return $this->attendances()
                    ->whereYear('date', now()->year)
                    ->whereMonth('date', now()->month)
                    ->where('status', 'absent')
                    ->count();
    }
}