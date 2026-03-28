# div CLI Installer for Windows (PowerShell)
#
# Usage:
#   irm https://raw.githubusercontent.com/divengine/cli/main/install.ps1 | iex
#   irm https://raw.githubusercontent.com/divengine/cli/main/install.ps1 | iex -InstallDir "C:\tools"

param(
    [string]$InstallDir = "$env:USERPROFILE\bin"
)

$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "  ██████╗ ██╗██╗   ██╗██████╗ ███████╗██████╗ " -ForegroundColor Cyan
Write-Host "  ██╔══██╗██║╚██╗ ██╔╝██╔══██╗██╔════╝██╔══██╗" -ForegroundColor Cyan
Write-Host "  ██████╔╝██║ ╚████╔╝ ██████╔╝█████╗  ██████╔╝" -ForegroundColor Cyan
Write-Host "  ██╔═══╝ ██║  ╚██╔╝  ██╔═══╝ ██╔══╝  ██╔══██╗" -ForegroundColor Cyan
Write-Host "  ██║     ██║   ██║   ██║     ███████╗██║  ██║" -ForegroundColor Cyan
Write-Host "  ╚═╝     ╚═╝   ╚═╝   ╚═╝     ╚══════╝╚═╝  ╚═╝" -ForegroundColor Cyan
Write-Host ""
Write-Host "  div CLI Installer (Windows)" -ForegroundColor Gray
Write-Host "  ─────────────────────────────" -ForegroundColor Gray
Write-Host ""

# Create install directory if needed
if (-not (Test-Path $InstallDir)) {
    Write-Host "  Creating directory: $InstallDir" -ForegroundColor Gray
    New-Item -ItemType Directory -Path $InstallDir -Force | Out-Null
}

$PharPath = Join-Path $InstallDir "div.phar"

Write-Host "  Install to: $InstallDir" -ForegroundColor Gray
Write-Host ""

# Get latest release
Write-Host "  Fetching latest release..." -ForegroundColor Gray

try {
    $Headers = @{ "User-Agent" = "div-installer/1.0" }
    $Response = Invoke-RestMethod -Uri "https://api.github.com/repos/divengine/cli/releases/latest" -Headers $Headers -TimeoutSec 30
} catch {
    Write-Host "  [ERROR] Failed to fetch release info. Check your internet connection." -ForegroundColor Red
    exit 1
}

$Version = $Response.tag_name
$DownloadUrl = $null

foreach ($Asset in $Response.assets) {
    if ($Asset.name -eq "div.phar") {
        $DownloadUrl = $Asset.browser_download_url
        break
    }
}

if (-not $DownloadUrl) {
    Write-Host "  [ERROR] div.phar not found in latest release." -ForegroundColor Red
    Write-Host "          Version: $Version" -ForegroundColor Red
    exit 1
}

Write-Host "  Version:    $Version" -ForegroundColor Gray
Write-Host "  Source:     $DownloadUrl" -ForegroundColor Gray
Write-Host ""

# Download the PHAR
Write-Host "  Downloading..." -ForegroundColor Gray

try {
    $WebClient = New-Object System.Net.WebClient
    $WebClient.Headers.Add("User-Agent", "div-installer/1.0")
    $WebClient.DownloadFile($DownloadUrl, $PharPath)
    $WebClient.Dispose()
} catch {
    Write-Host "  [ERROR] Failed to download div.phar." -ForegroundColor Red
    exit 1
}

Write-Host "  [OK] Downloaded div.phar" -ForegroundColor Green

Write-Host ""
Write-Host "  ─────────────────────────────────────" -ForegroundColor Gray
Write-Host "  [OK] Installation complete!" -ForegroundColor Green
Write-Host "  ─────────────────────────────────────" -ForegroundColor Gray
Write-Host ""

Write-Host "  Run: php $PharPath --help" -ForegroundColor White
Write-Host ""
Write-Host "  To add to PATH permanently, run:" -ForegroundColor Gray
Write-Host "    [Environment]::SetEnvironmentVariable('Path', \$env:Path + ';$InstallDir', 'User')" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Learn more: https://github.com/divengine/cli" -ForegroundColor Gray
Write-Host ""
