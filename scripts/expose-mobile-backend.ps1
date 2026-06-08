# Expose le backend Symfony (port 8319 dans WSL) sur le Wi-Fi Windows.
# Lancer PowerShell EN ADMINISTRATEUR depuis Windows.

$ErrorActionPreference = 'Stop'
$Port = 8319

Write-Host "Recuperation de l'IP WSL..."
$wslIp = (wsl -d Ubuntu hostname -I).Trim().Split(' ')[0]
if (-not $wslIp) {
    throw "Impossible de lire l'IP WSL. Verifiez que WSL/Ubuntu est demarre."
}
Write-Host "IP WSL : $wslIp"

Write-Host "Suppression d'un eventuel portproxy existant sur $Port..."
netsh interface portproxy delete v4tov4 listenport=$Port listenaddress=0.0.0.0 2>$null | Out-Null

Write-Host "Redirection 0.0.0.0:$Port -> ${wslIp}:$Port ..."
netsh interface portproxy add v4tov4 listenport=$Port listenaddress=0.0.0.0 connectport=$Port connectaddress=$wslIp

$ruleName = "Nafy Symfony $Port"
$existingRule = Get-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue
if (-not $existingRule) {
    Write-Host "Ajout regle pare-feu entrant TCP $Port ..."
    New-NetFirewallRule -DisplayName $ruleName -Direction Inbound -Action Allow -Protocol TCP -LocalPort $Port | Out-Null
} else {
    Write-Host "Regle pare-feu deja presente."
}

$wifiIp = (Get-NetIPAddress -AddressFamily IPv4 |
    Where-Object {
        $_.InterfaceAlias -eq 'Wi-Fi' -and
        $_.IPAddress -notmatch '^169\.'
    }).IPAddress

Write-Host ""
Write-Host "Configuration terminee."
Write-Host "Test PC  : http://127.0.0.1:$Port"
if ($wifiIp) {
    Write-Host "Test tel : http://${wifiIp}:$Port"
    Write-Host "API APK  : http://${wifiIp}:$Port/api"
}
Write-Host ""
Write-Host "Si l'IP WSL change apres un redemarrage WSL, relancez ce script."
