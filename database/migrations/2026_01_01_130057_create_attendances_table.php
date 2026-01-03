<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            // ID auto-incrémenté
            $table->id();
            
            // Relation avec la table users
            // Si un user est supprimé, ses attendances sont aussi supprimées
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Date du pointage
            $table->date('date');
            
            // Heure d'arrivée (OBLIGATOIRE)
            $table->time('check_in');
            
            // Heure de départ (OPTIONNEL - peut être NULL si pas encore parti)
            $table->time('check_out')->nullable();
            
            // Statut de la présence
            // present = présent à l'heure
            // late = en retard
            // absent = absent
            // justified = absence justifiée
            $table->enum('status', ['present', 'late', 'absent', 'justified'])
                  ->default('present');
            
            // Commentaire (pour les justifications)
            $table->text('comment')->nullable();
            
            // Timestamps automatiques (created_at, updated_at)
            $table->timestamps();
            
            // Index pour optimiser les recherches
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Annuler la migration
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};