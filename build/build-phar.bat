@echo off
REM
REM Build script for div.phar on Windows
REM
REM Usage:
REM   build\build-phar.bat
REM

setlocal EnableDelayedExpansion

echo.
echo ========================================
echo   div CLI - Building PHAR
echo ========================================
echo.

cd /d "%~dp0.."

if not exist "composer.json" (
    echo ERROR: composer.json not found. Run this from the project root.
    exit /b 1
)

if not exist "vendor" (
    echo Installing dependencies...
    call composer install --no-interaction
)

echo.
echo Building PHAR...
echo.

php -d phar.readonly=0 "%~dp0build-phar.php"

if exist "%~dp0div.phar" (
    echo.
    echo Done!
)

endlocal
