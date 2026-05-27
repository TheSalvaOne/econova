# ============================================================
# 10-backup-dc.ps1 - Copia de seguridad del Controlador de Dominio
# VM: DC-ECONOVA (VM2 - 192.168.1.102)
# Ejecutar: PowerShell como Administrador
# Programado: semanal los domingos a las 03:00
#
# Que incluye este backup:
#   - System State (AD DS, DNS, DHCP, GPOs, registro de Windows)
#   - Base de datos NTDS (Active Directory)
#   - Carpeta SYSVOL (scripts de inicio de sesion y politicas)
#   - Configuracion DHCP exportada
#   - Zonas DNS exportadas
# ============================================================

$fecha   = Get-Date -Format "yyyy-MM-dd_HH-mm"
$destino = "C:\Backups\DC\backup_$fecha"
$logfile = "C:\Backups\DC\backup-dc.log"

# Crear estructura de directorios
if (-not (Test-Path "C:\Backups\DC")) {
    New-Item -ItemType Directory -Path "C:\Backups\DC" -Force | Out-Null
}
New-Item -ItemType Directory -Path $destino -Force | Out-Null

Write-Host "Iniciando backup del DC: $fecha" -ForegroundColor Cyan
Add-Content $logfile "`n[$fecha] === INICIO BACKUP DC-ECONOVA ==="

# ── 1. System State Backup (AD DS + DNS + SYSVOL) ────────────
Write-Host "`n[1/4] Backup System State..." -ForegroundColor Yellow
try {
    # Requiere Windows Server Backup (instalar si no esta)
    $feature = Get-WindowsFeature -Name Windows-Server-Backup
    if (-not $feature.Installed) {
        Write-Host "Instalando Windows Server Backup..." -ForegroundColor Yellow
        Install-WindowsFeature -Name Windows-Server-Backup | Out-Null
    }

    $backupPolicy = New-WBPolicy
    $backupTarget = New-WBBackupTarget -VolumePath "$destino\SystemState"
    New-Item -ItemType Directory -Path "$destino\SystemState" -Force | Out-Null
    Add-WBBackupTarget -Policy $backupPolicy -Target $backupTarget
    Add-WBSystemState -Policy $backupPolicy
    Start-WBBackup -Policy $backupPolicy

    Add-Content $logfile "[$fecha] OK - System State backup completado"
    Write-Host "OK - System State backup completado" -ForegroundColor Green
} catch {
    Add-Content $logfile "[$fecha] ERROR - System State: $_"
    Write-Host "ERROR - System State: $_" -ForegroundColor Red

    # Fallback: exportar ntdsutil (base de datos AD)
    Write-Host "Intentando exportacion NTDS..." -ForegroundColor Yellow
    try {
        $ntdsPath = "$destino\NTDS"
        New-Item -ItemType Directory -Path $ntdsPath -Force | Out-Null
        ntdsutil "activate instance ntds" "files" "integrity" quit quit 2>&1 | Out-File "$ntdsPath\integrity.log"
        Add-Content $logfile "[$fecha] OK - NTDS integrity check completado"
    } catch {
        Add-Content $logfile "[$fecha] WARN - NTDS fallback: $_"
    }
}

# ── 2. Exportar configuracion DHCP ───────────────────────────
Write-Host "`n[2/4] Exportando configuracion DHCP..." -ForegroundColor Yellow
try {
    $dhcpFile = "$destino\dhcp-config.xml"
    Export-DhcpServer -File $dhcpFile -Force
    Add-Content $logfile "[$fecha] OK - Configuracion DHCP exportada a $dhcpFile"
    Write-Host "OK - DHCP exportado" -ForegroundColor Green
} catch {
    Add-Content $logfile "[$fecha] ERROR - DHCP: $_"
    Write-Host "ERROR - DHCP: $_" -ForegroundColor Red
}

# ── 3. Exportar zonas DNS ─────────────────────────────────────
Write-Host "`n[3/4] Exportando zonas DNS..." -ForegroundColor Yellow
try {
    $dnsPath = "$destino\DNS"
    New-Item -ItemType Directory -Path $dnsPath -Force | Out-Null

    # Exportar zona econova.local
    Export-DnsServerZone -Name "econova.local" -FileName "econova.local.dns" -ErrorAction SilentlyContinue
    Copy-Item "C:\Windows\System32\dns\econova.local.dns" $dnsPath -ErrorAction SilentlyContinue

    # Listar todos los registros DNS
    Get-DnsServerResourceRecord -ZoneName "econova.local" |
        Export-Csv "$dnsPath\dns-records.csv" -NoTypeInformation -Encoding UTF8

    Add-Content $logfile "[$fecha] OK - Zonas DNS exportadas"
    Write-Host "OK - DNS exportado" -ForegroundColor Green
} catch {
    Add-Content $logfile "[$fecha] ERROR - DNS: $_"
    Write-Host "ERROR - DNS: $_" -ForegroundColor Red
}

# ── 4. Exportar usuarios y grupos de AD ──────────────────────
Write-Host "`n[4/4] Exportando datos de Active Directory..." -ForegroundColor Yellow
try {
    $adPath = "$destino\AD"
    New-Item -ItemType Directory -Path $adPath -Force | Out-Null

    # Usuarios
    Get-ADUser -Filter * -Properties * |
        Select-Object Name, SamAccountName, UserPrincipalName, Enabled,
                      DistinguishedName, LastLogonDate, PasswordLastSet |
        Export-Csv "$adPath\usuarios.csv" -NoTypeInformation -Encoding UTF8

    # Grupos
    Get-ADGroup -Filter * -Properties Members |
        Select-Object Name, GroupCategory, GroupScope, DistinguishedName |
        Export-Csv "$adPath\grupos.csv" -NoTypeInformation -Encoding UTF8

    # OUs
    Get-ADOrganizationalUnit -Filter * |
        Select-Object Name, DistinguishedName |
        Export-Csv "$adPath\ous.csv" -NoTypeInformation -Encoding UTF8

    # GPOs
    Get-GPO -All |
        Select-Object DisplayName, GpoStatus, CreationTime, ModificationTime |
        Export-Csv "$adPath\gpos.csv" -NoTypeInformation -Encoding UTF8

    Add-Content $logfile "[$fecha] OK - Datos AD exportados (usuarios, grupos, OUs, GPOs)"
    Write-Host "OK - Active Directory exportado" -ForegroundColor Green
} catch {
    Add-Content $logfile "[$fecha] ERROR - AD Export: $_"
    Write-Host "ERROR - AD Export: $_" -ForegroundColor Red
}

# ── Comprimir todo en ZIP ─────────────────────────────────────
try {
    Compress-Archive -Path $destino -DestinationPath "$destino.zip" -Force
    Remove-Item -Path $destino -Recurse -Force
    $size = [math]::Round((Get-Item "$destino.zip").Length / 1MB, 2)
    Add-Content $logfile "[$fecha] OK - Comprimido: $destino.zip ($size MB)"
    Write-Host "`nBackup comprimido: $destino.zip ($size MB)" -ForegroundColor Green
} catch {
    Add-Content $logfile "[$fecha] ERROR - Compresion: $_"
    Write-Host "ERROR - Compresion: $_" -ForegroundColor Red
}

# ── Limpiar backups con mas de 90 dias ───────────────────────
# El DC se guarda mas tiempo que la web (90 dias vs 30 dias)
$eliminados = 0
Get-ChildItem "C:\Backups\DC\*.zip" | Where-Object {
    $_.LastWriteTime -lt (Get-Date).AddDays(-90)
} | ForEach-Object {
    Remove-Item $_.FullName -Force
    Add-Content $logfile "[$fecha] LIMPIEZA - Eliminado: $($_.Name)"
    $eliminados++
}

Add-Content $logfile "[$fecha] === FIN BACKUP DC-ECONOVA ==="
Write-Host "`nBackup DC completado. Eliminados $eliminados backups antiguos." -ForegroundColor Green
Write-Host "Log: $logfile" -ForegroundColor Cyan
