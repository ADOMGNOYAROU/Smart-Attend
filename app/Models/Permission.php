<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Permission extends Model
{
    use HasFactory;

    /**
     * Les attributs assignables en masse
     */
    protected $fillable = [
        'user_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'justification_file',
        'status',
        'admin_comment',
        'processed_by',
        'processed_at',
    ];

    /**
     * Conversion automatique des types
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Une permission appartient à un utilisateur (demandeur)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Une permission est traitée par un admin (optionnel)
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTHODES UTILITAIRES
    |--------------------------------------------------------------------------
    */

    /**
     * Vérifier si la permission est en attente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Vérifier si la permission est approuvée
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Vérifier si la permission est rejetée
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approuver la permission
     */
    public function approve(User $admin, ?string $comment = null): void
    {
        $this->update([
            'status' => 'approved',
            'processed_by' => $admin->id,
            'processed_at' => now(),
            'admin_comment' => $comment,
        ]);
    }

    /**
     * Rejeter la permission
     */
    public function reject(User $admin, ?string $comment = null): void
    {
        $this->update([
            'status' => 'rejected',
            'processed_by' => $admin->id,
            'processed_at' => now(),
            'admin_comment' => $comment,
        ]);
    }

    /**
     * Calculer la durée de la permission (en jours)
     */
    public function duration(): int
    {
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);

        return $start->diffInDays($end) + 1; // +1 pour inclure le dernier jour
    }

    /**
     * Obtenir le libellé du type de permission
     */
    public function typeLabel(): string
    {
        return match($this->type) {
            'retard' => 'Retard',
            'absence' => 'Absence',
            'sortie_anticipee' => 'Sortie anticipée',
            'teletravail' => 'Télétravail',
            'mission_exterieure' => 'Mission extérieure',
            default => 'Autre',
        };
    }

    /**
     * Obtenir le libellé du statut
     */
    public function statusLabel(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'approved' => 'Approuvée',
            'rejected' => 'Rejetée',
            default => 'Inconnu',
        };
    }

    /**
     * Vérifier si un fichier justificatif est fourni
     */
    public function hasJustification(): bool
    {
        return $this->justification_file !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope : Seulement les demandes en attente
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope : Seulement les demandes approuvées
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope : Seulement les demandes rejetées
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope : Filtrer par utilisateur
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope : Filtrer par période
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }
}