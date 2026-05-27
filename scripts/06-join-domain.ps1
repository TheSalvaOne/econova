# ============================================================
# 06-join-domain.ps1
# Unir equipo al dominio econova.local
# VM: WEB-ECONOVA (VM1) o PC-ECONOVA (VM3)
# REQUISITO: DNS debe apuntar a 192.168.1.102 antes de ejecutar
# ============================================================

$cred = Get-Credential -UserName "ECONOVA\Administrador" -Message "Credenciales del administrador del dominio"
Add-Computer -DomainName "econova.local" -Credential $cred -Restart -Force
