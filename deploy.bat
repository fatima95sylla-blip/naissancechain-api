@echo off
setlocal enabledelayedexpansion

echo ========================================
echo NaissanceChain API Deployment Script
echo ========================================
echo.

REM Set variables
set PROJECT_PATH=C:\wamp64\www\naissancechain-api
set ENV_FILE=env.production.txt
set BACKUP_DIR=C:\wamp64\backups\naissancechain

REM Create backup directory if not exists
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

echo [1/10] Creating backup...
if exist "%PROJECT_PATH%\.env" (
    copy "%PROJECT_PATH%\.env" "%BACKUP_DIR%\.env.backup.%date:~0,4%%date:~5,2%%date:~8,2%_%time:~0,2%%time:~3,2%%time:~6,2%" >nul
    echo Backup created successfully
) else (
    echo No .env file found to backup
)

echo.
echo [2/10] Copying production environment file...
copy "%PROJECT_PATH%\%ENV_FILE%" "%PROJECT_PATH%\.env" >nul
if errorlevel 1 (
    echo ERROR: Failed to copy environment file
    pause
    exit /b 1
)
echo Environment file copied successfully

echo.
echo [3/10] Installing Composer dependencies...
cd /d "%PROJECT_PATH%"
composer install --no-dev --optimize-autoloader --no-interaction
if errorlevel 1 (
    echo ERROR: Composer install failed
    pause
    exit /b 1
)
echo Dependencies installed successfully

echo.
echo [4/10] Generating application key...
php artisan key:generate --force
if errorlevel 1 (
    echo ERROR: Key generation failed
    pause
    exit /b 1
)
echo Application key generated

echo.
echo [5/10] Running database migrations...
php artisan migrate --force
if errorlevel 1 (
    echo ERROR: Database migration failed
    pause
    exit /b 1
)
echo Database migrated successfully

echo.
echo [6/10] Running database seeders...
php artisan db:seed --force
if errorlevel 1 (
    echo WARNING: Database seeding failed (non-critical)
)
echo Database seeded (if applicable)

echo.
echo [7/10] Creating storage symbolic link...
php artisan storage:link
if errorlevel 1 (
    echo WARNING: Storage link creation failed
)
echo Storage link created

echo.
echo [8/10] Optimizing application...
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
if errorlevel 1 (
    echo WARNING: Some optimization commands failed
)
echo Application optimized

echo.
echo [9/10] Clearing caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan queue:restart
echo Caches cleared

echo.
echo [10/10] Setting permissions...
icacls "%PROJECT_PATH%\storage" /grant "IUSR:(OI)(CI)(F)" /T >nul
icacls "%PROJECT_PATH%\bootstrap\cache" /grant "IUSR:(OI)(CI)(F)" /T >nul
icacls "%PROJECT_PATH%\public" /grant "IUSR:(OI)(CI)(F)" /T >nul
echo Permissions set

echo.
echo ========================================
echo Deployment completed successfully!
echo ========================================
echo.
echo Next steps:
echo 1. Update your hosts file: 127.0.0.1 naissancechain.local
echo 2. Configure Apache VirtualHost (see apache-vhost.conf)
echo 3. Restart Apache
echo 4. Test the API at: http://naissancechain.local
echo.
echo Queue workers:
echo Run: start-queue-workers.bat
echo.
pause
