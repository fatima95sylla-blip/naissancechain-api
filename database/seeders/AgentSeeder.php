<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin agent
        Agent::create([
            'nom' => 'Nexacore',
            'prenom' => 'Fatima',
            'email' => 'admin@naissancechain.gn',
            'password' => Hash::make('admin123'),
            'telephone' => '+224626600059',
            'prefecture' => 'Conakry',
            'zone' => 'Kaloum',
            'role' => 'admin',
            'actif' => true,
        ]);

        // Create sample agents
        $agents = [
            [
                'nom' => 'Barry',
                'prenom' => 'Mamadou',
                'email' => 'barry.mamadou@naissancechain.gn',
                'password' => Hash::make('agent123'),
                'telephone' => '+224620123456',
                'prefecture' => 'Conakry',
                'zone' => 'Dixinn',
                'role' => 'agent',
                'actif' => true,
            ],
            [
                'nom' => 'Diallo',
                'prenom' => 'Aïssatou',
                'email' => 'diallo.aissatou@naissancechain.gn',
                'password' => Hash::make('agent123'),
                'telephone' => '+224622234567',
                'prefecture' => 'Conakry',
                'zone' => 'Ratoma',
                'role' => 'agent',
                'actif' => true,
            ],
            [
                'nom' => 'Bah',
                'prenom' => 'Oumar',
                'email' => 'bah.oumar@naissancechain.gn',
                'password' => Hash::make('agent123'),
                'telephone' => '+224623345678',
                'prefecture' => 'Kindia',
                'zone' => 'Centre',
                'role' => 'agent',
                'actif' => true,
            ],
            [
                'nom' => 'Camara',
                'prenom' => 'Fatoumata',
                'email' => 'camara.fatoumata@naissancechain.gn',
                'password' => Hash::make('agent123'),
                'telephone' => '+224624456789',
                'prefecture' => 'Labé',
                'zone' => 'Moyenne-Guinée',
                'role' => 'agent',
                'actif' => true,
            ],
        ];

        foreach ($agents as $agent) {
            Agent::create($agent);
        }
    }
}
