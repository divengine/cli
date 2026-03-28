@echo off
setlocal enabledelayedexpansion

echo.
echo   ██████╗ ██╗██╗   ██╗██████╗ ███████╗██████╗ 
echo   ██╔══██╗██║╚██╗ ██╔╝██╔══██╗██╔════╝██╔══██╗
echo   ██████╔╝██║ ╚████╔╝ ██████╔╝█████╗  ██████╔╝
echo   ██╔═══╝ ██║  ╚██╔╝  ██╔═══╝ ██╔══╝  ██╔══██╗
echo   ██║     ██║   ██║   ██║     ███████╗██║  ██║
echo   ╚═╝     ╚═╝   ╚═╝   ╚═╝     ╚══════╝╚═╝  ╚═╝
echo.
echo   div CLI Installer (Windows)
echo   ─────────────────────────────
echo.

:: Get install directory from command line or use default
set "INSTALL_DIR=%USERPROFILE%\bin"

:: Create install directory if needed
if not exist "%INSTALL_DIR%" (
    echo   Creating directory: %INSTALL_DIR%
    mkdir "%INSTALL_DIR%"
)

set "PHAR_PATH=%INSTALL_DIR%\div.phar"

echo   Install to: %INSTALL_DIR%
echo.

echo   Fetching latest release...
set "GITHUB_API=https://api.github.com/repos/divengine/cli/releases/latest"

:: Use curl if available, otherwise fallback to bitsadmin
where curl >nul 2>&1
if %ERRORLEVEL% equ 0 (
    curl -sL -A "div-installer/1.0" "%GITHUB_API%" -o "%TEMP%\div_release.json"
) else (
    powershell -Command "(Invoke-WebRequest -Uri '%GITHUB_API%' -Headers @{'User-Agent'='div-installer/1.0'} -UseBasicParsing).Content | Out-File -FilePath '%TEMP%\div_release.json'"
)

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
set "DOWNLOAD_URL=!DOWNLOAD_URL:",=!"
set "DOWNLOAD_URL=!DOWNLOAD_URL:"=!"

echo   Version:    !VERSION!
echo   Source:     !DOWNLOAD_URL!
echo.

echo   Downloading...
where curl >nul 2>&1
if %ERRORLEVEL% equ 0 (
    curl -sL -A "div-installer/1.0" "!DOWNLOAD_URL!" -o "%PHAR_PATH%"
) else (
    powershell -Command "Invoke-WebRequest -Uri '!DOWNLOAD_URL!' -OutFile '%PHAR_PATH%' -Headers @{'User-Agent'='div-installer/1.0'}"
)

if not exist "%PHAR_PATH%" (
    echo   [ERROR] Failed to download div.phar.
    exit /b 1
)

echo   [OK] Downloaded div.phar
echo.
echo   ─────────────────────────────────────
echo   [OK] Installation complete!
echo   ─────────────────────────────────────
echo.
echo   Run: php %PHAR_PATH% --help
echo.
echo   Learn more: https://github.com/divengine/cli
echo.

:: Cleanup
del "%TEMP%\div_release.json" 2>nul
del "%TEMP%\div_url.txt" 2>nul

endlocal
