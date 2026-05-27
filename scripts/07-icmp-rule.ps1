# ============================================================
# 07-icmp-rule.ps1
# Permitir ping (ICMP) entre VMs
# Ejecutar en CADA VM por separado
# ============================================================

New-NetFirewallRule -DisplayName "Permitir ICMP EcoNova" -Protocol ICMPv4 -IcmpType 8 -Enabled True -Action Allow -Direction Inbound
Write-Host "Regla ICMP creada. Ahora puedes hacer ping a esta VM." -ForegroundColor Green
