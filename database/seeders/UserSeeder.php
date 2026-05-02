<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Nexacore Fatima',
            'email' => 'admin@naissancechain.gn',
            'password' => Hash::make('admin123'),
            'telephone' => '+224626600059',
            'prefecture' => 'Conakry',
            'zone' => 'Kaloum',
            'actif' => true,
        ]);
        $admin->assignRole('ADMIN');

        // Create sample users
        $users = [
            [
                'name' => 'Barry Mamadou',
                'email' => 'barry.mamadou@naissancechain.gn',
                'password' => Hash::make('agent123'),
                'telephone' => '+224620123456',
                'prefecture' => 'Conakry',
                'zone' => 'Dixinn',
                'actif' => true,
                'role' => 'AGENT',
            ],
            [
                'name' => 'Diallo Aïssatou',
                'email' => 'diallo.aissatou@naissancechain.gn',
                'password' => Hash::make('agent123'),
                'telephone' => '+224622234567',
                'prefecture' => 'Conakry',
                'zone' => 'Ratoma',
                'actif' => true,
                'role' => 'SANTE',
            ],
            [
                'name' => 'Bah Oumar',
                'email' => 'bah.oumar@naissancechain.gn',
                'password' => Hash::make('agent123'),
                'telephone' => '+224623345678',
                'prefecture' => 'Kindia',
                'zone' => 'Centre',
                'actif' => true,
                'role' => 'ECOLE',
            ],
            [
                'name' => 'Camara Fatoumata',
                'email' => 'camara.fatoumata@naissancechain.gn',
                'password' => Hash::make('agent123'),
                'telephone' => '+224624456789',
                'prefecture' => 'Labé',
                'zone' => 'Moyenne-Guinée',
                'actif' => true,
                'role' => 'AGENT',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => $userData['password'],
                'telephone' => $userData['telephone'],
                'prefecture' => $userData['prefecture'],
                'zone' => $userData['zone'],
                'actif' => $userData['actif'],
            ]);
            $user->assignRole($userData['role']);
        }

        $this->command->info('Users created successfully.');
    }
}
