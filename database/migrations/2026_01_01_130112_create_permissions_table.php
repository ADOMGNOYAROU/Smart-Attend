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
        Schema::create('permissions', function (Blueprint $table) {
            // ID auto-incrémenté
            $table->id();
            
            // Relation avec la table users
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Type de permission
            // retard = arrivée tardive
            // absence = absence d'une journée
            // sortie_anticipee = partir plus tôt
            // teletravail = travail à distance
            // mission_exterieure = déplacement professionnel
            $table->enum('type', [
                'retard',
                'absence',
                'sortie_anticipee',
                'teletravail',
                'mission_exterieure'
            ]);
            
            // Date et heure de début
            $table->dateTime('start_date');
            
            // Date et heure de fin
            $table->dateTime('end_date');
            
            // Motif de la demande (OBLIGATOIRE)
            $table->text('reason');
            
            // Chemin vers le fichier justificatif (OPTIONNEL)
            // Exemple : /storage/justifications/certificat_medical.pdf
            $table->string('justification_file')->nullable();
            
            // Statut de la demande
            // pending = en attente
            // approved = acceptée
            // rejected = rejetée
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending');
            
            // Commentaire de l'administrateur
            $table->text('admin_comment')->nullable();
            
            // ID de l'admin qui a traité la demande
            $table->foreignId('processed_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            
            // Date de traitement de la demande
            $table->timestamp('processed_at')->nullable();
            
            // Timestamps automatiques
            $table->timestamps();
            
            // Index pour optimiser les recherches
            $table->index(['user_id', 'status']);
            $table->index('start_date');
        });
    }

    /**
     * Annuler la migration
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};