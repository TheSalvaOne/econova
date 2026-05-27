# EcoNova — Proyecto Intermodular 2.º SMR

<div align="center">

**Plataforma de equipos informáticos reacondicionados con segunda vida**

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Windows Server](https://img.shields.io/badge/Windows_Server-2022-0078D4?logo=windows&logoColor=white)
![Proxmox](https://img.shields.io/badge/Proxmox-VE_9.x-E57000?logo=proxmox&logoColor=white)
![Tailscale](https://img.shields.io/badge/Tailscale-Zero_Trust-244654?logo=tailscale&logoColor=white)

*CDM FP · Ciclo Formativo Grado Medio — Sistemas Microinformáticos y Redes · Curso 2024–2025*

</div>

---

## Índice

1. [Descripción del proyecto](#1-descripción-del-proyecto)
2. [Infraestructura — Proxmox VE](#2-infraestructura--proxmox-ve)
3. [Topología de red](#3-topología-de-red)
4. [Máquinas virtuales](#4-máquinas-virtuales)
5. [Active Directory](#5-active-directory)
6. [Plataforma web EcoNova](#6-plataforma-web-econova)
7. [Intranet corporativa](#7-intranet-corporativa)
8. [Servicios en red](#8-servicios-en-red)
9. [Seguridad implementada](#9-seguridad-implementada)
10. [Scripts de administración](#10-scripts-de-administración)
11. [Copias de seguridad automáticas](#11-copias-de-seguridad-automáticas)
12. [Acceso externo — Tailscale](#12-acceso-externo--tailscale)
13. [Estructura del repositorio](#13-estructura-del-repositorio)
14. [Credenciales de prueba](#14-credenciales-de-prueba)
15. [Módulos profesionales cubiertos](#15-módulos-profesionales-cubiertos)
16. [Fuentes y referencias](#16-fuentes-y-referencias)

---

## 1. Descripción del proyecto

**EcoNova** es una plataforma web de venta de equipos informáticos reacondicionados procedentes de renovaciones de flotas corporativas. El proyecto integra el desarrollo de una aplicación web completa con una infraestructura de red virtualizada sobre Proxmox VE, demostrando los conocimientos adquiridos en todos los módulos del ciclo de 2.º SMR.

### Modelo de negocio

EcoNova recoge equipos de empresa que son dados de baja por renovación de flota, los diagnostica, repara y clasifica por **grado de reacondicionamiento** (A, B o C) y los vende con **2 años de garantía** a un precio hasta un 70% inferior al nuevo. El concepto se basa en la **economía circular**: alargar la vida útil de la tecnología para reducir residuos electrónicos.

### Referencia de mercado

La inspiración de catálogo y productos proviene de **Borax.es**, empresa líder española en informática reacondicionada profesional. Los datos del catálogo han sido generados de forma realista sin hacer scraping real, para evitar bloqueos.

---

## 2. Infraestructura — Proxmox VE

### Hardware físico (servidor anfitrión)

| Componente | Especificación |
|---|---|
| Modelo | GMKtec NucBox K12 |
| Procesador | AMD Ryzen 7 H255 (16 hilos) · Radeon 780M |
| RAM | 32 GB DDR5 (28.18 GiB reconocidos por Proxmox) |
| Almacenamiento | 1 TB SSD NVMe |
| Red | Ethernet Gigabit · Switch TP-Link TL-SG608E (8 puertos gestionable) |
| Hipervisor | **Proxmox VE 9.x** (Debian Trixie · Kernel 7.0.2-6-pve) |
| IP gestión | `192.168.1.100` · Interfaz web: `https://192.168.1.100:8006` |

### ¿Por qué Proxmox?

Proxmox VE es un hipervisor de **tipo 1** (bare-metal) de código abierto. A diferencia de VirtualBox o VMware Workstation (tipo 2), corre directamente sobre el hardware sin sistema operativo base, lo que le da acceso directo a los recursos físicos y mejor rendimiento. Es el estándar en entornos PYME y laboratorios de FP.

---

## 3. Topología de red

```
                          INTERNET
                              │
                         Router casa
                       192.168.1.1
                              │
                   Switch TP-Link TL-SG608E
                   (8 puertos Gigabit)
                   ┌──────────┼──────────┐
                   │          │          │
            192.168.1.100  192.168.1.101  └── Portátil alumno
            Proxmox VE    VM1            (DHCP router)
                          WEB-ECONOVA    Acceso Tailscale
                          │
                   ┌──────┴──────┐
                   │             │
            192.168.1.102  192.168.1.103
            VM2            VM3
            DC-ECONOVA     PC-ECONOVA
            (AD DS·DNS·    (Windows 10 Pro
             DHCP)          Cliente empleado)

DHCP server: DC-ECONOVA · Rango: 192.168.1.150 – 192.168.1.200
DNS principal: 192.168.1.102 (DC integrado con AD)
Dominio: econova.local

── Tailscale (WireGuard) ──────────────────────────────
Portátil alumno (datos móviles) ←→ VM1 WEB-ECONOVA
                                ←→ Proxmox host
Cifrado extremo a extremo · Sin apertura de puertos
```

> El diagrama visual se encuentra en [`docs/topologia-red.svg`](docs/topologia-red.svg)

### Tabla de direccionamiento

| Equipo | IP | Función | DNS |
|---|---|---|---|
| Router | 192.168.1.1 | Puerta de enlace | — |
| Proxmox host | 192.168.1.100 | Hipervisor · gestión :8006 | 192.168.1.1 |
| VM1 WEB-ECONOVA | 192.168.1.101 | Web + Intranet + FTP | 192.168.1.102 |
| VM2 DC-ECONOVA | 192.168.1.102 | AD DS + DNS + DHCP | 127.0.0.1 |
| VM3 PC-ECONOVA | 192.168.1.103 | Cliente empleado | 192.168.1.102 |
| Rango DHCP | .150 – .200 | Equipos adicionales dinámicos | 192.168.1.102 |

---

## 4. Máquinas virtuales

### VM1 — WEB-ECONOVA

| Parámetro | Valor |
|---|---|
| ID Proxmox | 101 |
| SO | Windows Server 2022 Eval (180 días) · con Experiencia de escritorio |
| Máquina | q35 · SeaBIOS · Qemu Agent activado |
| vCPU | 4 núcleos |
| RAM | 8 GB (8192 MiB) |
| Disco | 80 GB · IDE · local-lvm |
| Red | Intel E1000 · puente vmbr0 |
| IP | 192.168.1.101 (estática) |
| Servicios | XAMPP (Apache + PHP + MySQL) · Intranet :8080 · FileZilla Server · Tailscale |

### VM2 — DC-ECONOVA

| Parámetro | Valor |
|---|---|
| ID Proxmox | 102 |
| SO | Windows Server 2022 Eval (180 días) · con Experiencia de escritorio |
| Máquina | q35 · SeaBIOS · Qemu Agent activado |
| vCPU | 4 núcleos |
| RAM | 8 GB (8192 MiB) |
| Disco | 80 GB · IDE · local-lvm |
| Red | Intel E1000 · puente vmbr0 |
| IP | 192.168.1.102 (estática) |
| Roles | AD DS · DNS Server · DHCP Server |
| Tailscale | ❌ DC aislado sin acceso externo |

### VM3 — PC-ECONOVA

| Parámetro | Valor |
|---|---|
| ID Proxmox | 103 |
| SO | Windows 10 Pro (obligatorio Pro para unirse a dominio) |
| Máquina | q35 · SeaBIOS · Qemu Agent activado |
| vCPU | 2 núcleos |
| RAM | 6 GB (6144 MiB) |
| Disco | 60 GB · IDE · local-lvm |
| Red | Intel E1000 · puente vmbr0 |
| IP | 192.168.1.103 (estática) |
| Dominio | econova.local · login con empleado01 / empleado02 |

---

## 5. Active Directory

| Parámetro | Valor |
|---|---|
| Dominio | `econova.local` |
| NetBIOS | `ECONOVA` |
| DC | DC-ECONOVA · 192.168.1.102 |
| Nivel funcional | Windows Server 2016 |
| DNS integrado | Sí — zona econova.local gestionada automáticamente |

### Unidades organizativas

```
econova.local
├── OU=Empleados
│   ├── empleado01
│   └── empleado02
└── OU=Servidores
    └── WEB-ECONOVA (equipo)
```

### Políticas de grupo (GPO)

| GPO | Configuración |
|---|---|
| Contraseñas | Mínimo 8 chars · mayúscula + número + símbolo |
| Bloqueo | 5 intentos fallidos → bloqueo 15 minutos |
| Caducidad | 90 días |
| Escritorio | Fondo corporativo EcoNova aplicado via GPO |

### DHCP

- **Ámbito:** `Red EcoNova` · rango `192.168.1.150 – 192.168.1.200`
- **Máscara:** `255.255.255.0`
- **Puerta de enlace:** `192.168.1.1`
- **DNS asignado:** `192.168.1.102`
- **Dominio DNS:** `econova.local`

---

## 6. Plataforma web EcoNova

### Stack tecnológico

```
┌─────────────────────────────────────────────┐
│  VM1 WEB-ECONOVA · 192.168.1.101            │
│                                              │
│  Apache 2.4  ──→  PHP 8.x  ──→  MySQL 8    │
│  (XAMPP)          (lógica)      (datos)     │
│                                              │
│  URL: http://192.168.1.101/econova          │
└─────────────────────────────────────────────┘
```

### Estructura de ficheros

```
econova/
├── index.php               # Página de inicio
├── econova.sql             # Schema BD + 30 productos seed
├── includes/
│   ├── config.php          # Configuración global
│   ├── db.php              # PDO singleton (prepared statements)
│   ├── security.php        # CSRF · auth · rate limiting · audit
│   ├── header.php          # Cabecera común
│   └── footer.php
├── assets/
│   ├── css/style.css       # ~700 líneas · variables CSS · responsive
│   ├── js/main.js          # AJAX favoritos · auto-submit filtros
│   └── img/                # SVG vectoriales por categoría
├── pages/
│   ├── catalogo.php        # Filtros dinámicos · paginación
│   ├── producto.php        # Ficha + specs JSON + relacionados
│   ├── login.php           # Rate limiting · CSRF · bcrypt
│   ├── registro.php        # Validación contraseña
│   ├── carrito.php         # Carrito → presupuesto
│   ├── favoritos.php       # Lista de favoritos
│   ├── mis-presupuestos.php
│   ├── mi-cuenta.php       # Cambio de contraseña
│   ├── sobre-nosotros.php
│   ├── contacto.php
│   └── como-funciona.php
└── admin/
    ├── index.php           # Dashboard con stats
    ├── productos.php       # Activar/desactivar productos
    ├── presupuestos.php    # Estados: pendiente→aprobado
    ├── presupuesto-detalle.php
    ├── usuarios.php
    └── audit.php           # Últimas 100 acciones
```

### Base de datos

| Tabla | Descripción |
|---|---|
| `usuarios` | Clientes y admins · bcrypt cost=12 · soft delete |
| `productos` | Catálogo · specs en JSON · grado A/B/C |
| `categorias` | 5 categorías con slugs para URLs limpias |
| `favoritos` | N:M · PK compuesta evita duplicados en BD |
| `carrito` | Temporal · se convierte en presupuesto |
| `presupuestos` | Cabecera con estado (pendiente/revisando/aprobado/rechazado) |
| `presupuesto_items` | Líneas con precio_unitario histórico |
| `audit_log` | 11 tipos de acciones · IP · usuario · timestamp |

### Instalación

```bash
# 1. Copiar ficheros en VM1
C:\xampp\htdocs\econova\

# 2. Importar BD en phpMyAdmin
http://localhost/phpmyadmin
→ Nueva BD: econova · utf8mb4_unicode_ci
→ Importar econova.sql

# 3. Editar config
includes/config.php → BASE_URL, DB_USER, DB_PASS

# 4. Acceder
http://192.168.1.101/econova
```

---

## 7. Intranet corporativa

Accesible solo desde la red interna y Tailscale. Corre en el mismo servidor (VM1) pero en un VirtualHost de Apache en el **puerto 8080**.

```
URL red local: http://intranet.econova.local:8080
URL Tailscale: http://[IP-Tailscale]:8080
```

### Módulos

| Módulo | Descripción |
|---|---|
| Dashboard | CPU · RAM · Disco · Uptime · Auto-refresh 30s |
| Servicios | Ping a 6 servicios con latencia en ms |
| FTP | Explorador de archivos · subir · eliminar |
| Tareas | Prioridad alta/media/baja · toggle AJAX |
| Documentos | Repositorio de recursos y manuales internos |

---

## 8. Servicios en red

### DNS — DC-ECONOVA (VM2)

El DNS de **Windows Server** está integrado con Active Directory. Se instala automáticamente al promover el servidor a DC y gestiona la zona `econova.local` para toda la red.

**Configuración de la zona:**

| Parámetro | Valor |
|---|---|
| Zona principal | `econova.local` |
| Tipo | Zona integrada en AD (se replica automáticamente) |
| Actualización dinámica | Segura únicamente (solo equipos del dominio) |
| Servidor autoritativo | DC-ECONOVA · 192.168.1.102 |

**Registros A creados:**

| Nombre | Tipo | IP | Descripción |
|---|---|---|---|
| dc-econova | A | 192.168.1.102 | Controlador de dominio |
| web-econova | A | 192.168.1.101 | Servidor web EcoNova |
| intranet | A | 192.168.1.101 | Intranet corporativa |
| pc-econova | A | 192.168.1.103 | Equipo cliente empleado |
| proxmox | A | 192.168.1.100 | Hipervisor Proxmox |
| econova.local | SOA | 192.168.1.102 | Registro de autoridad |

**Añadir registros desde PowerShell (VM2):**

```powershell
# Añadir registro A para intranet
Add-DnsServerResourceRecordA -ZoneName "econova.local" `
  -Name "intranet" -IPv4Address "192.168.1.101"

# Añadir registro A para proxmox
Add-DnsServerResourceRecordA -ZoneName "econova.local" `
  -Name "proxmox" -IPv4Address "192.168.1.100"

# Verificar todos los registros de la zona
Get-DnsServerResourceRecord -ZoneName "econova.local" | `
  Select-Object HostName, RecordType, RecordData
```

**Verificar resolución DNS desde cualquier VM:**

```powershell
nslookup econova.local
nslookup web-econova.econova.local
nslookup intranet.econova.local
Resolve-DnsName dc-econova.econova.local
```

---

### FTP — VM1 WEB-ECONOVA

**FileZilla Server** instalado en VM1. Permite transferir ficheros entre equipos de la red interna sin necesitar acceso al escritorio remoto.

**Datos de conexión:**

| Parámetro | Valor |
|---|---|
| Servidor | FileZilla Server (Windows) |
| Host | `192.168.1.101` |
| Puerto | `21` |
| Usuario | `ftpuser` |
| Contraseña | Definida en FileZilla Server |
| Modo | **Pasivo (PASV)** — necesario detrás de NAT/router |
| Cifrado | Sin TLS (red interna) |

**Instalación y configuración de FileZilla Server:**

1. Descargar FileZilla Server desde `https://filezilla-project.org`
2. Instalar en VM1 · seleccionar **"Start FileZilla Server as service"**
3. Abrir la interfaz de administración → `127.0.0.1:14147`
4. **Server → Configure → Users → Add**:
   - Usuario: `ftpuser`
   - Contraseña: definir una segura
   - Directorio raíz: `C:\FTP\`
   - Permisos: Read + Write + Delete
5. **Server → Configure → Passive mode settings**:
   - Activar modo pasivo
   - Rango de puertos: `50000-50100`
6. **Regla de firewall** — abrir puertos FTP (ver sección Firewall)

**Conectar desde el explorador de archivos de Windows:**

```
Abrir explorador → barra de dirección → ftp://192.168.1.101
Usuario: ftpuser · Contraseña: la configurada
```

**Conectar con FileZilla Client:**

```
Host:     sftp://192.168.1.101
Puerto:   21
Protocolo: FTP
Usuario:  ftpuser
```

---

### Firewall de Windows — Reglas EcoNova

Las reglas de firewall se aplican en cada VM individualmente. Windows Server 2022 tiene el firewall activado por defecto y bloquea todo lo que no esté explícitamente permitido.

**Reglas necesarias por VM:**

**VM1 — WEB-ECONOVA:**

```powershell
# ICMP (ping)
New-NetFirewallRule -DisplayName "EcoNova - ICMP" `
  -Protocol ICMPv4 -IcmpType 8 -Enabled True -Action Allow -Direction Inbound

# HTTP (web EcoNova)
New-NetFirewallRule -DisplayName "EcoNova - HTTP 80" `
  -Protocol TCP -LocalPort 80 -Enabled True -Action Allow -Direction Inbound

# Intranet
New-NetFirewallRule -DisplayName "EcoNova - Intranet 8080" `
  -Protocol TCP -LocalPort 8080 -Enabled True -Action Allow -Direction Inbound `
  -RemoteAddress 192.168.1.0/24,100.64.0.0/10

# FTP control
New-NetFirewallRule -DisplayName "EcoNova - FTP 21" `
  -Protocol TCP -LocalPort 21 -Enabled True -Action Allow -Direction Inbound

# FTP pasivo (rango de puertos de datos)
New-NetFirewallRule -DisplayName "EcoNova - FTP Pasivo" `
  -Protocol TCP -LocalPort 50000-50100 -Enabled True -Action Allow -Direction Inbound

# MySQL (solo red local, no exponer al exterior)
New-NetFirewallRule -DisplayName "EcoNova - MySQL 3306" `
  -Protocol TCP -LocalPort 3306 -Enabled True -Action Allow -Direction Inbound `
  -RemoteAddress 192.168.1.0/24
```

**VM2 — DC-ECONOVA:**

```powershell
# ICMP (ping)
New-NetFirewallRule -DisplayName "EcoNova - ICMP" `
  -Protocol ICMPv4 -IcmpType 8 -Enabled True -Action Allow -Direction Inbound

# DNS (TCP y UDP)
New-NetFirewallRule -DisplayName "EcoNova - DNS TCP" `
  -Protocol TCP -LocalPort 53 -Enabled True -Action Allow -Direction Inbound
New-NetFirewallRule -DisplayName "EcoNova - DNS UDP" `
  -Protocol UDP -LocalPort 53 -Enabled True -Action Allow -Direction Inbound

# DHCP
New-NetFirewallRule -DisplayName "EcoNova - DHCP" `
  -Protocol UDP -LocalPort 67 -Enabled True -Action Allow -Direction Inbound

# Active Directory (Kerberos, LDAP, RPC)
New-NetFirewallRule -DisplayName "EcoNova - Kerberos" `
  -Protocol TCP -LocalPort 88 -Enabled True -Action Allow -Direction Inbound
New-NetFirewallRule -DisplayName "EcoNova - LDAP" `
  -Protocol TCP -LocalPort 389 -Enabled True -Action Allow -Direction Inbound
New-NetFirewallRule -DisplayName "EcoNova - LDAPS" `
  -Protocol TCP -LocalPort 636 -Enabled True -Action Allow -Direction Inbound
```

**VM3 — PC-ECONOVA:**

```powershell
# ICMP (ping)
New-NetFirewallRule -DisplayName "EcoNova - ICMP" `
  -Protocol ICMPv4 -IcmpType 8 -Enabled True -Action Allow -Direction Inbound
```

**Verificar reglas activas en cualquier VM:**

```powershell
# Ver todas las reglas EcoNova
Get-NetFirewallRule | Where-Object { $_.DisplayName -like "EcoNova*" } | `
  Select-Object DisplayName, Enabled, Direction, Action | Format-Table

# Ver puertos en escucha
netstat -ano | findstr "LISTENING"
```

**Tabla resumen de puertos abiertos:**

| Puerto | Protocolo | VM | Servicio | Acceso |
|---|---|---|---|---|
| 80 | TCP | VM1 | Apache (web EcoNova) | Red completa |
| 8080 | TCP | VM1 | Apache (intranet) | Solo LAN + Tailscale |
| 21 | TCP | VM1 | FTP control | Red completa |
| 50000-50100 | TCP | VM1 | FTP datos (pasivo) | Red completa |
| 3306 | TCP | VM1 | MySQL | Solo LAN |
| 53 | TCP/UDP | VM2 | DNS | Red completa |
| 67 | UDP | VM2 | DHCP | Red completa |
| 88 | TCP | VM2 | Kerberos (AD) | Red completa |
| 389 | TCP | VM2 | LDAP (AD) | Red completa |
| 8006 | TCP | Proxmox | Panel Proxmox | Red completa |



---

## 9. Seguridad implementada

### Web EcoNova (OWASP Top 10)

| Medida | Implementación |
|---|---|
| SQL Injection | PDO prepared statements reales · `emulate_prepares=false` |
| CSRF | Token 32 bytes por sesión · `hash_equals()` en cada POST |
| Bcrypt | `password_hash()` cost=12 · nunca MD5/SHA1 |
| Rate limiting | 5 intentos → bloqueo 15 min · delay 500ms |
| Session fixation | `session_regenerate_id(true)` en login + cada 5 min |
| HTTP Headers | CSP · X-Frame-Options · X-Content-Type-Options · Referrer-Policy |
| Audit log | 11 acciones registradas con IP · usuario · timestamp |
| Sanitización | `htmlspecialchars()` · `strip_tags()` · `filter_var()` |

### Intranet

| Medida | Detalle |
|---|---|
| Restricción IP | Solo `192.168.1.0/24` y `100.64.0.0/10` (Tailscale) |
| Login bcrypt | `password_hash()` cost=12 |
| Rate limiting | Máx 5 intentos + delay 400ms |
| CSRF | Token en sesión en todos los formularios |
| `/data` protegido | `Require all denied` en Apache |
| noindex/nofollow | No indexada por buscadores |

---

## 10. Scripts de administración

Todos los scripts están en [`scripts/`](scripts/). Se ejecutan en PowerShell como **Administrador**.

| Script | VM | Descripción |
|---|---|---|
| `01-config-ip.ps1` | VM1/VM2/VM3 | IP estática · DNS · puerta de enlace |
| `02-rename-pc.ps1` | VM1/VM2/VM3 | Renombrar equipo y reiniciar |
| `03-install-ad.ps1` | VM2 | Instalar AD DS + DNS + DHCP |
| `04-create-users.ps1` | VM2 | OUs + usuarios en Active Directory |
| `05-config-dhcp.ps1` | VM2 | Ámbito DHCP .150-.200 |
| `06-join-domain.ps1` | VM1/VM3 | Unir equipo al dominio |
| `07-icmp-rule.ps1` | VM1/VM2/VM3 | Permitir ping entre VMs |
| `08-backup.ps1` | **VM1** | Backup web + BD MySQL (diario 02:00) |
| `09-programar-backup.ps1` | **VM1** | Registra la tarea diaria en Windows |
| `12-firewall-rules.ps1` | VM1/VM2/VM3 | Reglas firewall completas (detecta VM automáticamente) |
| `10-backup-dc.ps1` | **VM2** | Backup DC: System State + AD + DHCP + DNS |
| `11-programar-backup-dc.ps1` | **VM2** | Registra la tarea semanal en Windows |

---

## 11. Copias de seguridad automáticas

### VM1 — WEB-ECONOVA (backup diario)

> Ficheros web + base de datos MySQL · Retención 30 días · Ejecuta a las **02:00**

```powershell
# Programar backup diario (ejecutar una sola vez en VM1)
C:\Scripts\09-programar-backup.ps1
```

Guarda en `C:\Backups\econova_YYYY-MM-DD_HH-mm.zip`:
- `web\` — todos los ficheros PHP, CSS, JS, SVG
- `econova_db.sql` — volcado completo de MySQL

### VM2 — DC-ECONOVA (backup semanal)

> System State + Active Directory + DHCP + DNS · Retención 90 días · Ejecuta los **domingos a las 03:00**

El DC es la VM más crítica. Si se pierde el Active Directory se cae toda la autenticación de la red. Por eso tiene su propio backup con más retención.

```powershell
# Programar backup semanal del DC (ejecutar una sola vez en VM2)
C:\Scripts\11-programar-backup-dc.ps1
```

Guarda en `C:\Backups\DC\backup_YYYY-MM-DD_HH-mm.zip`:
- `SystemState\` — AD DS, SYSVOL, registro Windows
- `dhcp-config.xml` — configuración completa del DHCP
- `DNS\dns-records.csv` — todos los registros DNS
- `AD\usuarios.csv` — usuarios del dominio
- `AD\grupos.csv` — grupos y miembros
- `AD\ous.csv` — unidades organizativas
- `AD\gpos.csv` — políticas de grupo

### Resumen de backups

| VM | Frecuencia | Hora | Retención | Contenido |
|---|---|---|---|---|
| VM1 WEB-ECONOVA | Diario | 02:00 | 30 días | Web EcoNova + MySQL |
| VM2 DC-ECONOVA | Semanal (domingo) | 03:00 | 90 días | System State + AD + DHCP + DNS |
| Proxmox | Manual | — | — | Snapshot completo de VMs |

### Script de backup (`scripts/08-backup.ps1`)

```powershell
# ============================================================
# EcoNova - Script de copia de seguridad automática
# VM: WEB-ECONOVA (VM1 · 192.168.1.101)
# Programado: diario a las 02:00
# ============================================================

$fecha   = Get-Date -Format "yyyy-MM-dd_HH-mm"
$origen  = "C:\xampp\htdocs\econova"
$destino = "C:\Backups\econova_$fecha"
$mysql   = "C:\xampp\mysql\bin\mysqldump.exe"
$logfile = "C:\Backups\backup.log"

# ── Crear directorio de backup ────────────────────────────────
New-Item -ItemType Directory -Path $destino -Force | Out-Null

# ── 1. Backup de ficheros web ────────────────────────────────
try {
    Copy-Item -Path $origen -Destination "$destino\web" -Recurse -Force
    Add-Content $logfile "[$fecha] OK - Ficheros web copiados"
} catch {
    Add-Content $logfile "[$fecha] ERROR - Ficheros web: $_"
}

# ── 2. Backup de la base de datos MySQL ──────────────────────
try {
    $sqlFile = "$destino\econova_db.sql"
    & $mysql -u root --databases econova | Out-File $sqlFile -Encoding UTF8
    Add-Content $logfile "[$fecha] OK - Base de datos exportada"
} catch {
    Add-Content $logfile "[$fecha] ERROR - Base de datos: $_"
}

# ── 3. Comprimir el backup en ZIP ────────────────────────────
try {
    Compress-Archive -Path $destino -DestinationPath "$destino.zip" -Force
    Remove-Item -Path $destino -Recurse -Force
    Add-Content $logfile "[$fecha] OK - Comprimido: $destino.zip"
} catch {
    Add-Content $logfile "[$fecha] ERROR - Compresión: $_"
}

# ── 4. Limpiar backups antiguos (más de 30 días) ─────────────
Get-ChildItem "C:\Backups\*.zip" | Where-Object {
    $_.LastWriteTime -lt (Get-Date).AddDays(-30)
} | ForEach-Object {
    Remove-Item $_.FullName -Force
    Add-Content $logfile "[$fecha] LIMPIEZA - Eliminado: $($_.Name)"
}

Write-Host "Backup completado: $destino.zip" -ForegroundColor Green
```

### Programar el backup automático en Windows

El backup se programa con el **Programador de tareas de Windows** para ejecutarse cada día a las 02:00:

**Pasos en VM1 (WEB-ECONOVA):**

1. Abrir **Programador de tareas** (buscar en el menú inicio)
2. Panel derecho → **Crear tarea...**
3. Pestaña **General:**
   - Nombre: `EcoNova - Backup diario`
   - Descripción: `Copia de seguridad de la web EcoNova y su base de datos MySQL`
   - Marcar: **Ejecutar tanto si el usuario inició sesión como si no**
   - Marcar: **Ejecutar con los privilegios más altos**
4. Pestaña **Desencadenadores** → Nuevo:
   - Iniciar la tarea: **Según una programación**
   - Configuración: **Diariamente**
   - Hora de inicio: `02:00:00`
   - Repetir cada: `1 día`
5. Pestaña **Acciones** → Nueva:
   - Acción: **Iniciar un programa**
   - Programa: `powershell.exe`
   - Argumentos: `-ExecutionPolicy Bypass -NonInteractive -File "C:\Scripts\08-backup.ps1"`
6. Pestaña **Condiciones:**
   - Desmarcar: *Iniciar la tarea solo si el equipo está en CA* (para VMs siempre conectadas)
7. **Aceptar** → introducir contraseña del Administrador

**Alternativa por PowerShell (más rápido):**

```powershell
# Ejecutar en VM1 como Administrador — registra la tarea automáticamente
$action  = New-ScheduledTaskAction -Execute "powershell.exe" `
             -Argument '-ExecutionPolicy Bypass -NonInteractive -File "C:\Scripts\08-backup.ps1"'
$trigger = New-ScheduledTaskTrigger -Daily -At "02:00"
$settings = New-ScheduledTaskSettingsSet -RunOnlyIfNetworkAvailable:$false
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -RunLevel Highest

Register-ScheduledTask -TaskName "EcoNova - Backup diario" `
  -Action $action -Trigger $trigger `
  -Settings $settings -Principal $principal `
  -Description "Backup diario de EcoNova a las 02:00"

Write-Host "Tarea programada registrada correctamente" -ForegroundColor Green
```

### Verificar que el backup funciona

```powershell
# Ejecutar manualmente para probar
& "C:\Scripts\08-backup.ps1"

# Ver el log de backups
Get-Content "C:\Backups\backup.log" | Select-Object -Last 20

# Listar backups disponibles
Get-ChildItem "C:\Backups\*.zip" | Select-Object Name, Length, LastWriteTime
```

### Estructura de los backups

```
C:\Backups\
├── econova_2026-05-26_02-00.zip
│   ├── web\                    ← Todos los ficheros PHP/CSS/JS
│   │   ├── index.php
│   │   ├── includes\
│   │   ├── pages\
│   │   └── admin\
│   └── econova_db.sql          ← Volcado completo de MySQL
├── econova_2026-05-25_02-00.zip
├── econova_2026-05-24_02-00.zip
└── backup.log                  ← Log de todas las operaciones
```

---

## 12. Acceso externo — Tailscale

**Tailscale** es una VPN basada en **WireGuard** con arquitectura Zero Trust. Se instala en VM1 y en el portátil del alumno.

### Por qué Tailscale y no abrir puertos

| Alternativa | Problema |
|---|---|
| Abrir puertos en router | Requiere IP pública fija · expone servicios a Internet |
| VPN tradicional (OpenVPN) | Configuración compleja · requiere servidor dedicado |
| **Tailscale** ✅ | Sin configuración de router · funciona con IP dinámica · cifrado WireGuard |

### Instalación en VM1

```powershell
# Descargar e instalar desde https://tailscale.com/download/windows
# Tras instalación, autenticarse:
tailscale up

# Ver IP asignada (100.x.x.x)
tailscale ip
```

### Instalación en Proxmox host

```bash
# En la Shell de Proxmox
curl -fsSL https://tailscale.com/install.sh | sh
tailscale up
```

### URLs de acceso externo

```
Web EcoNova:  http://[IP-Tailscale-VM1]/econova
Intranet:     http://[IP-Tailscale-VM1]:8080
Proxmox:      https://[IP-Tailscale-Proxmox]:8006
```

---

## 13. Estructura del repositorio

```
econova-smr/
├── README.md                    ← Este fichero (memoria técnica)
├── web/
│   ├── econova/                 ← Código fuente web EcoNova
│   │   ├── index.php
│   │   ├── econova.sql
│   │   ├── includes/
│   │   ├── pages/
│   │   ├── admin/
│   │   └── assets/
│   └── intranet/                ← Código fuente intranet
│       ├── index.php
│       ├── login.php
│       ├── includes/
│       ├── pages/
│       └── assets/
├── scripts/
│   ├── 01-config-ip.ps1
│   ├── 02-rename-pc.ps1
│   ├── 03-install-ad.ps1
│   ├── 04-create-users.ps1
│   ├── 05-config-dhcp.ps1
│   ├── 06-join-domain.ps1
│   ├── 07-icmp-rule.ps1
│   └── 08-backup.ps1
├── docs/
│   ├── topologia-red.svg        ← Diagrama de red
│   ├── EcoNova_Memoria_Web.docx ← Memoria técnica completa
│   ├── EcoNova_Presentacion.pptx
│   └── internos/
│       ├── DOC-TEC-001_Manual_Administracion.docx
│       ├── DOC-RH-001_Alta_Nuevo_Empleado.docx
│       ├── DOC-SEG-001_Politica_Seguridad.docx
│       └── DOC-USR-001_Guia_Intranet.docx
└── configs/
    ├── httpd-vhosts.conf        ← VirtualHost Apache (intranet)
    └── dhcp-scope.txt           ← Configuración ámbito DHCP
```

---

## 14. Credenciales de prueba

> ⚠️ Solo para entorno de laboratorio. Nunca usar estas contraseñas en producción.

| Acceso | Usuario | Contraseña |
|---|---|---|
| Web EcoNova (admin) | `admin@econova.local` | `Fp.2026` |
| Web EcoNova (cliente) | Registrarse en `/pages/registro.php` | — |
| Intranet | `admin` | Consultar `includes/config.php` |
| Administrador dominio | `ECONOVA\Administrador` | `Fp.2026` |
| Contraseña DSRM | — | `Fp.2026` |
| empleado01 | `empleado01` | `Econova.2026` |
| empleado02 | `empleado02` | `Econova.2026` |
| Proxmox | `root` | (definida en instalación) |
| phpMyAdmin | `root` | (vacía en XAMPP local) |

---

## 15. Módulos profesionales cubiertos

| Módulo | Evidencias en el proyecto |
|---|---|
| **Seguridad Informática** | OWASP Top 10 · SQL Injection · CSRF · bcrypt · Rate limiting · Audit log · HTTP Headers |
| **Aplicaciones Web** | PHP 8 + MySQL · MVC · AJAX · Panel admin · Paginación · Sesiones seguras |
| **Servicios en Red** | Apache · DNS econova.local · FTP FileZilla · HTTP GET/POST · VirtualHost |
| **Sistemas Operativos en Red** | Windows Server 2022 · AD DS · GPOs · PowerShell · Proxmox VMs · VirtIO |
| **Digitalización Productiva** | EcoNova digitaliza venta de equipos · catálogo online · presupuestos automatizados |
| **Sostenibilidad** | Economía circular · CO₂ evitado · grados A/B/C · datos E-Waste Monitor ONU |
| **Empleabilidad II** | Esta memoria · GitHub como portfolio · simulación de startup · documentos internos |
| **Inglés Profesional** | Documentación OWASP/MDN/MySQL en inglés · vocabulario técnico en código · slide en inglés |

---

## 16. Fuentes y referencias

### Documentación oficial
- [PHP Manual — PDO](https://www.php.net/manual/es/book.pdo.php)
- [MySQL 8.0 Reference Manual](https://dev.mysql.com/doc/refman/8.0/en/)
- [OWASP Top 10 — 2021](https://owasp.org/Top10/es/)
- [MDN Web Docs — HTTP](https://developer.mozilla.org/es/docs/Web/HTTP)
- [Proxmox VE Documentation](https://pve.proxmox.com/wiki/Main_Page)
- [Tailscale Documentation](https://tailscale.com/kb/)

### Recursos de aprendizaje
- Píldoras Informáticas — PHP desde cero (YouTube)
- MitoCode — PHP con MySQL (YouTube)
- Midudev — CSS Grid y Flexbox (YouTube)
- s4vitar — Seguridad web ofensiva/defensiva (YouTube)

### Herramientas utilizadas
- [XAMPP](https://www.apachefriends.org) — Entorno de desarrollo local
- [Proxmox VE](https://www.proxmox.com) — Hipervisor
- [Tailscale](https://tailscale.com) — VPN Zero Trust
- [FileZilla Server](https://filezilla-project.org) — Servidor FTP
- [Visual Studio Code](https://code.visualstudio.com) — Editor de código
- [Claude AI (Anthropic)](https://claude.ai) — Apoyo técnico en desarrollo

---

<div align="center">

**EcoNova · Tecnología con segunda vida**

*Proyecto Intermodular 2.º SMR · CDM FP · 2024–2025*

</div>
