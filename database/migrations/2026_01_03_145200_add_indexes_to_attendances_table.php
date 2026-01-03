<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Ajout d'index pour améliorer les performances des requêtes
        Schema::table('attendances', function (Blueprint $table) {
            // Index pour les requêtes de recherche par user_id, date, et statut
            $table->index(['user_id', 'date']);
            $table->index('status');
            $table->index('check_in');
            $table->index('check_out');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['check_in']);
            $table->dropIndex(['check_out']);
        });
    }
};
