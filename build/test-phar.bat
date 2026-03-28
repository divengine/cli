@echo off
REM
REM Test script for div.phar on Windows
REM
REM Usage:
REM   build\test-phar.bat [command]
REM

setlocal

set "SCRIPT_DIR=%~dp0"
set "PHAR_PATH=%SCRIPT_DIR%div.phar"

echo.
echo Testing div.phar...
echo.

if not exist "%PHAR_PATH%" (
    echo ERROR: div.phar not found at: %PHAR_PATH%
    echo.
    echo Run build\build-phar.bat first to build it.
    exit /b 1
)

set "COMMAND=%1"
if "%COMMAND%"=="" set "COMMAND=version"

echo Running: div.phar %COMMAND%
echo.

php "%PHAR_PATH%" %COMMAND%

echo.
echo Test complete!

endlocal
