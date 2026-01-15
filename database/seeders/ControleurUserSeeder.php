<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ControleurUserSeeder extends Seeder
{
    /**
     * Exécuter le seeder
     */
    public function run(): void
    {
        // Vérifier si l'utilisateur contrôleur existe déjà
        $existingControleur = User::where('email', 'controleur@smartattend.com')->first();
        
        if (!$existingControleur) {
            // Créer l'utilisateur contrôleur
            $controleur = User::create([
                'name' => 'Contrôleur',
                'email' => 'controleur@smartattend.com',
                'password' => Hash::make('controleur123'),
                'role' => 'controleur',
                'matricule' => 'CTRL' . rand(100, 999),
                'phone' => '+228 90 11 22 33',
                'department' => null,
                'work_start_time' => '08:00:00',
                'work_end_time' => '17:00:00',
                'is_active' => true,
            ]);

            $this->command->info('✅ Utilisateur contrôleur créé avec succès !');
            $this->command->info('🔑 Email : controleur@smartattend.com');
            $this->command->info('🔑 Mot de passe : controleur123');
        } else {
            $this->command->info('ℹ️ Un utilisateur avec l\'email controleur@smartattend.com existe déjà.');
        }
    }
}
