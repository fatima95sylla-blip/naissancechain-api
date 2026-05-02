@echo off
setlocal enabledelayedexpansion

echo ========================================
echo NaissanceChain Queue Workers Manager
echo ========================================
echo.

REM Set variables
set PROJECT_PATH=C:\wamp64\www\naissancechain-api
set LOG_PATH=%PROJECT_PATH%\storage\logs\queue-workers.log
set MAX_WORKERS=3
set SLEEP=3
set MEMORY=512

REM Check if queue table exists
echo [1/5] Checking queue table...
cd /d "%PROJECT_PATH%"
php artisan queue:table --force >nul 2>&1
php artisan migrate --force --path=database/migrations/2024_01_01_000001_create_queue_jobs_table.php >nul 2>&1
echo Queue table ready

echo.
echo [2/5] Starting queue workers...
echo Starting %MAX_WORKERS% workers with %MEMORY%M memory limit
echo Log file: %LOG_PATH%
echo.

REM Create log directory if not exists
if not exist "%PROJECT_PATH%\storage\logs" mkdir "%PROJECT_PATH%\storage\logs"

REM Start workers in background
for /L %%i in (1,1,%MAX_WORKERS%) do (
    echo Starting worker %%i...
    start "QueueWorker%%i" cmd /c "cd /d \"%PROJECT_PATH%\" && php artisan queue:work --memory=%MEMORY% --sleep=%SLEEP% --tries=3 --timeout=60 --max-time=3600 >> \"%LOG_PATH%\" 2>&1"
)

echo.
echo [3/5] Workers started successfully!
echo.
echo [4/5] Monitoring workers...
echo Press Ctrl+C to stop all workers
echo.

REM Monitor workers
:monitor
timeout /t 30 >nul
echo Checking worker status...

REM Check if workers are still running
tasklist /FI "WINDOWTITLE eq QueueWorker*" | find /C "cmd.exe" > temp_count.txt
set /p worker_count=<temp_count.txt
del temp_count.txt

if !worker_count! LSS %MAX_WORKERS% (
    echo WARNING: Some workers may have stopped. Restarting...
    for /L %%i in (1,1,%MAX_WORKERS%) do (
        tasklist /FI "WINDOWTITLE eq QueueWorker%%i" | find "cmd.exe" >nul
        if errorlevel 1 (
            echo Restarting worker %%i...
            start "QueueWorker%%i" cmd /c "cd /d \"%PROJECT_PATH%\" && php artisan queue:work --memory=%MEMORY% --sleep=%SLEEP% --tries=3 --timeout=60 --max-time=3600 >> \"%LOG_PATH%\" 2>&1"
        )
    )
)

echo Workers running: !worker_count!/%MAX_WORKERS%
echo Last check: %date% %time%
goto monitor

REM Cleanup when stopped
:cleanup
echo.
echo [5/5] Stopping all workers...
taskkill /F /IM php.exe /FI "WINDOWTITLE eq QueueWorker*" >nul 2>&1
echo Workers stopped
pause
