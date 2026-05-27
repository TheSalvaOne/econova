# ============================================================
# 04-create-users.ps1
# Crear OUs y usuarios en Active Directory
# VM: DC-ECONOVA (VM2) - Ejecutar DESPUES del reinicio post-AD
# ============================================================

# OUs
New-ADOrganizationalUnit -Name "Empleados" -Path "DC=econova,DC=local"
New-ADOrganizationalUnit -Name "Servidores" -Path "DC=econova,DC=local"

# Usuarios
$pass = ConvertTo-SecureString "Econova.2026" -AsPlainText -Force
New-ADUser -Name "empleado01" -SamAccountName "empleado01" -UserPrincipalName "empleado01@econova.local" -Path "OU=Empleados,DC=econova,DC=local" -AccountPassword $pass -Enabled $true -ChangePasswordAtLogon $true
New-ADUser -Name "empleado02" -SamAccountName "empleado02" -UserPrincipalName "empleado02@econova.local" -Path "OU=Empleados,DC=econova,DC=local" -AccountPassword $pass -Enabled $true -ChangePasswordAtLogon $true

Write-Host "Usuarios creados: empleado01, empleado02" -ForegroundColor Green
Get-ADUser -Filter * | Select-Object Name, SamAccountName
