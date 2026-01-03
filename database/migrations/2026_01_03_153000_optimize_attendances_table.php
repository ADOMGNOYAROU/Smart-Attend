<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        try {
            // Vérifier si les index existent déjà
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('attendances');
            
            Schema::table('attendances', function (Blueprint $table) use ($indexes) {
                // Ajout d'index manquants
                if (!isset($indexes['attendances_date_index'])) {
                    $table->index('date');
                }
                
                if (!isset($indexes['attendances_user_id_status_index'])) {
                    $table->index(['user_id', 'status']);
                }
                
                if (!isset($indexes['attendances_created_at_index'])) {
                    $table->index('created_at');
                }
            });
            
            // Optimisation de la table (seulement si la table est de type MyISAM)
            $tableType = DB::selectOne("SHOW TABLE STATUS LIKE 'attendances'");
            if (isset($tableType->Engine) && $tableType->Engine === 'MyISAM') {
                DB::statement('OPTIMIZE TABLE `attendances`');
            }
        } catch (\Exception $e) {
            // Journaliser l'erreur mais ne pas arrêter le flux
            \Log::error('Erreur lors de l\'optimisation de la table attendances: ' . $e->getMessage());
        }
    }

    public function down()
    {
        // Ne pas supprimer les index en cas de rollback pour éviter les problèmes
        // Les index seront supprimés automatiquement lors de la suppression de la table
    }
};
