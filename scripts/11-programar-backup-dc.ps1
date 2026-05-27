# ============================================================
# 11-programar-backup-dc.ps1
# Registrar backup del DC como tarea programada semanal
# VM: DC-ECONOVA (VM2) - Ejecutar UNA SOLA VEZ como Administrador
# ============================================================

# Copiar script a C:\Scripts\
if (-not (Test-Path "C:\Scripts")) {
    New-Item -ItemType Directory -Path "C:\Scripts" -Force | Out-Null
}
Copy-Item -Path "$PSScriptRoot\10-backup-dc.ps1" -Destination "C:\Scripts\10-backup-dc.ps1" -Force
Write-Host "Script copiado a C:\Scripts\10-backup-dc.ps1" -ForegroundColor Green

# Instalar Windows Server Backup si no esta instalado
$feature = Get-WindowsFeature -Name Windows-Server-Backup
if (-not $feature.Installed) {
    Write-Host "Instalando Windows Server Backup..." -ForegroundColor Yellow
    Install-WindowsFeature -Name Windows-Server-Backup | Out-Null
    Write-Host "OK - Windows Server Backup instalado" -ForegroundColor Green
}

# Definir la tarea - semanal los domingos a las 03:00
$action = New-ScheduledTaskAction `
    -Execute "powershell.exe" `
    -Argument '-ExecutionPolicy Bypass -NonInteractive -WindowStyle Hidden -File "C:\Scripts\10-backup-dc.ps1"'

# Domingos a las 03:00 (una hora despues del backup de VM1)
$trigger = New-ScheduledTaskTrigger -Weekly -DaysOfWeek Sunday -At "03:00"

$settings = New-ScheduledTaskSettingsSet `
    -RunOnlyIfNetworkAvailable:$false `
    -StartWhenAvailable `
    -ExecutionTimeLimit (New-TimeSpan -Hours 2)

$principal = New-ScheduledTaskPrincipal `
    -UserId "SYSTEM" `
    -RunLevel Highest `
    -LogonType ServiceAccount

Register-ScheduledTask `
    -TaskName "EcoNova - Backup DC semanal" `
    -TaskPath "\EcoNova\" `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Principal $principal `
    -Description "Backup semanal del Controlador de Dominio: System State, DHCP, DNS y Active Directory. Se ejecuta los domingos a las 03:00." `
    -Force

Write-Host "`nTarea programada registrada:" -ForegroundColor Green
Write-Host "  Nombre:   EcoNova - Backup DC semanal" -ForegroundColor Cyan
Write-Host "  Horario:  Domingos a las 03:00" -ForegroundColor Cyan
Write-Host "  Incluye:  System State + AD + DHCP + DNS" -ForegroundColor Cyan
Write-Host "  Retencion: 90 dias" -ForegroundColor Cyan
Write-Host "  Ubicacion: C:\Backups\DC\" -ForegroundColor Cyan

# Ejecutar ahora para verificar
Write-Host "`nEjecutando backup de prueba..." -ForegroundColor Yellow
Start-ScheduledTask -TaskName "EcoNova - Backup DC semanal" -TaskPath "\EcoNova\"
Write-Host "Backup iniciado en segundo plano. Revisa C:\Backups\DC\ en unos minutos." -ForegroundColor Green
