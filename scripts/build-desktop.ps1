# Build et lancement de l'application bureau Kamille Events Manager
# Usage :
#   .\scripts\build-desktop.ps1            # build WSL + copie locale + lance
#   .\scripts\build-desktop.ps1 -BuildOnly # build uniquement
#   .\scripts\build-desktop.ps1 -RunOnly   # copie + lance (sans rebuild)

param(
    [switch]$BuildOnly,
    [switch]$RunOnly
)

$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$WslProject = "/buts4/www/nafy-application"
$LocalApp = Join-Path $env:LOCALAPPDATA "Kamille Events Manager"
$ExeName = "Kamille Events Manager.exe"
$UncSource = "\\wsl.localhost\Ubuntu\buts4\www\nafy-application\dist-electron\win-unpacked"

function Test-IsUncPath {
    param([string]$Path)
    return $Path -match '^\\\\'
}

function Invoke-WslBuild {
    if (-not (Get-Command wsl.exe -ErrorAction SilentlyContinue)) {
        throw "WSL requis. Installez Ubuntu depuis le Microsoft Store."
    }

    Write-Host "Build via WSL (chemin UNC non supporte par npm Windows)..." -ForegroundColor Cyan
    Write-Host ""

    $buildCmd = "cd $WslProject && bash scripts/build-desktop.sh win"
    wsl.exe -d Ubuntu -- bash -lc $buildCmd

    if ($LASTEXITCODE -ne 0) {
        Write-Host ""
        Write-Host "Si erreur 'permission denied', ouvrez WSL et lancez :" -ForegroundColor Yellow
        Write-Host "  cd /buts4/www/nafy-application" -ForegroundColor Yellow
        Write-Host "  sudo bash scripts/fix-host-permissions.sh" -ForegroundColor Yellow
        Write-Host "  bash scripts/build-desktop.sh win" -ForegroundColor Yellow
        throw "Echec du build WSL (code $LASTEXITCODE)"
    }
}

function Get-SourceUnpacked {
    $candidates = @(
        (Join-Path $Root "dist-electron\win-unpacked"),
        $UncSource,
        "\\wsl$\Ubuntu\buts4\www\nafy-application\dist-electron\win-unpacked"
    )

    foreach ($candidate in $candidates) {
        $exe = Join-Path $candidate $ExeName
        if (Test-Path $exe) {
            return $candidate
        }
    }

    throw "Executable introuvable dans dist-electron/win-unpacked. Lancez d'abord le build."
}

function Copy-AppLocally {
    param([string]$Source)

    Write-Host "Copie vers le disque local Windows..." -ForegroundColor Cyan
    Write-Host "  Source : $Source"
    Write-Host "  Cible  : $LocalApp"

    if (Test-Path $LocalApp) {
        Remove-Item $LocalApp -Recurse -Force
    }

    New-Item -ItemType Directory -Path $LocalApp -Force | Out-Null
    Copy-Item -Path (Join-Path $Source "*") -Destination $LocalApp -Recurse -Force
}

function Start-DesktopApp {
    $exe = Join-Path $LocalApp $ExeName

    if (-not (Test-Path $exe)) {
        throw "Executable absent : $exe"
    }

    Write-Host "Lancement : $exe" -ForegroundColor Green
    Start-Process -FilePath $exe
}

Write-Host "=== Kamille Events Manager - application bureau ===" -ForegroundColor Cyan
Write-Host ""

if (Test-IsUncPath $Root) {
    Write-Host "Projet sur chemin reseau WSL : build uniquement via WSL." -ForegroundColor DarkGray
}

$doBuild = (-not $RunOnly)
$doRun = (-not $BuildOnly)

if ($doBuild) {
    Invoke-WslBuild
}

if ($doRun) {
    $source = Get-SourceUnpacked
    Copy-AppLocally -Source $source

    # Supprimer l'ancienne config (peut pointer vers 127.0.0.1 / mauvais vhost)
    $configDir = Join-Path $env:APPDATA "Kamille Events Manager"
    $configFile = Join-Path $configDir "config.json"
    if (Test-Path $configFile) {
        Remove-Item $configFile -Force
        Write-Host "Config reinitialisee (nouvelle URL par defaut)." -ForegroundColor DarkGray
    }

    Start-DesktopApp
}

Write-Host ""
Write-Host "Rappel : Docker doit etre demarre (port 8319)." -ForegroundColor DarkGray
Write-Host "Config : $env:APPDATA\Kamille Events Manager\config.json" -ForegroundColor DarkGray
