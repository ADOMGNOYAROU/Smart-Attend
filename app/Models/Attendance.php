<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Les attributs assignables en masse
     */
    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'comment',
    ];

    /**
     * Conversion automatique des types
     */
    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime:H:i',
        'check_out' => 'datetime:H:i',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Un pointage appartient à un utilisateur
     * 
     * Relation inverse : N attendances → 1 user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTHODES UTILITAIRES
    |--------------------------------------------------------------------------
    */

    /**
     * Vérifier si l'employé a pointé la sortie
     */
    public function hasCheckedOut(): bool
    {
        return $this->check_out !== null;
    }

    /**
     * Calculer la durée de travail (en heures)
     */
    public function workDuration(): ?float
    {
        if (!$this->hasCheckedOut()) {
            return null; // Pas encore sorti
        }

        $checkIn = Carbon::parse($this->check_in);
        $checkOut = Carbon::parse($this->check_out);

        // Différence en heures (avec décimales)
        return $checkOut->diffInHours($checkIn, true);
    }

    /**
     * Déterminer si l'employé est en retard
     * Basé sur l'heure de début de travail de l'utilisateur
     */
    public function isLate(): bool
    {
        $user = $this->user;
        $workStartTime = Carbon::parse($user->work_start_time);
        $checkInTime = Carbon::parse($this->check_in);

        // Retard si arrivée après l'heure de début + 5 minutes de tolérance
        return $checkInTime->greaterThan($workStartTime->addMinutes(5));
    }

    /**
     * Calculer les minutes de retard
     */
    public function lateMinutes(): int
    {
        if (!$this->isLate()) {
            return 0;
        }

        $user = $this->user;
        $workStartTime = Carbon::parse($user->work_start_time);
        $checkInTime = Carbon::parse($this->check_in);

        return $checkInTime->diffInMinutes($workStartTime);
    }

    /**
     * Formater la durée de travail en "Xh Ymin"
     */
    public function formattedWorkDuration(): string
    {
        $duration = $this->workDuration();
        
        if ($duration === null) {
            return 'En cours';
        }

        $hours = floor($duration);
        $minutes = round(($duration - $hours) * 60);

        return "{$hours}h {$minutes}min";
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES (Filtres réutilisables)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope : Filtrer par date
     * 
     * Usage : Attendance::forDate('2024-01-15')->get()
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope : Filtrer par mois
     * 
     * Usage : Attendance::forMonth(1, 2024)->get()
     */
    public function scopeForMonth($query, $month, $year)
    {
        return $query->whereYear('date', $year)
                     ->whereMonth('date', $month);
    }

    /**
     * Scope : Seulement les retards
     * 
     * Usage : Attendance::onlyLate()->get()
     */
    public function scopeOnlyLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope : Seulement les absences
     */
    public function scopeOnlyAbsent($query)
    {
        return $query->where('status', 'absent');
    }
}