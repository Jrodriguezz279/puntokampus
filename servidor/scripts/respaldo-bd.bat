@echo off
REM Respaldo Diario de Base de Datos PostgreSQL PuntoKampus

set PGPASSWORD=sistemas
set PG_BIN=C:\Program Files\PostgreSQL\16\bin
set BACKUP_DIR=C:\backups\puntokampus
set DB_NAME=puntokampus
set DB_USER=postgres
set DB_HOST=127.0.0.1
set DB_PORT=5432

for /f %%I in ('powershell -Command "Get-Date -Format yyyy-MM-dd_HHmmss"') do set FECHA=%%I
set ARCHIVO=%BACKUP_DIR%\puntokampus_%FECHA%.sql

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

echo [%date% %time%] Iniciando respaldo de PostgreSQL: %DB_NAME%...

"%PG_BIN%\pg_dump.exe" -h %DB_HOST% -p %DB_PORT% -U %DB_USER% -d %DB_NAME% --no-owner --clean > "%ARCHIVO%"

if %ERRORLEVEL% equ 0 (
    echo [%date% %time%] Respaldo completado exitosamente: %ARCHIVO%
) else (
    echo [%date% %time%] ERROR al realizar el respaldo de la base de datos.
    exit /b %ERRORLEVEL%
)
