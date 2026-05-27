# ============================================================
# 01-config-ip.ps1
# Configurar IP estatica en una VM
# Cambiar los valores segun la VM antes de ejecutar
# ============================================================
$ip      = "192.168.1.101"   # Cambiar: .101 VM1 / .102 VM2 / .103 VM3
$dns1    = "192.168.1.102"   # DC como DNS primario (excepto en VM2: usar 127.0.0.1)
$dns2    = "192.168.1.1"     # Router como DNS alternativo
$gateway = "192.168.1.1"

$NIC = Get-NetAdapter | Where-Object {$_.Status -eq "Up"} | Select-Object -First 1
New-NetIPAddress -InterfaceIndex $NIC.ifIndex -IPAddress $ip -PrefixLength 24 -DefaultGateway $gateway
Set-DnsClientServerAddress -InterfaceIndex $NIC.ifIndex -ServerAddresses ($dns1, $dns2)
Write-Host "IP estatica configurada: $ip" -ForegroundColor Green
