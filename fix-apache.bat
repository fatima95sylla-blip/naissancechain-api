@echo off
echo ========================================
echo Configuration Apache pour NaissanceChain
echo ========================================
echo.

echo [1/5] Verification de la configuration Apache...
set APACHE_CONF=C:\wamp64\bin\apache\apache2.4.54\conf\extra\httpd-vhosts.conf
if exist "%APACHE_CONF%" (
    echo Fichier de configuration trouve: %APACHE_CONF%
) else (
    echo ERROR: Fichier de configuration Apache non trouve
    echo Verifier votre installation WAMP64
    pause
    exit /b 1
)

echo.
echo [2/5] Ajout du VirtualHost NaissanceChain...
echo # NaissanceChain API VirtualHost >> "%APACHE_CONF%"
echo. >> "%APACHE_CONF%"
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

echo.
echo [3/5] Verification de la configuration...
echo Contenu ajoute:
type "%APACHE_CONF%" | findstr /C:"naissancechain"

echo.
echo [4/5] Redemarrage des services Apache...
net stop wampapache
timeout /t 2 >nul
net start wampapache

echo.
echo [5/5] Test de la configuration...
echo Test DNS local...
ping -n 1 naissancechain.local

echo.
echo Test Apache...
curl -s -o nul -w "Status: %%{http_code}" http://naissancechain.local/

echo.
echo ========================================
echo Configuration terminee!
echo ========================================
echo.
echo Si le statut est 200, tout fonctionne!
echo Si le statut est 404, verifier:
echo 1. Apache est bien demarre
echo 2. Le fichier .htaccess existe dans public/
echo 3. Les permissions sont correctes
echo.
pause
