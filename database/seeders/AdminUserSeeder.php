<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Exécuter le seeder
     */
    public function run(): void
    {
        // Créer l'administrateur
        $admin = User::create([
            'name' => 'Administrateur',
            'email' => 'admin@smartattend.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'matricule' => 'ADM' . rand(100, 999),
            'phone' => '+228 90 00 00 00',
            'department' => 'Direction',
            'work_start_time' => '08:00:00',
            'work_end_time' => '17:00:00',
            'is_active' => true,
        ]);

        // Créer des utilisateurs de test
        $departments = ['Informatique', 'Ressources Humaines', 'Comptabilité', 'Commercial', 'Marketing'];
        
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => 'Employé ' . $i,
                'email' => 'employe' . $i . '@smartattend.com',
                'password' => Hash::make('password123'),
                'role' => 'employee',
                'matricule' => 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'phone' => '+228 90 ' . str_pad(rand(10, 99), 2, '0') . ' ' . str_pad(rand(10, 99), 2, '0') . ' ' . str_pad(rand(10, 99), 2, '0'),
                'department' => $departments[array_rand($departments)],
                'work_start_time' => '08:00:00',
                'work_end_time' => '17:00:00',
                'is_active' => true,
            ]);
        }

        $this->command->info('✅ 1 administrateur et 5 employés créés avec succès !');
        $this->command->info('🔑 Email admin : admin@smartattend.com');
        $this->command->info('🔑 Mot de passe : password123');
    }
}
