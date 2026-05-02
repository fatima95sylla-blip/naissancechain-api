<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Création des utilisateurs de test NaissanceChain...\n\n";

try {
    // Suppression utilisateurs existants
    $deleted = DB::table('users')->where('email', 'like', '%@naissancechain.local')->delete();
    echo "Utilisateurs supprimés: $deleted\n\n";

    // Création Admin
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@naissancechain.local',
        'password' => Hash::make('Admin123!'),
        'role' => 'ADMIN',
        'telephone' => '1234567890',
        'prefecture' => 'Abidjan',
        'zone' => 'Zone 1',
        'actif' => true,
    ]);
    echo "✓ Admin créé: admin@naissancechain.local\n";

    // Création Agent
    $agent = User::create([
        'name' => 'Agent Test',
        'email' => 'agent@naissancechain.local',
        'password' => Hash::make('Agent123!'),
        'role' => 'AGENT',
        'telephone' => '1234567891',
        'prefecture' => 'Abidjan',
        'zone' => 'Zone 2',
        'actif' => true,
    ]);
    echo "✓ Agent créé: agent@naissancechain.local\n";

    // Création Ecole
    $ecole = User::create([
        'name' => 'Ecole',
        'email' => 'ecole@naissancechain.local',
        'password' => Hash::make('Ecole123!'),
        'role' => 'ECOLE',
        'telephone' => '1234567892',
        'prefecture' => 'Yamoussoukro',
        'zone' => 'Zone 3',
        'actif' => true,
    ]);
    echo "✓ Ecole créé: ecole@naissancechain.local\n";

    // Création Sante
    $sante = User::create([
        'name' => 'Sante',
        'email' => 'sante@naissancechain.local',
        'password' => Hash::make('Sante123!'),
        'role' => 'SANTE',
        'telephone' => '1234567893',
        'prefecture' => 'Bouake',
        'zone' => 'Zone 4',
        'actif' => true,
    ]);
    echo "✓ Sante créé: sante@naissancechain.local\n";

    echo "\n🎉 Tous les utilisateurs créés avec succès!\n\n";
    echo "📋 Comptes disponibles:\n";
    echo "   Admin: admin@naissancechain.local / Admin123!\n";
    echo "   Agent: agent@naissancechain.local / Agent123!\n";
    echo "   Ecole: ecole@naissancechain.local / Ecole123!\n";
    echo "   Sante: sante@naissancechain.local / Sante123!\n\n";

    // Vérification
    $users = DB::table('users')->where('email', 'like', '%@naissancechain.local')->get();
    echo "📊 Total utilisateurs créés: " . $users->count() . "\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
