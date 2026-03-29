@echo off
setlocal enabledelayedexpansion

echo.
echo   div CLI Installer (Windows)
echo   ─────────────────────────────
echo.

:: Check for --local flag
set "LOCAL_MODE="
echo %* | findstr /i "--local" >nul
if not errorlevel 1 set "LOCAL_MODE=1"

:: Get install directory from command line or use default
set "INSTALL_DIR=%USERPROFILE%\bin"

:: Create install directory if needed
if not exist "%INSTALL_DIR%" (
    mkdir "%INSTALL_DIR%"
)

set "PHAR_PATH=%INSTALL_DIR%\div.phar"
set "SCRIPT_DIR=%~dp0"
set "PROJECT_ROOT=%SCRIPT_DIR%.."
set "LOCAL_PHAR=%PROJECT_ROOT%\build\div.phar"

echo   Install to: %INSTALL_DIR%
echo.

if defined LOCAL_MODE (
    echo   Mode:       Local (development)
    echo   Source:     %PROJECT_ROOT%
    echo.

    if not exist "%LOCAL_PHAR%" (
        echo   [INFO] No local PHAR found. Building from source...
        echo.
        echo   Running: php build\build-phar.php
        call php -d phar.readonly=0 "%PROJECT_ROOT%\build\build-phar.php"
        
        if not exist "%LOCAL_PHAR%" (
            echo   [ERROR] PHAR was not created.
            exit /b 1
        )
        
        echo   [OK] PHAR built successfully
    ) else (
        echo   [OK] Using existing local PHAR
    )
    echo.

    copy /Y "%LOCAL_PHAR%" "%PHAR_PATH%" >nul
    if errorlevel 1 (
        echo   [ERROR] Failed to copy PHAR.
        exit /b 1
    )
    echo   [OK] Copied div.phar to %INSTALL_DIR%

) else (
    echo   Mode:       Remote (production)
    echo.

    echo   Fetching latest release from GitHub...
    set "GITHUB_API=https://api.github.com/repos/divengine/cli/releases/latest"

    curl -sL -A "div-installer/1.0" "%GITHUB_API%" -o "%TEMP%\div_release.json"

    if not exist "%TEMP%\div_release.json" (
        echo   [ERROR] Failed to fetch release info.
        exit /b 1
    )

    :: Extract version and download URL
    for /f "tokens=1* delims=:," %%a in ('findstr /C:"\"tag_name\"" "%TEMP%\div_release.json"') do (
        set "VERSION=%%b"
        set "VERSION=!VERSION:"=!"
        set "VERSION=!VERSION: =!"
    )

    findstr /C:"\"browser_download_url\"" "%TEMP%\div_release.json" | findstr "div.phar" > "%TEMP%\div_url.txt"
    set /p DOWNLOAD_URL=<"%TEMP%\div_url.txt"

    :: Clean up URLs
    set "DOWNLOAD_URL=!DOWNLOAD_URL:*: =!"
    set "DOWNLOAD_URL=!DOWNLOAD_URL:,=!"
    set "DOWNLOAD_URL=!DOWNLOAD_URL:"=!"

    echo   Version:    !VERSION!
    echo   Source:     !DOWNLOAD_URL!
    echo.
    echo   Downloading...

    curl -sL -A "div-installer/1.0" "!DOWNLOAD_URL!" -o "%PHAR_PATH%"

    if not exist "%PHAR_PATH%" (
        echo   [ERROR] Failed to download div.phar.
        exit /b 1
    )
    echo   [OK] Downloaded div.phar

    :: Cleanup
    del "%TEMP%\div_release.json" 2>nul
    del "%TEMP%\div_url.txt" 2>nul
)

echo.
echo   ================================================
echo   [OK] Installation complete!
echo   ================================================
echo.
echo   Run: php %PHAR_PATH% --help
echo.
echo   To use without 'php', add %INSTALL_DIR% to your PATH.
echo.
echo   Learn more: https://github.com/divengine/cli
echo.

endlocal
