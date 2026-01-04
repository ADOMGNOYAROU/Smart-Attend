<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->text('admin_response')->nullable()->after('status');
        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('admin_response');
        });
    }
};
