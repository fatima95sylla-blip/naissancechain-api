@echo off
setlocal enabledelayedexpansion

echo ========================================
echo NaissanceChain MySQL Backup Script
echo ========================================
echo.

REM Set variables
set DB_NAME=naissancechain_prod
set DB_USER=root
set DB_PASSWORD=
set BACKUP_DIR=C:\wamp64\backups\mysql
set MYSQL_PATH=C:\wamp64\bin\mysql\mysql8.0.31\bin
set DATE_TIME=%date:~0,4%%date:~5,2%%date:~8,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set BACKUP_FILE=%DB_NAME%_backup_%DATE_TIME%.sql

REM Create backup directory if not exists
if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

echo [1/4] Creating database backup...
echo Backup file: %BACKUP_DIR%\%BACKUP_FILE%

REM Create backup
"%MYSQL_PATH%\mysqldump.exe" --user=%DB_USER% --password=%DB_PASSWORD% --host=127.0.0.1 --port=3306 --single-transaction --routines --triggers --events %DB_NAME% > "%BACKUP_DIR%\%BACKUP_FILE%"

if errorlevel 1 (
    echo ERROR: Database backup failed
    pause
    exit /b 1
)

echo Database backup created successfully

echo.
echo [2/4] Compressing backup file...
cd /d "%BACKUP_DIR%"
powershell -Command "Compress-Archive -Path '%BACKUP_FILE%' -DestinationPath '%BACKUP_FILE%.zip' -Force"
if errorlevel 1 (
    echo WARNING: Compression failed
) else (
    echo Backup compressed successfully
    del "%BACKUP_FILE%" >nul
    set BACKUP_FILE=%BACKUP_FILE%.zip
)

echo.
echo [3/4] Cleaning old backups (keep last 7 days)...
forfiles /P "%BACKUP_DIR%" /M "%DB_NAME%_backup_*.zip" /D -7 /C "cmd /c echo Deleting @file... && del @path" 2>nul
echo Old backups cleaned

echo.
echo [4/4] Backup completed!
echo.
echo Backup location: %BACKUP_DIR%\%BACKUP_FILE%
echo Database: %DB_NAME%
echo Size: 
dir "%BACKUP_DIR%\%BACKUP_FILE%" | findstr "%BACKUP_FILE%"
echo.
pause
