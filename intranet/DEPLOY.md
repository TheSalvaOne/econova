# EcoNova Intranet — Configuración Apache y despliegue

## Estructura de ficheros

```
/var/www/intranet/          ← raíz del virtualhost
├── index.php               ← dashboard
├── login.php
├── logout.php
├── includes/
│   ├── config.php          ← ← EDITAR: IPs, contraseñas, DB
│   ├── auth.php
│   └── layout.php
├── assets/css/intranet.css
├── assets/js/intranet.js
├── pages/
│   ├── servicios.php
│   ├── ftp.php
│   ├── tareas.php
│   └── documentos.php
└── data/                   ← creado automáticamente (tareas.json, documentos.json)
```

---

## 1. Copiar ficheros a la VM1

```bash
# Desde el host o vía FTP
sudo mkdir -p /var/www/intranet
sudo cp -r . /var/www/intranet/
sudo chown -R www-data:www-data /var/www/intranet
sudo chmod -R 755 /var/www/intranet
sudo chmod 700 /var/www/intranet/data   # proteger datos internos
```

---

## 2. Configurar Apache — VirtualHost en puerto 8080

```bash
sudo nano /etc/apache2/ports.conf
```
Añadir si no existe:
```
Listen 8080
```

```bash
sudo nano /etc/apache2/sites-available/intranet.conf
```

Contenido del VirtualHost:
```apache
<VirtualHost *:8080>
    ServerName intranet.econova.local
    DocumentRoot /var/www/intranet

    <Directory /var/www/intranet>
        AllowOverride All
        Require all granted
    </Directory>

    # Bloquear acceso externo — solo red local (192.168.1.0/24) y Tailscale (100.64.0.0/10)
    <Location />
        Require ip 192.168.1.0/24
        Require ip 100.64.0.0/10
        Require ip 127.0.0.1
    </Location>

    # Proteger carpeta de datos
    <Directory /var/www/intranet/data>
        Require all denied
    </Directory>

    ErrorLog  ${APACHE_LOG_DIR}/intranet_error.log
    CustomLog ${APACHE_LOG_DIR}/intranet_access.log combined
</VirtualHost>
```

Activar y reiniciar:
```bash
sudo a2ensite intranet.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

---

## 3. DNS interno — Añadir registro en BIND9 (VM2)

```bash
sudo nano /etc/bind/db.econova.local
```

Añadir la línea:
```
intranet    IN    A    192.168.1.101    ; IP de VM1
```

Recargar BIND9:
```bash
sudo rndc reload
```

---

## 4. Generar contraseña de acceso

```bash
# En la VM1, ejecutar:
php -r "echo password_hash('TuPassword123!', PASSWORD_BCRYPT, ['cost'=>12]);"
```

Copiar el resultado y pegarlo en `includes/config.php`:
```php
define('INTRANET_PASS', '$2y$12$...');
```

---

## 5. Verificar acceso

Desde la red local:
```
http://intranet.econova.local:8080
http://192.168.1.101:8080
```

Desde fuera (via Tailscale):
```
http://[IP-Tailscale-VM1]:8080
```

---

## Seguridad implementada

| Medida | Detalle |
|--------|---------|
| Acceso restringido por IP | Solo red 192.168.1.0/24 + Tailscale 100.64.0.0/10 |
| Login con bcrypt | `password_hash()` cost=12 |
| Rate limiting | Máx. 5 intentos por sesión |
| Delay anti-brute-force | `usleep(400000)` en login fallido |
| CSRF en todos los formularios | Token en sesión + `hash_equals()` |
| Carpeta /data protegida | `Require all denied` en Apache |
| Session regeneration | Cada 5 minutos + en cada login |
| Cookies httponly + samesite=Lax | Sin acceso desde JavaScript |
| noindex, nofollow | La intranet no aparece en buscadores |
