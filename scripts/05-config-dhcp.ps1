# ============================================================
# 05-config-dhcp.ps1
# Configurar ambito DHCP en DC-ECONOVA
# VM: DC-ECONOVA (VM2 - 192.168.1.102)
# ============================================================

Add-DhcpServerv4Scope -Name "Red EcoNova" -StartRange "192.168.1.150" -EndRange "192.168.1.200" -SubnetMask "255.255.255.0"
Set-DhcpServerv4OptionValue -ScopeId "192.168.1.0" -DnsServer "192.168.1.102" -Router "192.168.1.1" -DnsDomain "econova.local"
Add-DhcpServerInDC -DnsName "DC-ECONOVA.econova.local" -IpAddress "192.168.1.102"

Write-Host "DHCP configurado. Rango: 192.168.1.150 - 192.168.1.200" -ForegroundColor Green
Get-DhcpServerv4Scope
