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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // Ajouter la colonne "role" (admin ou employee)
            $table->enum('role', ['admin', 'employee'])->default('employee');
            
            // Ajouter le matricule (numéro d'employé unique)
            $table->string('matricule', 20)->unique();
            
            // Ajouter le numéro de téléphone (optionnel)
            $table->string('phone', 20)->nullable();
            
            // Ajouter le département
            $table->string('department', 100)->nullable();
            
            // Ajouter les horaires de travail
            $table->time('work_start_time')->default('08:00:00');
            $table->time('work_end_time')->default('17:00:00');
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Annuler la migration
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};