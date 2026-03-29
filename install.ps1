# div CLI Installer for Windows (PowerShell)
#
# Usage:
#   # Install from local source (development)
#   php install --from-here
#   
#   # Download from GitHub releases (production)
#   irm https://raw.githubusercontent.com/divengine/cli/main/install.ps1 | iex

param(
    [switch]$FromHere,
    [string]$InstallDir = "$env:USERPROFILE\bin"
)

$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "  div CLI Installer (Windows)" -ForegroundColor Gray
Write-Host "  ─────────────────────────────" -ForegroundColor Gray
Write-Host ""

# Determine project root (where this script is located)
$ScriptPath = $PSCommandPath
if (-not $ScriptPath) {
    $ScriptPath = $MyInvocation.MyCommand.Path
}
$ProjectRoot = Split-Path -Parent $ScriptPath

Write-Host "  Install to: $InstallDir" -ForegroundColor Gray
Write-Host ""

# Create install directory if needed
if (-not (Test-Path $InstallDir)) {
    Write-Host "  Creating directory: $InstallDir" -ForegroundColor Gray
    New-Item -ItemType Directory -Path $InstallDir -Force | Out-Null
}

$PharPath = Join-Path $InstallDir "div.phar"

# Check for local source
$LocalPhar = Join-Path $ProjectRoot "build\div.phar"
$HasLocalPhar = Test-Path $LocalPhar
$HasComposerJson = Test-Path (Join-Path $ProjectRoot "composer.json")

if ($FromHere -or ($HasLocalPhar -and $HasComposerJson)) {
    # LOCAL INSTALLATION
    Write-Host "  Mode:       From here (development)" -ForegroundColor Cyan
    Write-Host "  Source:     $ProjectRoot" -ForegroundColor Gray
    Write-Host ""

    if (-not $HasLocalPhar) {
        Write-Host "  [!] No local PHAR found." -ForegroundColor Yellow
        Write-Host "     Building PHAR from source..." -ForegroundColor Yellow
        Write-Host ""

        # Build PHAR
        $BuildScript = Join-Path $ProjectRoot "build\build-phar.php"
        if (-not (Test-Path $BuildScript)) {
            Write-Host "  [ERROR] Build script not found: $BuildScript" -ForegroundColor Red
            exit 1
        }

        Write-Host "  Running: php build/build-phar.php" -ForegroundColor Gray
        $null = iex "php -d phar.readonly=0 `"$BuildScript`" 2>&1"
        
        if (-not (Test-Path $LocalPhar)) {
            Write-Host "  [ERROR] PHAR was not created." -ForegroundColor Red
            exit 1
        }
        
        Write-Host "  [OK] PHAR built successfully" -ForegroundColor Green
    } else {
        Write-Host "  [OK] Using existing local PHAR" -ForegroundColor Green
    }
    Write-Host ""

    # Copy local PHAR to install location
    Copy-Item -Path $LocalPhar -Destination $PharPath -Force
    
    Write-Host "  [OK] Copied div.phar to $InstallDir" -ForegroundColor Green
    Write-Host ""

} else {
    # REMOTE INSTALLATION
    Write-Host "  Mode:       Remote (production)" -ForegroundColor Cyan
    Write-Host ""

    Write-Host "  Fetching latest release from GitHub..." -ForegroundColor Gray

    try {
        $Headers = @{ "User-Agent" = "div-installer/1.0" }
        $Response = Invoke-RestMethod -Uri "https://api.github.com/repos/divengine/cli/releases/latest" -Headers $Headers -TimeoutSec 30
    } catch {
        Write-Host "  [ERROR] Failed to fetch release info." -ForegroundColor Red
        Write-Host "          Check your internet connection." -ForegroundColor Red
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
}

Write-Host "  ─────────────────────────────────────" -ForegroundColor Gray
Write-Host "  [OK] Installation complete!" -ForegroundColor Green
Write-Host "  ─────────────────────────────────────" -ForegroundColor Gray
Write-Host ""

Write-Host "  Run: php $PharPath --help" -ForegroundColor White
Write-Host ""
Write-Host "  To use without 'php', add $InstallDir to your PATH:" -ForegroundColor Gray
Write-Host "    [Environment]::SetEnvironmentVariable('Path', \$env:Path + ';$InstallDir', 'User')" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Learn more: https://github.com/divengine/cli" -ForegroundColor Gray
Write-Host ""
