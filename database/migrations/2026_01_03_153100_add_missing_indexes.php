<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        try {
            // Vérifier si la colonne date existe et ajouter un index si nécessaire
            if (Schema::hasColumn('attendances', 'date')) {
                DB::statement('CREATE INDEX IF NOT EXISTS attendances_date_index ON attendances (date)');
            }

            // Vérifier si les colonnes user_id et status existent et ajouter un index composite
            if (Schema::hasColumns('attendances', ['user_id', 'status'])) {
                DB::statement('CREATE INDEX IF NOT EXISTS attendances_user_id_status_index ON attendances (user_id, status)');
            }

            // Ajouter un index sur created_at s'il n'existe pas
            if (Schema::hasColumn('attendances', 'created_at')) {
                DB::statement('CREATE INDEX IF NOT EXISTS attendances_created_at_index ON attendances (created_at)');
            }
        } catch (\Exception $e) {
            // Journaliser l'erreur mais ne pas arrêter le flux
            \Log::error('Erreur lors de l\'ajout des index: ' . $e->getMessage());
        }
    }

    public function down()
    {
        // Ne rien faire en cas de rollback pour éviter les problèmes
        // Les index peuvent être supprimés manuellement si nécessaire
    }
};
