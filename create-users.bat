@echo off
echo ========================================
echo Creation Utilisateurs de Test NaissanceChain
echo ========================================
echo.

echo [1/4] Verification de la base de donnees...
php artisan tinker --execute="
try {
    \$count = DB::table('users')->count();
    echo 'Users actuels: ' . \$count . PHP_EOL;
} catch (Exception \$e) {
    echo 'Erreur de connexion DB: ' . \$e->getMessage() . PHP_EOL;
}
"

echo.
echo [2/4] Suppression des utilisateurs existants...
php artisan tinker --execute="
try {
    \$deleted = DB::table('users')->where('email', 'like', '%@naissancechain.local')->delete();
    echo 'Utilisateurs supprimes: ' . \$deleted . PHP_EOL;
} catch (Exception \$e) {
    echo 'Erreur suppression: ' . \$e->getMessage() . PHP_EOL;
}
"

echo.
echo [3/4] Creation des utilisateurs de test...
php artisan tinker --execute="
use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Admin
    \$admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@naissancechain.local',
        'password' => Hash::make('Admin123!'),
        'role' => 'ADMIN',
        'telephone' => '1234567890',
        'prefecture' => 'Abidjan',
        'zone' => 'Zone 1',
        'actif' => true,
    ]);
    echo 'Admin créé: admin@naissancechain.local' . PHP_EOL;

    // Agent
    \$agent = User::create([
        'name' => 'Agent Test',
        'email' => 'agent@naissancechain.local',
        'password' => Hash::make('Agent123!'),
        'role' => 'AGENT',
        'telephone' => '1234567891',
        'prefecture' => 'Abidjan',
        'zone' => 'Zone 2',
        'actif' => true,
    ]);
    echo 'Agent créé: agent@naissancechain.local' . PHP_EOL;

    // Ecole
    \$ecole = User::create([
        'name' => 'Ecole',
        'email' => 'ecole@naissancechain.local',
        'password' => Hash::make('Ecole123!'),
        'role' => 'ECOLE',
        'telephone' => '1234567892',
        'prefecture' => 'Yamoussoukro',
        'zone' => 'Zone 3',
        'actif' => true,
    ]);
    echo 'Ecole créé: ecole@naissancechain.local' . PHP_EOL;

    // Sante
    \$sante = User::create([
        'name' => 'Sante',
        'email' => 'sante@naissancechain.local',
        'password' => Hash::make('Sante123!'),
        'role' => 'SANTE',
        'telephone' => '1234567893',
        'prefecture' => 'Bouake',
        'zone' => 'Zone 4',
        'actif' => true,
    ]);
    echo 'Sante créé: sante@naissancechain.local' . PHP_EOL;

    echo PHP_EOL . 'Tous les utilisateurs créés avec succès!' . PHP_EOL;

} catch (Exception \$e) {
    echo 'Erreur création: ' . \$e->getMessage() . PHP_EOL;
}
"

echo.
echo [4/4] Verification finale...
php artisan tinker --execute="
\$users = DB::table('users')->where('email', 'like', '%@naissancechain.local')->get();
echo 'Utilisateurs créés:' . PHP_EOL;
foreach (\$users as \$user) {
    echo '- ' . \$user->email . ' (' . \$user->role . ')' . PHP_EOL;
}
echo PHP_EOL . 'Total: ' . \$users->count() . ' utilisateurs' . PHP_EOL;
"

echo.
echo ========================================
echo Utilisateurs créés avec succès!
echo ========================================
echo.
echo Comptes disponibles:
echo Admin:    admin@naissancechain.local / Admin123!
echo Agent:    agent@naissancechain.local / Agent123!
echo Ecole:    ecole@naissancechain.local / Ecole123!
echo Sante:    sante@naissancechain.local / Sante123!
echo.
echo Testez maintenant avec Postman ou curl!
pause
