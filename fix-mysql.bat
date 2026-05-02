@echo off
echo ========================================
echo Correction Migration MySQL NaissanceChain
echo ========================================
echo.

echo [1/5] Suppression des migrations problematiques...
del database\migrations\2026_04_30_120201_create_permission_tables.php 2>nul
del database\migrations\2026_04_30_120202_create_permission_tables.php 2>nul
del database\migrations\*permission*.php 2>nul
echo Migrations problematiques supprimees

echo.
echo [2/5] Verification de la configuration MySQL...
echo Configuration actuelle:
type .env | findstr DB_

echo.
echo [3/5] Nettoyage de la base de donnees...
php artisan migrate:fresh --force --database=mysql

echo.
echo [4/5] Reexecution des migrations...
php artisan migrate --force

echo.
echo [5/5] Verification de la base de donnees...
php artisan tinker --execute="
echo 'Tables creees:';
\$tables = DB::select('SHOW TABLES');
foreach (\$tables as \$table) {
    echo '- ' . \$table->{'Tables_in_naissancechain_db'} . PHP_EOL;
}
echo PHP_EOL;
echo 'Users count: ' . DB::table('users')->count() . PHP_EOL;
echo 'Naissances count: ' . DB::table('naissances')->count() . PHP_EOL;
"

echo.
echo ========================================
echo Correction terminee!
echo ========================================
echo.
echo Si l'erreur persiste:
echo 1. Verifier la version MySQL (>=5.7.7 requis)
echo 2. Verifier le charset: utf8mb4_unicode_ci
echo 3. Augmenter innodb_file_per_table=1
echo.
pause
