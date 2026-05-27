# ============================================================
# EcoNova - Registrar backup como tarea programada
# VM: WEB-ECONOVA (VM1 - 192.168.1.101)
# Ejecutar UNA SOLA VEZ como Administrador para programar el backup
# ============================================================

# Crear carpeta de scripts si no existe
if (-not (Test-Path "C:\Scripts")) {
    New-Item -ItemType Directory -Path "C:\Scripts" -Force | Out-Null
}

# Copiar el script de backup a C:\Scripts\
Copy-Item -Path "$PSScriptRoot\08-backup.ps1" -Destination "C:\Scripts\08-backup.ps1" -Force
Write-Host "Script copiado a C:\Scripts\08-backup.ps1" -ForegroundColor Green

# Definir la tarea programada
$action = New-ScheduledTaskAction `
    -Execute "powershell.exe" `
    -Argument '-ExecutionPolicy Bypass -NonInteractive -WindowStyle Hidden -File "C:\Scripts\08-backup.ps1"'

$trigger = New-ScheduledTaskTrigger -Daily -At "02:00"

$settings = New-ScheduledTaskSettingsSet `
    -RunOnlyIfNetworkAvailable:$false `
    -StartWhenAvailable `
    -ExecutionTimeLimit (New-TimeSpan -Hours 1)

$principal = New-ScheduledTaskPrincipal `
    -UserId "SYSTEM" `
    -RunLevel Highest `
    -LogonType ServiceAccount

# Registrar la tarea
Register-ScheduledTask `
    -TaskName "EcoNova - Backup diario" `
    -TaskPath "\EcoNova\" `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Principal $principal `
    -Description "Copia de seguridad diaria de la web EcoNova y su base de datos MySQL. Se ejecuta a las 02:00 cada dia." `
    -Force

Write-Host "`nTarea programada registrada correctamente." -ForegroundColor Green
Write-Host "Nombre: EcoNova - Backup diario" -ForegroundColor Cyan
Write-Host "Horario: Diariamente a las 02:00" -ForegroundColor Cyan
Write-Host "Usuario: SYSTEM (no requiere sesion activa)" -ForegroundColor Cyan
Write-Host "`nPara verificar, abre el Programador de tareas y busca la carpeta EcoNova." -ForegroundColor Yellow

# Ejecutar una vez ahora para verificar que funciona
Write-Host "`nEjecutando backup de prueba..." -ForegroundColor Cyan
Start-ScheduledTask -TaskName "EcoNova - Backup diario" -TaskPath "\EcoNova\"
Start-Sleep -Seconds 5
$lastRun = (Get-ScheduledTaskInfo -TaskName "EcoNova - Backup diario" -TaskPath "\EcoNova\").LastRunTime
Write-Host "Ultima ejecucion: $lastRun" -ForegroundColor Green
