@echo off
setlocal enabledelayedexpansion

echo ========================================
echo NaissanceChain API - Test de Deploiement
echo ========================================
echo.

REM Set variables
set API_URL=http://naissancechain.local
set TEST_EMAIL=test@example.com
set TEST_PASSWORD=Password123!

echo [1/8] Verification de l'API...
curl -s -o nul -w "%%{http_code}" "%API_URL%/api/v1/" > temp_status.txt
set /p API_STATUS=<temp_status.txt
del temp_status.txt

if "%API_STATUS%"=="200" (
    echo ✓ API accessible (Status: %API_STATUS%)
) else (
    echo ✗ API inaccessible (Status: %API_STATUS%)
    goto :error
)

echo.
echo [2/8] Test de sante de l'API...
curl -s -o temp_health.json -w "%%{http_code}" "%API_URL%/api/v1/" > temp_status.txt
set /p HEALTH_STATUS=<temp_status.txt
del temp_status.txt

if "%HEALTH_STATUS%"=="200" (
    echo ✓ API sante OK
    type temp_health.json
    del temp_health.json
) else (
    echo ✗ API sante KO (Status: %HEALTH_STATUS%)
)

echo.
echo [3/8] Test d'inscription...
curl -s -o temp_register.json -w "%%{http_code}" "%API_URL%/api/v1/register" ^
  -H "Content-Type: application/json" ^
  -d "{\"name\":\"Test User\",\"email\":\"%TEST_EMAIL%\",\"password\":\"%TEST_PASSWORD%\",\"password_confirmation\":\"%TEST_PASSWORD%\",\"role\":\"AGENT\",\"telephone\":\"1234567890\",\"prefecture\":\"Abidjan\",\"zone\":\"Zone 1\"}" ^
  > temp_status.txt

set /p REGISTER_STATUS=<temp_status.txt
del temp_status.txt

if "%REGISTER_STATUS%"=="201" (
    echo ✓ Inscription reussie
    type temp_register.json
    del temp_register.json
) else (
    echo ✗ Inscription echouee (Status: %REGISTER_STATUS%)
    if exist temp_register.json (
        type temp_register.json
        del temp_register.json
    )
)

echo.
echo [4/8] Test de connexion...
curl -s -o temp_login.json -w "%%{http_code}" "%API_URL%/api/v1/login" ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"%TEST_EMAIL%\",\"password\":\"%TEST_PASSWORD%\"}" ^
  > temp_status.txt

set /p LOGIN_STATUS=<temp_status.txt
del temp_status.txt

if "%LOGIN_STATUS%"=="200" (
    echo ✓ Connexion reussie
    type temp_login.json
    
    REM Extraire le token
    for /f "tokens=2 delims=," %%i in ('findstr "token" temp_login.json') do (
        set TOKEN_LINE=%%i
    )
    REM Simple extraction du token (approximative)
    for /f "tokens=2 delims=:\"" %%i in ("%TOKEN_LINE%") do (
        set TOKEN=%%i
    )
    echo Token extrait: %TOKEN%
    del temp_login.json
) else (
    echo ✗ Connexion echouee (Status: %LOGIN_STATUS%)
    if exist temp_login.json (
        type temp_login.json
        del temp_login.json
    )
    set TOKEN=
)

echo.
echo [5/8] Test de profil utilisateur...
if defined TOKEN (
    curl -s -o temp_profile.json -w "%%{http_code}" "%API_URL%/api/v1/me" ^
      -H "Authorization: Bearer %TOKEN%" ^
      > temp_status.txt
    
    set /p PROFILE_STATUS=<temp_status.txt
    del temp_status.txt
    
    if "%PROFILE_STATUS%"=="200" (
        echo ✓ Profil accessible
        type temp_profile.json
        del temp_profile.json
    ) else (
        echo ✗ Profil inaccessible (Status: %PROFILE_STATUS%)
        if exist temp_profile.json (
            type temp_profile.json
            del temp_profile.json
        )
    )
) else (
    echo ✗ Impossible de tester le profil (pas de token)
)

echo.
echo [6/8] Test des endpoints naissances...
if defined TOKEN (
    curl -s -o temp_naissances.json -w "%%{http_code}" "%API_URL%/api/v1/naissances" ^
      -H "Authorization: Bearer %TOKEN%" ^
      > temp_status.txt
    
    set /p NAISSANCES_STATUS=<temp_status.txt
    del temp_status.txt
    
    if "%NAISSANCES_STATUS%"=="200" (
        echo ✓ Endpoint naissances accessible
        type temp_naissances.json
        del temp_naissances.json
    ) else (
        echo ✗ Endpoint naissances inaccessible (Status: %NAISSANCES_STATUS%)
        if exist temp_naissances.json (
            type temp_naissances.json
            del temp_naissances.json
        )
    )
) else (
    echo ✗ Impossible de tester les naissances (pas de token)
)

echo.
echo [7/8] Test des endpoints blockchain...
if defined TOKEN (
    curl -s -o temp_blockchain.json -w "%%{http_code}" "%API_URL%/api/v1/blockchain/stats" ^
      -H "Authorization: Bearer %TOKEN%" ^
      > temp_status.txt
    
    set /p BLOCKCHAIN_STATUS=<temp_status.txt
    del temp_status.txt
    
    if "%BLOCKCHAIN_STATUS%"=="200" (
        echo ✓ Endpoint blockchain accessible
        type temp_blockchain.json
        del temp_blockchain.json
    ) else (
        echo ✗ Endpoint blockchain inaccessible (Status: %BLOCKCHAIN_STATUS%)
        if exist temp_blockchain.json (
            type temp_blockchain.json
            del temp_blockchain.json
        )
    )
) else (
    echo ✗ Impossible de tester la blockchain (pas de token)
)

echo.
echo [8/8] Verification de la base de donnees...
php artisan tinker --execute="
try {
    \$users = DB::table('users')->count();
    \$naissances = DB::table('naissances')->count();
    echo 'Users: ' . \$users . PHP_EOL;
    echo 'Naissances: ' . \$naissances . PHP_EOL;
    echo 'Database: OK' . PHP_EOL;
} catch (Exception \$e) {
    echo 'Database Error: ' . \$e->getMessage() . PHP_EOL;
}
"

echo.
echo ========================================
echo Test de deploiement termine!
echo ========================================
echo.

REM Cleanup
if exist temp_*.json del temp_*.json
if exist temp_status.txt del temp_status.txt

REM Summary
echo Resume:
echo - API Access: %API_STATUS%
echo - Health Check: %HEALTH_STATUS%
echo - Registration: %REGISTER_STATUS%
echo - Login: %LOGIN_STATUS%
echo - Profile: %PROFILE_STATUS%
echo - Naissances: %NAISSANCES_STATUS%
echo - Blockchain: %BLOCKCHAIN_STATUS%

echo.
goto :end

:error
echo.
echo ========================================
echo ERREUR DE DEPLOIEMENT
echo ========================================
echo.
echo Verifications a effectuer:
echo 1. WAMP64 est-il demarre?
echo 2. Apache fonctionne-t-il?
echo 3. MySQL fonctionne-t-il?
echo 4. Le VirtualHost est-il configure?
echo 5. Le fichier .env existe-t-il?
echo 6. Les migrations ont-elles ete executees?
echo 7. Les workers sont-ils demarres?
echo.
echo Consultez les logs:
echo - Apache: C:/wamp64/logs/apache_error.log
echo - Laravel: storage/logs/laravel.log
echo - MySQL: C:/wamp64/logs/mysql.log

:end
pause
