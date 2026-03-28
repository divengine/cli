@echo off
setlocal EnableDelayedExpansion

echo.
echo Divengine CLI Installer
echo =========================
echo.

set "SCRIPT_DIR=%~dp0"
set "PROJECT_ROOT=%SCRIPT_DIR%.."

set "INSTALL_DIR=%USERPROFILE%\AppData\Local\Programs\div"
set "BIN_DIR=%INSTALL_DIR%\bin"

echo Installing div to %INSTALL_DIR%...
echo.

if not exist "%INSTALL_DIR%" (
    mkdir "%INSTALL_DIR%"
    mkdir "%BIN_DIR%"
)

if exist "%BIN_DIR%\div.bat" (
    echo Removing existing installation...
    del /q "%BIN_DIR%\div.bat" 2>nul
)

(
    echo @echo off
    echo set "DIV_CLI_ROOT=%PROJECT_ROOT:)=^)%"
    echo php "%%DIV_CLI_ROOT%%\bin\div" %%*
) > "%BIN_DIR%\div.bat"

echo.
echo Setting PATH and DIV_CLI_ROOT...
echo.

setx DIV_CLI_ROOT "%PROJECT_ROOT%" >nul 2>&1
setx PATH "%BIN_DIR%;%PATH%" >nul 2>&1

echo ================================================
echo Divengine CLI installed successfully!
echo ================================================
echo.
echo IMPORTANT: You may need to restart your terminal
echo for the PATH changes to take effect.
echo.
echo Run: div --help
echo.

endlocal
