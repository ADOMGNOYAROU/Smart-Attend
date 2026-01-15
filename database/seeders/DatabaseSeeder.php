<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Exécuter les seeders de l'application
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            ControleurUserSeeder::class,
            // Ajoutez d'autres seeders ici si nécessaire
        ]);
    }
}