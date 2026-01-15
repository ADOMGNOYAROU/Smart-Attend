<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QRCode extends Model
{
    use HasFactory;

    /**
     * Nom de la table associée au modèle
     *
     * @var string
     */
    protected $table = 'qr_codes';

    protected $fillable = [
        'code',
        'created_by',
        'expires_at',
        'is_used'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean'
    ];

    /**
     * Génère un nouveau code QR unique
     */
    public static function generate($userId, $validityMinutes = 15)
    {
        return self::create([
            'code' => Str::random(32),
            'created_by' => $userId,
            'expires_at' => now()->addMinutes($validityMinutes),
            'is_used' => false
        ]);
    }

    /**
     * Vérifie si le code QR est valide
     */
    public function isValid()
    {
        return !$this->is_used && $this->expires_at->isFuture();
    }

    /**
     * Marque le code comme utilisé
     */
    public function markAsUsed()
    {
        $this->update(['is_used' => true]);
    }

    /**
     * Relation avec l'utilisateur qui a créé le code
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
