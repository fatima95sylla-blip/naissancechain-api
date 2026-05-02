@echo off
echo ========================================
echo Correction Complete Backend NaissanceChain
echo ========================================
echo.

echo [1/8] Verification WAMP64...
echo Verification des services WAMP64...
tasklist | findstr wampapache >nul
if %errorlevel% equ 0 (
    echo ✓ Apache est en cours d'execution
) else (
    echo ✗ Apache n'est pas demarre
    echo Demarrage de WAMP64...
    echo.
    echo Manuellement:
    echo 1. Ouvrir l'interface WAMP64
    echo 2. Clic sur "Start All Services"
    echo 3. Attendre que l'icone devienne verte
    echo.
    pause
)

tasklist | findstr mysql >nul
if %errorlevel% equ 0 (
    echo ✓ MySQL est en cours d'execution
) else (
    echo ✗ MySQL n'est pas demarre
    echo Demarrage de WAMP64 requis
)

echo.
echo [2/8] Verification de la base de donnees...
php artisan tinker --execute="
try {
    DB::connection()->getPdo();
    echo '✓ Base de donnees accessible' . PHP_EOL;
} catch (Exception \$e) {
    echo '✗ Erreur base de donnees: ' . \$e->getMessage() . PHP_EOL;
    echo 'Solution: Demarrer WAMP64' . PHP_EOL;
}
"

echo.
echo [3/8] Creation des utilisateurs de test...
php artisan tinker --execute="
use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Suppression utilisateurs existants
    User::where('email', 'like', '%@naissancechain.local')->delete();
    
    // Admin
    User::create([
        'name' => 'Admin',
        'email' => 'admin@naissancechain.local',
        'password' => Hash::make('Admin123!'),
        'role' => 'ADMIN',
        'telephone' => '1234567890',
        'prefecture' => 'Abidjan',
        'zone' => 'Zone 1',
        'actif' => true,
    ]);
    echo '✓ Admin créé: admin@naissancechain.local' . PHP_EOL;
    
    // Agent
    User::create([
        'name' => 'Agent Test',
        'email' => 'agent@naissancechain.local',
        'password' => Hash::make('Agent123!'),
        'role' => 'AGENT',
        'telephone' => '1234567891',
        'prefecture' => 'Abidjan',
        'zone' => 'Zone 2',
        'actif' => true,
    ]);
    echo '✓ Agent créé: agent@naissancechain.local' . PHP_EOL;
    
    // Ecole
    User::create([
        'name' => 'Ecole',
        'email' => 'ecole@naissancechain.local',
        'password' => Hash::make('Ecole123!'),
        'role' => 'ECOLE',
        'telephone' => '1234567892',
        'prefecture' => 'Yamoussoukro',
        'zone' => 'Zone 3',
        'actif' => true,
    ]);
    echo '✓ Ecole créé: ecole@naissancechain.local' . PHP_EOL;
    
    // Sante
    User::create([
        'name' => 'Sante',
        'email' => 'sante@naissancechain.local',
        'password' => Hash::make('Sante123!'),
        'role' => 'SANTE',
        'telephone' => '1234567893',
        'prefecture' => 'Bouake',
        'zone' => 'Zone 4',
        'actif' => true,
    ]);
    echo '✓ Sante créé: sante@naissancechain.local' . PHP_EOL;
    
    echo '✓ Tous les utilisateurs créés avec succès!' . PHP_EOL;
    
} catch (Exception \$e) {
    echo '✗ Erreur création utilisateurs: ' . \$e->getMessage() . PHP_EOL;
}
"

echo.
echo [4/8] Verification des utilisateurs...
php artisan tinker --execute="
\$users = DB::table('users')->where('email', 'like', '%@naissancechain.local')->get();
echo 'Utilisateurs créés:' . PHP_EOL;
foreach (\$users as \$user) {
    echo '- ' . \$user->email . ' (' . \$user->role . ')' . PHP_EOL;
}
echo 'Total: ' . \$users->count() . ' utilisateurs' . PHP_EOL;
"

echo.
echo [5/8] Configuration Apache VirtualHost...
set APACHE_CONF=C:\wamp64\bin\apache\apache2.4.54\conf\extra\httpd-vhosts.conf
if exist "%APACHE_CONF%" (
    echo Configuration Apache trouve: %APACHE_CONF%
    
    # Backup du fichier original
    copy "%APACHE_CONF%" "%APACHE_CONF%.backup" >nul 2>&1
    
    # Ajout du VirtualHost s'il n'existe pas
    findstr /C:"naissancechain.local" "%APACHE_CONF%" >nul
    if %errorlevel% neq 0 (
        echo Ajout du VirtualHost NaissanceChain...
        echo. >> "%APACHE_CONF%"
        echo # NaissanceChain API VirtualHost >> "%APACHE_CONF%"
        echo ^<VirtualHost *:80^> >> "%APACHE_CONF%"
        echo     ServerName naissancechain.local >> "%APACHE_CONF%"
        echo     ServerAlias www.naissancechain.local >> "%APACHE_CONF%"
        echo     DocumentRoot "C:/wamp64/www/naissancechain-api/public" >> "%APACHE_CONF%"
        echo     ^<Directory "C:/wamp64/www/naissancechain-api/public"^> >> "%APACHE_CONF%"
        echo         Options -Indexes +FollowSymLinks >> "%APACHE_CONF%"
        echo         AllowOverride All >> "%APACHE_CONF%"
        echo         Require all granted >> "%APACHE_CONF%"
        echo     ^</Directory^> >> "%APACHE_CONF%"
        echo     ErrorLog "C:/wamp64/logs/naissancechain_error.log" >> "%APACHE_CONF%"
        echo     CustomLog "C:/wamp64/logs/naissancechain_access.log" combined >> "%APACHE_CONF%"
        echo ^</VirtualHost^> >> "%APACHE_CONF%"
        echo ✓ VirtualHost ajouté
    ) else (
        echo ✓ VirtualHost déjà configuré
    )
) else (
    echo ✗ Fichier de configuration Apache non trouvé
    echo Vérifier l'installation WAMP64
)

echo.
echo [6/8] Test API accessibilite...
curl -s -o nul -w "Status: %%{http_code}" http://127.0.0.1/api/v1/ 2>nul
if %errorlevel% equ 0 (
    echo ✓ API accessible via IP
) else (
    echo ✗ API non accessible via IP
    echo Verification Apache et .htaccess requis
)

echo.
echo [7/8] Test DNS local...
ping -n 1 naissancechain.local >nul 2>&1
if %errorlevel% equ 0 (
    echo ✓ DNS local fonctionne
) else (
    echo ✗ DNS local ne fonctionne pas
    echo Solution: Utiliser l'IP directe http://127.0.0.1/api/v1/
)

echo.
echo [8/8] Optimisation Laravel...
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo.
echo ========================================
echo Correction terminee!
echo ========================================
echo.
echo 📋 Comptes disponibles:
echo    Admin: admin@naissancechain.local / Admin123!
echo    Agent: agent@naissancechain.local / Agent123!
echo    Ecole: ecole@naissancechain.local / Ecole123!
echo    Sante: sante@naissancechain.local / Sante123!
echo.
echo 🌐 URLs API:
echo    IP directe: http://127.0.0.1/api/v1/
echo    Domaine:   http://naissancechain.local/api/v1/
echo.
echo 🧪 Tests a effectuer:
echo    1. Test connexion: POST /login
echo    2. Test profil: GET /me
echo    3. Test naissances: GET /naissances
echo    4. Test création: POST /naissances
echo.
pause
