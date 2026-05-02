<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Supprimer les utilisateurs existants
        User::where('email', 'like', '%@naissancechain.local')->delete();

        // Créer les utilisateurs de test
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@naissancechain.local',
                'password' => Hash::make('Admin123!'),
                'role' => 'ADMIN',
                'telephone' => '1234567890',
                'prefecture' => 'Abidjan',
                'zone' => 'Zone 1',
                'actif' => true,
            ],
            [
                'name' => 'Agent Test',
                'email' => 'agent@naissancechain.local',
                'password' => Hash::make('Agent123!'),
                'role' => 'AGENT',
                'telephone' => '1234567891',
                'prefecture' => 'Abidjan',
                'zone' => 'Zone 2',
                'actif' => true,
            ],
            [
                'name' => 'Ecole',
                'email' => 'ecole@naissancechain.local',
                'password' => Hash::make('Ecole123!'),
                'role' => 'ECOLE',
                'telephone' => '1234567892',
                'prefecture' => 'Yamoussoukro',
                'zone' => 'Zone 3',
                'actif' => true,
            ],
            [
                'name' => 'Sante',
                'email' => 'sante@naissancechain.local',
                'password' => Hash::make('Sante123!'),
                'role' => 'SANTE',
                'telephone' => '1234567893',
                'prefecture' => 'Bouake',
                'zone' => 'Zone 4',
                'actif' => true,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
            echo "Utilisateur créé: {$user['email']} ({$user['role']})" . PHP_EOL;
        }

        echo PHP_EOL . "Utilisateurs de test créés avec succès!" . PHP_EOL;
    }
}
