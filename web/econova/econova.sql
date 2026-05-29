-- ============================================================
-- ECONOVA - Base de datos
-- Motor: MySQL 8.x / MariaDB
-- Seguridad: passwords con password_hash(), prepared statements
-- ============================================================

CREATE DATABASE IF NOT EXISTS econova
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE econova;

-- -------------------------------------------------------
-- USUARIOS
-- -------------------------------------------------------
CREATE TABLE usuarios (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(100)        NOT NULL,
  email       VARCHAR(180)        NOT NULL UNIQUE,
  password    VARCHAR(255)        NOT NULL,          -- bcrypt via password_hash()
  rol         ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  activo      TINYINT(1)          NOT NULL DEFAULT 1,
  created_at  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_rol   (rol)
) ENGINE=InnoDB;

-- Admin por defecto  (password: Fp.2026)
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador', 'admin@econova.local',
 '$2y$12$zRj4H.A5IkRc47ZUDIftZus.hYGIYtBbh4dAQKNQZ.lEoaCvcSrsG', 'admin');

-- -------------------------------------------------------
-- CATEGORÍAS
-- -------------------------------------------------------
CREATE TABLE categorias (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(80)  NOT NULL,
  slug        VARCHAR(80)  NOT NULL UNIQUE,
  descripcion TEXT,
  icono       VARCHAR(50)           -- nombre de icono (p.ej. lucide)
) ENGINE=InnoDB;

INSERT INTO categorias (nombre, slug, descripcion, icono) VALUES
('Ordenadores de sobremesa', 'ordenadores',   'Torres y mini PCs reacondicionados de empresas',          'monitor'),
('Portátiles',              'portatiles',     'Portátiles profesionales con segunda vida',                'laptop'),
('Monitores',               'monitores',      'Pantallas de alta calidad revisadas y garantizadas',       'tv'),
('Servidores',              'servidores',     'Servidores y workstations para entornos exigentes',        'server'),
('Accesorios',              'accesorios',     'Teclados, ratones, docks y periféricos reacondicionados',  'package');

-- -------------------------------------------------------
-- PRODUCTOS
-- -------------------------------------------------------
CREATE TABLE productos (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id    INT UNSIGNED        NOT NULL,
  nombre          VARCHAR(200)        NOT NULL,
  slug            VARCHAR(200)        NOT NULL UNIQUE,
  descripcion     TEXT,
  especificaciones JSON,                             -- {cpu, ram, almacenamiento, os, grado}
  precio          DECIMAL(8,2)        NOT NULL,
  precio_original DECIMAL(8,2),                     -- PVP nuevo, para mostrar ahorro
  stock           SMALLINT UNSIGNED   NOT NULL DEFAULT 0,
  grado           ENUM('A','B','C')   NOT NULL DEFAULT 'A',  -- A=como nuevo, B=bueno, C=funcional
  imagen          VARCHAR(255),
  activo          TINYINT(1)          NOT NULL DEFAULT 1,
  created_at      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (categoria_id) REFERENCES categorias(id),
  INDEX idx_categoria (categoria_id),
  INDEX idx_activo    (activo),
  INDEX idx_precio    (precio)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- SEED: 30 productos realistas (datos inspirados en Borax)
-- -------------------------------------------------------
INSERT INTO productos (categoria_id, nombre, slug, descripcion, especificaciones, precio, precio_original, stock, grado, imagen) VALUES

-- ORDENADORES (cat 1)
(1, 'HP EliteDesk 800 G4 SFF', 'hp-elitedesk-800-g4-sff',
 'Ordenador de sobremesa empresarial de altas prestaciones procedente de renovación de flota corporativa. Revisado y testeado al 100%.',
 '{"cpu":"Intel Core i5-8500","ram":"16 GB DDR4","almacenamiento":"SSD 256 GB","os":"Windows 11 Pro","grado":"A"}',
 189.00, 650.00, 12, 'A', 'hp-elitedesk-800-g4.jpg'),

(1, 'Dell OptiPlex 7060 MT', 'dell-optiplex-7060-mt',
 'Torre compacta Dell con procesador de 8ª generación. Ideal para ofimática y teletrabajo exigente.',
 '{"cpu":"Intel Core i7-8700","ram":"16 GB DDR4","almacenamiento":"SSD 512 GB","os":"Windows 11 Pro","grado":"A"}',
 249.00, 900.00, 8, 'A', 'dell-optiplex-7060.jpg'),

(1, 'Lenovo ThinkCentre M720q Mini PC', 'lenovo-thinkcentre-m720q',
 'Mini PC ultracompacto con potencia de sobremesa. Perfecto para espacios reducidos sin renunciar al rendimiento.',
 '{"cpu":"Intel Core i5-8400T","ram":"8 GB DDR4","almacenamiento":"SSD 256 GB","os":"Windows 10 Pro","grado":"A"}',
 159.00, 550.00, 15, 'A', 'lenovo-m720q.jpg'),

(1, 'HP ProDesk 600 G3 SFF', 'hp-prodesk-600-g3-sff',
 'Compacto y fiable. Procedente de entidad bancaria, uso exclusivamente ofimático.',
 '{"cpu":"Intel Core i5-7500","ram":"8 GB DDR4","almacenamiento":"SSD 240 GB","os":"Windows 10 Pro","grado":"B"}',
 129.00, 480.00, 20, 'B', 'hp-prodesk-600-g3.jpg'),

(1, 'Dell OptiPlex 3060 Micro', 'dell-optiplex-3060-micro',
 'Factor de forma ultra pequeño. Ideal para montaje en VESA detrás del monitor.',
 '{"cpu":"Intel Core i5-8500T","ram":"8 GB DDR4","almacenamiento":"SSD 256 GB","os":"Windows 11 Pro","grado":"A"}',
 179.00, 600.00, 10, 'A', 'dell-optiplex-3060-micro.jpg'),

(1, 'Lenovo ThinkCentre M910s', 'lenovo-thinkcentre-m910s',
 'Sobremesa slim de alta fiabilidad. Componentes de gama empresarial garantizados.',
 '{"cpu":"Intel Core i7-7700","ram":"16 GB DDR4","almacenamiento":"SSD 512 GB","os":"Windows 11 Pro","grado":"A"}',
 219.00, 800.00, 6, 'A', 'lenovo-m910s.jpg'),

-- PORTÁTILES (cat 2)
(2, 'HP EliteBook 840 G6', 'hp-elitebook-840-g6',
 'Portátil empresarial premium con pantalla Full HD IPS antirreflejo. Batería reemplazada, autonomía real de 7h.',
 '{"cpu":"Intel Core i5-8365U","ram":"16 GB DDR4","almacenamiento":"SSD 512 GB","pantalla":"14 FHD IPS","os":"Windows 11 Pro","grado":"A"}',
 329.00, 1100.00, 9, 'A', 'hp-elitebook-840-g6.jpg'),

(2, 'Lenovo ThinkPad T490', 'lenovo-thinkpad-t490',
 'El ThinkPad más vendido en entornos corporativos. Teclado legendario, durabilidad MIL-SPEC.',
 '{"cpu":"Intel Core i7-8565U","ram":"16 GB DDR4","almacenamiento":"SSD 512 GB","pantalla":"14 FHD IPS","os":"Windows 11 Pro","grado":"A"}',
 369.00, 1300.00, 7, 'A', 'lenovo-thinkpad-t490.jpg'),

(2, 'Dell Latitude 5490', 'dell-latitude-5490',
 'Portátil robusto con lector de huella y TPM 2.0. Procedente de renovación de flota de 200 unidades.',
 '{"cpu":"Intel Core i5-8250U","ram":"8 GB DDR4","almacenamiento":"SSD 256 GB","pantalla":"14 FHD","os":"Windows 10 Pro","grado":"B"}',
 259.00, 900.00, 14, 'B', 'dell-latitude-5490.jpg'),

(2, 'HP ProBook 450 G7', 'hp-probook-450-g7',
 'Portátil 15 pulgadas equilibrado para trabajo diario. Excelente relación precio-prestaciones.',
 '{"cpu":"Intel Core i5-10210U","ram":"8 GB DDR4","almacenamiento":"SSD 256 GB","pantalla":"15.6 FHD","os":"Windows 11 Pro","grado":"A"}',
 289.00, 950.00, 11, 'A', 'hp-probook-450-g7.jpg'),

(2, 'Lenovo ThinkPad X1 Carbon Gen 7', 'lenovo-thinkpad-x1-carbon-g7',
 'El ultrabook empresarial por excelencia. Apenas 1,08 kg con pantalla 2K espectacular.',
 '{"cpu":"Intel Core i7-8565U","ram":"16 GB LPDDR3","almacenamiento":"SSD 512 GB NVMe","pantalla":"14 2K IPS","os":"Windows 11 Pro","grado":"A"}',
 549.00, 1800.00, 4, 'A', 'thinkpad-x1-carbon-g7.jpg'),

(2, 'Dell Latitude 7490', 'dell-latitude-7490',
 'Ultradelgado y ultraresistente. Con 4G LTE integrado para conectividad en cualquier lugar.',
 '{"cpu":"Intel Core i7-8650U","ram":"16 GB DDR4","almacenamiento":"SSD 512 GB NVMe","pantalla":"14 FHD","os":"Windows 11 Pro","grado":"B"}',
 399.00, 1400.00, 6, 'B', 'dell-latitude-7490.jpg'),

(2, 'HP EliteBook 830 G6', 'hp-elitebook-830-g6',
 'Compacto y potente. Pantalla táctil, ideal para profesionales en movimiento.',
 '{"cpu":"Intel Core i5-8365U","ram":"8 GB DDR4","almacenamiento":"SSD 256 GB","pantalla":"13.3 FHD Táctil","os":"Windows 11 Pro","grado":"A"}',
 319.00, 1050.00, 5, 'A', 'hp-elitebook-830-g6.jpg'),

-- MONITORES (cat 3)
(3, 'Dell P2417H 24" Full HD', 'dell-p2417h-24',
 'Monitor profesional IPS con ajuste de altura, giro e inclinación. Biseles ultrafinos.',
 '{"tamaño":"24 pulgadas","resolucion":"1920x1080 FHD","panel":"IPS","conectores":"DP, HDMI, VGA, USB Hub","grado":"A"}',
 89.00, 280.00, 25, 'A', 'dell-p2417h.jpg'),

(3, 'HP EliteDisplay E243 23.8"', 'hp-elitedisplay-e243',
 'Panel IPS antirreflejo con certificación TCO. Diseño sin bordes en tres lados.',
 '{"tamaño":"23.8 pulgadas","resolucion":"1920x1080 FHD","panel":"IPS","conectores":"DP, HDMI, VGA","grado":"A"}',
 79.00, 250.00, 18, 'A', 'hp-e243.jpg'),

(3, 'Lenovo ThinkVision T27h 27" QHD', 'lenovo-thinkvision-t27h',
 'Monitor 27" QHD con USB-C. Para los que quieren espacio de trabajo sin compromisos.',
 '{"tamaño":"27 pulgadas","resolucion":"2560x1440 QHD","panel":"IPS","conectores":"USB-C, DP, HDMI","grado":"A"}',
 189.00, 550.00, 8, 'A', 'lenovo-t27h.jpg'),

(3, 'Dell U2415 24" WUXGA', 'dell-u2415-24-wuxga',
 'Panel IPS de gama UltraSharp. Colores fieles, ideal para diseño y trabajo creativo.',
 '{"tamaño":"24 pulgadas","resolucion":"1920x1200 WUXGA","panel":"IPS","conectores":"DP, mDP, HDMI, USB Hub","grado":"B"}',
 99.00, 400.00, 12, 'B', 'dell-u2415.jpg'),

(3, 'HP Z24n G2 24" WUXGA', 'hp-z24n-g2',
 'Monitor de la serie Z, concebido para estaciones de trabajo creativas.',
 '{"tamaño":"24 pulgadas","resolucion":"1920x1200 WUXGA","panel":"IPS","conectores":"DP, HDMI, USB-C","grado":"A"}',
 149.00, 480.00, 7, 'A', 'hp-z24n-g2.jpg'),

-- SERVIDORES (cat 4)
(4, 'HP ProLiant DL20 Gen10', 'hp-proliant-dl20-gen10',
 'Servidor rack 1U de entrada perfecta para PYME. Silencioso y compacto. Ideal como servidor de ficheros o virtualización ligera.',
 '{"cpu":"Intel Xeon E-2224","ram":"16 GB ECC","almacenamiento":"2x HDD 1TB SATA","factor":"1U Rack","os":"Sin OS","grado":"A"}',
 649.00, 2200.00, 3, 'A', 'hp-proliant-dl20.jpg'),

(4, 'Dell PowerEdge T340 Torre', 'dell-poweredge-t340',
 'Servidor torre silencioso, cabe en cualquier oficina. Expandible hasta 64 GB RAM y múltiples discos.',
 '{"cpu":"Intel Xeon E-2224","ram":"32 GB ECC","almacenamiento":"4x SSD 480 GB","factor":"Torre","os":"Sin OS","grado":"A"}',
 849.00, 2800.00, 2, 'A', 'dell-poweredge-t340.jpg'),

(4, 'Lenovo ThinkSystem ST50 V2', 'lenovo-thinksystem-st50-v2',
 'Servidor torre entry-level con gestión remota. Perfecto para primer servidor empresarial.',
 '{"cpu":"Intel Xeon E-2356G","ram":"16 GB ECC","almacenamiento":"2x SSD 960 GB","factor":"Torre","os":"Sin OS","grado":"B"}',
 749.00, 2500.00, 2, 'B', 'lenovo-st50-v2.jpg'),

-- ACCESORIOS (cat 5)
(5, 'Lenovo ThinkPad USB-C Dock Gen2', 'lenovo-usbc-dock-gen2',
 'Docking station universal USB-C. Convierte tu portátil en estación de trabajo completa con un solo cable.',
 '{"conectores":"2x USB-C, 4x USB-A, 2x DP, HDMI, Ethernet, Audio","potencia":"90W PD","grado":"A"}',
 89.00, 250.00, 15, 'A', 'lenovo-dock-gen2.jpg'),

(5, 'HP USB-C G5 Essential Dock', 'hp-usbc-g5-dock',
 'Dock HP con carga rápida. Compatible con todos los portátiles USB-C del mercado.',
 '{"conectores":"USB-C, 4x USB-A, DP, HDMI, Ethernet, SD","potencia":"65W PD","grado":"A"}',
 69.00, 180.00, 20, 'A', 'hp-g5-dock.jpg'),

(5, 'Teclado Lenovo ThinkPad TrackPoint II', 'lenovo-thinkpad-trackpoint-ii',
 'El teclado inalámbrico con TrackPoint para control sin ratón. Favorito de los power users.',
 '{"conexion":"Bluetooth + USB Dongle","trackpoint":"Sí","bateria":"AA x2","grado":"A"}',
 59.00, 130.00, 10, 'A', 'lenovo-trackpoint-keyboard.jpg'),

(5, 'Monitor Dell 27" + Dock Bundle', 'dell-monitor-dock-bundle',
 'Pack productividad: monitor 27" QHD + docking station. Todo lo que necesitas en un pedido.',
 '{"incluye":"Monitor Dell 27 QHD + Dock USB-C 90W","conectores":"USB-C, DP, HDMI, Ethernet","grado":"A"}',
 299.00, 780.00, 5, 'A', 'dell-bundle-27.jpg'),

(5, 'Ratón HP 430 Multi-Device', 'hp-430-multi-device',
 'Ratón inalámbrico multi-dispositivo. Conecta hasta 3 equipos y alterna con un botón.',
 '{"conexion":"Bluetooth + USB Nano","dispositivos":"3","dpi":"800-4000","grado":"A"}',
 29.00, 65.00, 30, 'A', 'hp-430-mouse.jpg'),

(5, 'Kit Teclado + Ratón Dell KM117', 'dell-km117-kit',
 'Set inalámbrico compacto y silencioso. Receptor único para ambos dispositivos.',
 '{"conexion":"USB Nano 2.4GHz","bateria":"AA incluidas","idioma":"ES","grado":"B"}',
 25.00, 60.00, 22, 'B', 'dell-km117.jpg'),

(5, 'HP HS04 Auriculares con Micrófono', 'hp-hs04-auriculares',
 'Auriculares con cable certificados para videollamadas. Compatibles con Teams y Zoom.',
 '{"conexion":"Jack 3.5mm + USB Adaptador","microfono":"Sí, cancelación ruido","certificacion":"MS Teams","grado":"A"}',
 19.00, 45.00, 25, 'A', 'hp-hs04.jpg'),

(5, 'Webcam Logitech C920 Reacondicionada', 'logitech-c920-reacondicionada',
 'La webcam de referencia para profesionales, revisada y certificada. Full HD 1080p con autoenfoque.',
 '{"resolucion":"1080p 30fps","microfono":"Estéreo integrado","conexion":"USB-A","grado":"B"}',
 45.00, 100.00, 8, 'B', 'logitech-c920.jpg');

-- -------------------------------------------------------
-- FAVORITOS
-- -------------------------------------------------------
CREATE TABLE favoritos (
  usuario_id  INT UNSIGNED NOT NULL,
  producto_id INT UNSIGNED NOT NULL,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (usuario_id, producto_id),
  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- PRESUPUESTOS (simulación de pedido)
-- -------------------------------------------------------
CREATE TABLE presupuestos (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT UNSIGNED        NOT NULL,
  estado      ENUM('pendiente','revisando','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
  notas       TEXT,
  total       DECIMAL(10,2)       NOT NULL DEFAULT 0.00,
  created_at  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  INDEX idx_estado (estado)
) ENGINE=InnoDB;

CREATE TABLE presupuesto_items (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  presupuesto_id INT UNSIGNED     NOT NULL,
  producto_id    INT UNSIGNED     NOT NULL,
  cantidad       SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  precio_unitario DECIMAL(8,2)   NOT NULL,
  FOREIGN KEY (presupuesto_id) REFERENCES presupuestos(id) ON DELETE CASCADE,
  FOREIGN KEY (producto_id)    REFERENCES productos(id)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- CARRITO DE SESIÓN (temporal, se convierte en presupuesto)
-- -------------------------------------------------------
CREATE TABLE carrito (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT UNSIGNED        NOT NULL,
  producto_id INT UNSIGNED        NOT NULL,
  cantidad    SMALLINT UNSIGNED   NOT NULL DEFAULT 1,
  UNIQUE KEY uq_usuario_producto (usuario_id, producto_id),
  FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)  ON DELETE CASCADE,
  FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- LOG DE AUDITORÍA (para pasar la auditoría de seguridad)
-- -------------------------------------------------------
CREATE TABLE audit_log (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT UNSIGNED,
  accion      VARCHAR(100)  NOT NULL,
  tabla       VARCHAR(50),
  registro_id INT UNSIGNED,
  ip          VARCHAR(45),
  user_agent  VARCHAR(255),
  created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_usuario  (usuario_id),
  INDEX idx_accion   (accion),
  INDEX idx_created  (created_at)
) ENGINE=InnoDB;
