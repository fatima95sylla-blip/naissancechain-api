@echo off
echo ========================================
echo Test API NaissanceChain
echo ========================================
echo.

echo [1/4] Test accessibilite API...
curl -s -o nul -w "Status: %%{http_code}" http://naissancechain.local/api/v1/
echo.

echo [2/4] Test inscription...
curl -s -o temp_register.json -w "Status: %%{http_code}" http://naissancechain.local/api/v1/register -H "Content-Type: application/json" -d "{\"name\":\"Test User\",\"email\":\"test@example.com\",\"password\":\"Password123!\",\"password_confirmation\":\"Password123!\",\"role\":\"AGENT\",\"telephone\":\"1234567890\",\"prefecture\":\"Abidjan\",\"zone\":\"Zone 1\"}"
if exist temp_register.json (
    echo Reponse inscription:
    type temp_register.json
    del temp_register.json
)
echo.

echo [3/4] Test connexion...
curl -s -o temp_login.json -w "Status: %%{http_code}" http://naissancechain.local/api/v1/login -H "Content-Type: application/json" -d "{\"email\":\"test@example.com\",\"password\":\"Password123!\"}"
if exist temp_login.json (
    echo Reponse connexion:
    type temp_login.json
    del temp_login.json
)
echo.

echo [4/4] Test base de donnees...
php artisan tinker --execute="echo 'Users: ' . DB::table('users')->count() . PHP_EOL; echo 'Naissances: ' . DB::table('naissances')->count() . PHP_EOL; echo 'Agents: ' . DB::table('agents')->count() . PHP_EOL;"

echo.
echo ========================================
echo Test termine!
echo ========================================
pause
