# Build script for div.phar on Windows (PowerShell)
#
# Usage:
#   .\build\build-phar.ps1

$ErrorActionPreference = "Stop"

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectRoot = Split-Path -Parent $ScriptDir

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  div CLI - Building PHAR" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Set-Location $ProjectRoot

if (-not (Test-Path "composer.json")) {
    Write-Host "ERROR: composer.json not found. Run this from the project root." -ForegroundColor Red
    exit 1
}

if (-not (Test-Path "vendor")) {
    Write-Host "Installing dependencies..."
    composer install --no-interaction
}

Write-Host ""
Write-Host "Building PHAR..."
Write-Host ""

php -d phar.readonly=0 "$ScriptDir\build-phar.php"

if (Test-Path "$ScriptDir\div.phar") {
    Write-Host ""
    Write-Host "Done!" -ForegroundColor Green
}
