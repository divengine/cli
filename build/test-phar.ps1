# Test script for div.phar on Windows (PowerShell)
#
# Usage:
#   .\build\test-phar.ps1 [command]

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$PharPath = Join-Path $ScriptDir "div.phar"

Write-Host ""
Write-Host "Testing div.phar..." -ForegroundColor Cyan
Write-Host ""

if (-not (Test-Path $PharPath)) {
    Write-Host "ERROR: div.phar not found at: $PharPath" -ForegroundColor Red
    Write-Host ""
    Write-Host "Run .\build\build-phar.ps1 first to build it." -ForegroundColor Yellow
    exit 1
}

$Command = if ($Args.Count -gt 0) { $Args[0] } else { "version" }

Write-Host "Running: div.phar $Command"
Write-Host ""

php $PharPath $Command

Write-Host ""
Write-Host "Test complete!" -ForegroundColor Green
