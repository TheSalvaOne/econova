# ============================================================
# 03-install-ad.ps1
# Instalar AD DS + DNS + DHCP y promover a DC
# VM: DC-ECONOVA (VM2 - 192.168.1.102)
# ============================================================

# 1. Instalar roles
Install-WindowsFeature -Name AD-Domain-Services, DNS, DHCP -IncludeManagementTools
Write-Host "Roles instalados. Promoviendo a controlador de dominio..." -ForegroundColor Cyan

# 2. Crear dominio econova.local
Install-ADDSForest -DomainName "econova.local" -DomainNetbiosName "ECONOVA" -InstallDns -Force
# El servidor se reiniciara automaticamente
