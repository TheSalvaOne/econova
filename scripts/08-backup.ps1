# ============================================================
# EcoNova - Script de copia de seguridad automatica
# VM: WEB-ECONOVA (VM1 - 192.168.1.101)
# Ejecutar: PowerShell como Administrador
# Programado: diario a las 02:00 via Programador de tareas
# ============================================================

$fecha   = Get-Date -Format "yyyy-MM-dd_HH-mm"
$origen  = "C:\xampp\htdocs\econova"
$destino = "C:\Backups\econova_$fecha"
$mysql   = "C:\xampp\mysql\bin\mysqldump.exe"
$logfile = "C:\Backups\backup.log"

# Crear carpeta Backups si no existe
if (-not (Test-Path "C:\Backups")) {
    New-Item -ItemType Directory -Path "C:\Backups" -Force | Out-Null
}

New-Item -ItemType Directory -Path $destino -Force | Out-Null
Write-Host "Iniciando backup: $fecha" -ForegroundColor Cyan

# 1. Backup ficheros web
try {
    Copy-Item -Path $origen -Destination "$destino\web" -Recurse -Force
    Add-Content $logfile "[$fecha] OK - Ficheros web copiados desde $origen"
    Write-Host "OK - Ficheros web copiados" -ForegroundColor Green
} catch {
    Add-Content $logfile "[$fecha] ERROR - Ficheros web: $_"
    Write-Host "ERROR - Ficheros web: $_" -ForegroundColor Red
}

# 2. Backup base de datos MySQL
try {
    $sqlFile = "$destino\econova_db.sql"
    & $mysql -u root --databases econova | Out-File $sqlFile -Encoding UTF8
    Add-Content $logfile "[$fecha] OK - Base de datos exportada a $sqlFile"
    Write-Host "OK - Base de datos exportada" -ForegroundColor Green
} catch {
    Add-Content $logfile "[$fecha] ERROR - Base de datos: $_"
    Write-Host "ERROR - Base de datos: $_" -ForegroundColor Red
}

# 3. Comprimir en ZIP
try {
    Compress-Archive -Path $destino -DestinationPath "$destino.zip" -Force
    Remove-Item -Path $destino -Recurse -Force
    Add-Content $logfile "[$fecha] OK - Comprimido: $destino.zip"
    Write-Host "OK - Backup comprimido: $destino.zip" -ForegroundColor Green
} catch {
    Add-Content $logfile "[$fecha] ERROR - Compresion: $_"
    Write-Host "ERROR - Compresion: $_" -ForegroundColor Red
}

# 4. Eliminar backups con mas de 30 dias
$eliminados = 0
Get-ChildItem "C:\Backups\*.zip" | Where-Object {
    $_.LastWriteTime -lt (Get-Date).AddDays(-30)
} | ForEach-Object {
    Remove-Item $_.FullName -Force
    Add-Content $logfile "[$fecha] LIMPIEZA - Eliminado: $($_.Name)"
    $eliminados++
}
if ($eliminados -gt 0) {
    Write-Host "Limpieza: $eliminados backups antiguos eliminados" -ForegroundColor Yellow
}

Write-Host "`nBackup completado correctamente." -ForegroundColor Green
Write-Host "Ubicacion: $destino.zip" -ForegroundColor Cyan
Write-Host "Log: $logfile" -ForegroundColor Cyan
