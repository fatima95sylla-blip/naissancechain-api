@echo off
echo ========================================
echo Migration Base de Donnees NaissanceChain
echo ========================================
echo.

echo [1/3] Verification de la base de donnees...
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
echo [2/3] Execution de la migration...
php artisan migrate --force

echo.
echo [3/3] Verification des tables...
php artisan tinker --execute="
\$tables = DB::select('SHOW TABLES');
echo 'Tables creees:' . PHP_EOL;
foreach (\$tables as \$table) {
    echo '- ' . \$table->{'Tables_in_naissancechain_db'} . PHP_EOL;
}
echo PHP_EOL . 'Total tables: ' . count(\$tables) . PHP_EOL;
"

echo.
echo ========================================
echo Migration terminee!
echo ========================================
echo.
echo Si des erreurs apparaissent:
echo 1. Verifier que WAMP64 est demarre
echo 2. Verifier la connexion MySQL
echo 3. Verifier les permissions
echo.
pause
