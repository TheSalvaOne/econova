# ============================================================
# 12-firewall-rules.ps1 - Reglas de Firewall EcoNova
# Ejecutar en CADA VM por separado como Administrador
# Detecta automaticamente en que VM esta y aplica las reglas correspondientes
# ============================================================

$hostname = $env:COMPUTERNAME
Write-Host "Configurando firewall en: $hostname" -ForegroundColor Cyan

# ── Regla comun a todas las VMs: ICMP (ping) ─────────────────
New-NetFirewallRule -DisplayName "EcoNova - ICMP" `
    -Protocol ICMPv4 -IcmpType 8 `
    -Enabled True -Action Allow -Direction Inbound `
    -ErrorAction SilentlyContinue | Out-Null
Write-Host "OK - ICMP (ping) habilitado" -ForegroundColor Green

# ── Reglas especificas por VM ─────────────────────────────────
if ($hostname -eq "WEB-ECONOVA") {

    Write-Host "`nAplicando reglas VM1 - WEB-ECONOVA..." -ForegroundColor Yellow

    # HTTP - Web EcoNova publica
    New-NetFirewallRule -DisplayName "EcoNova - HTTP 80" `
        -Protocol TCP -LocalPort 80 `
        -Enabled True -Action Allow -Direction Inbound | Out-Null
    Write-Host "OK - Puerto 80 (HTTP)" -ForegroundColor Green

    # Intranet - solo red local y Tailscale
    New-NetFirewallRule -DisplayName "EcoNova - Intranet 8080" `
        -Protocol TCP -LocalPort 8080 `
        -Enabled True -Action Allow -Direction Inbound `
        -RemoteAddress "192.168.1.0/24","100.64.0.0/10","127.0.0.1" | Out-Null
    Write-Host "OK - Puerto 8080 (Intranet - solo LAN + Tailscale)" -ForegroundColor Green

    # FTP control
    New-NetFirewallRule -DisplayName "EcoNova - FTP Control 21" `
        -Protocol TCP -LocalPort 21 `
        -Enabled True -Action Allow -Direction Inbound | Out-Null
    Write-Host "OK - Puerto 21 (FTP Control)" -ForegroundColor Green

    # FTP datos modo pasivo
    New-NetFirewallRule -DisplayName "EcoNova - FTP Pasivo 50000-50100" `
        -Protocol TCP -LocalPort "50000-50100" `
        -Enabled True -Action Allow -Direction Inbound | Out-Null
    Write-Host "OK - Puertos 50000-50100 (FTP Pasivo)" -ForegroundColor Green

    # MySQL - solo red local (nunca exponer al exterior)
    New-NetFirewallRule -DisplayName "EcoNova - MySQL 3306" `
        -Protocol TCP -LocalPort 3306 `
        -Enabled True -Action Allow -Direction Inbound `
        -RemoteAddress "192.168.1.0/24","127.0.0.1" | Out-Null
    Write-Host "OK - Puerto 3306 (MySQL - solo LAN)" -ForegroundColor Green

} elseif ($hostname -eq "DC-ECONOVA") {

    Write-Host "`nAplicando reglas VM2 - DC-ECONOVA..." -ForegroundColor Yellow

    # DNS TCP y UDP
    New-NetFirewallRule -DisplayName "EcoNova - DNS TCP 53" `
        -Protocol TCP -LocalPort 53 `
        -Enabled True -Action Allow -Direction Inbound | Out-Null
    New-NetFirewallRule -DisplayName "EcoNova - DNS UDP 53" `
        -Protocol UDP -LocalPort 53 `
        -Enabled True -Action Allow -Direction Inbound | Out-Null
    Write-Host "OK - Puerto 53 TCP/UDP (DNS)" -ForegroundColor Green

    # DHCP
    New-NetFirewallRule -DisplayName "EcoNova - DHCP 67" `
        -Protocol UDP -LocalPort 67 `
        -Enabled True -Action Allow -Direction Inbound | Out-Null
    Write-Host "OK - Puerto 67 UDP (DHCP)" -ForegroundColor Green

    # Active Directory - Kerberos
    New-NetFirewallRule -DisplayName "EcoNova - Kerberos TCP 88" `
        -Protocol TCP -LocalPort 88 `
        -Enabled True -Action Allow -Direction Inbound | Out-Null
    New-NetFirewallRule -DisplayName "EcoNova - Kerberos UDP 88" `
        -Protocol UDP -LocalPort 88 `
        -Enabled True -Action Allow -Direction Inbound | Out-Null
    Write-Host "OK - Puerto 88 TCP/UDP (Kerberos)" -ForegroundColor Green

    # LDAP
    New-NetFirewallRule -DisplayName "EcoNova - LDAP 389" `
        -Protocol TCP -LocalPort 389 `
        -Enabled True -Action Allow -Direction Inbound | Out-Null
    New-NetFirewallRule -DisplayName "EcoNova - LDAP UDP 389" `
        -Protocol UDP -LocalPort 389 `
        -Enabled True -Action Allow -Direction Inbound | Out-Null
    Write-Host "OK - Puerto 389 TCP/UDP (LDAP)" -ForegroundColor Green

    # LDAPS
    New-NetFirewallRule -DisplayName "EcoNova - LDAPS 636" `
        -Protocol TCP -LocalPort 636 `
        -Enabled True -Action Allow -Direction Inbound | Out-Null
    Write-Host "OK - Puerto 636 TCP (LDAPS)" -ForegroundColor Green

    # SMB (comparticion de archivos del dominio)
    New-NetFirewallRule -DisplayName "EcoNova - SMB 445" `
        -Protocol TCP -LocalPort 445 `
        -Enabled True -Action Allow -Direction Inbound | Out-Null
    Write-Host "OK - Puerto 445 TCP (SMB)" -ForegroundColor Green

    # RPC dinamico para AD
    New-NetFirewallRule -DisplayName "EcoNova - RPC 135" `
        -Protocol TCP -LocalPort 135 `
        -Enabled True -Action Allow -Direction Inbound | Out-Null
    Write-Host "OK - Puerto 135 TCP (RPC)" -ForegroundColor Green

} elseif ($hostname -eq "PC-ECONOVA") {

    Write-Host "`nAplicando reglas VM3 - PC-ECONOVA..." -ForegroundColor Yellow
    Write-Host "Solo se necesita ICMP para esta VM (ya aplicado)" -ForegroundColor Green

} else {
    Write-Host "`nAVISO: Hostname '$hostname' no reconocido." -ForegroundColor Yellow
    Write-Host "Ejecuta el script despues de renombrar el equipo." -ForegroundColor Yellow
}

# ── Mostrar resumen de reglas EcoNova ────────────────────────
Write-Host "`n=== Reglas EcoNova activas en $hostname ===" -ForegroundColor Cyan
Get-NetFirewallRule | Where-Object { $_.DisplayName -like "EcoNova*" } |
    Select-Object DisplayName, Enabled, Direction, Action |
    Format-Table -AutoSize
