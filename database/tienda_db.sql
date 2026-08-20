-- =============================================================
-- Script SQL - Tienda Virtual Online
-- Base de datos: tienda_db (PostgreSQL)
-- =============================================================

-- Crear base de datos (ejecutar como superusuario si no existe)
-- CREATE DATABASE tienda_db ENCODING 'UTF8';

-- =============================================================
-- TABLAS
-- =============================================================

CREATE TABLE IF NOT EXISTS productos (
    id          SERIAL PRIMARY KEY,
    nombre      VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio      DECIMAL(10,2),
    tiene_precio BOOLEAN DEFAULT TRUE,
    categoria   VARCHAR(100),
    imagen_url  VARCHAR(300) DEFAULT 'assets/img/productos/default.jpg',
    activo      BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS productos_tallas (
    id          SERIAL PRIMARY KEY,
    id_producto INTEGER NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
    talla       VARCHAR(10) NOT NULL,
    stock       INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS pedidos_web (
    id              SERIAL PRIMARY KEY,
    fecha           TIMESTAMP DEFAULT NOW(),
    total           DECIMAL(10,2) NOT NULL DEFAULT 0,
    estado_pago     VARCHAR(20) DEFAULT 'Pendiente',
    estado_envio    VARCHAR(20) DEFAULT 'Pendiente',
    direccion_envio TEXT,
    celular         VARCHAR(20),
    dni             VARCHAR(15),
    nombres         VARCHAR(200),
    observaciones   TEXT,
    codigo_transaccion VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS detalle_pedido (
    id              SERIAL PRIMARY KEY,
    id_pedido       INTEGER NOT NULL REFERENCES pedidos_web(id) ON DELETE CASCADE,
    id_producto     INTEGER NOT NULL REFERENCES productos(id),
    talla           VARCHAR(10),
    cantidad        INTEGER NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS config_pasarela (
    id              SERIAL PRIMARY KEY,
    merchant_id     VARCHAR(100),
    clave_publica   VARCHAR(300),
    clave_privada   VARCHAR(300),
    entorno         VARCHAR(20) DEFAULT 'sandbox'
);

CREATE TABLE IF NOT EXISTS admins (
    id              SERIAL PRIMARY KEY,
    usuario         VARCHAR(100) UNIQUE NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    nombre          VARCHAR(200),
    created_at      TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS config_ajustes (
    id          SERIAL PRIMARY KEY,
    clave       VARCHAR(100) UNIQUE NOT NULL,
    valor       VARCHAR(255),
    updated_at  TIMESTAMP DEFAULT NOW()
);

-- =============================================================
-- INDICES
-- =============================================================
CREATE INDEX IF NOT EXISTS idx_productos_categoria ON productos(categoria);
CREATE INDEX IF NOT EXISTS idx_productos_activo ON productos(activo);
CREATE INDEX IF NOT EXISTS idx_productos_tallas_producto ON productos_tallas(id_producto);
CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON pedidos_web(estado_pago, estado_envio);
CREATE INDEX IF NOT EXISTS idx_detalle_pedido ON detalle_pedido(id_pedido);

-- =============================================================
-- DATOS DE PRUEBA: PRODUCTOS
-- =============================================================
INSERT INTO productos (nombre, descripcion, precio, tiene_precio, categoria, imagen_url) VALUES
('Polo Básico Blanco', 'Polo de algodón 100%, corte regular clásico. Muy cómodo para el día a día. Lavado a máquina.', 35.00, TRUE, 'Polos', 'assets/img/productos/polo-blanco.jpg'),
('Polo Básico Negro', 'Polo de algodón premium en color negro. Ideal para combinar con cualquier outfit. Corte slim.', 35.00, TRUE, 'Polos', 'assets/img/productos/polo-negro.jpg'),
('Polo Estampado Surf', 'Diseño exclusivo con estampado surf urbano. Tela suave y fresca, perfecta para el verano.', 45.00, TRUE, 'Polos', 'assets/img/productos/polo-surf.jpg'),
('Camiseta Oversize', 'Talla grande de corte oversize, estilo urbano streetwear. 100% algodón. Muy de moda.', 55.00, TRUE, 'Polos', 'assets/img/productos/polo-oversize.jpg'),
('Polo Manga Larga Rayas', 'Manga larga con rayas horizontales clásicas, estilo marinero atemporal. Algodón suave.', 48.00, TRUE, 'Polos', 'assets/img/productos/polo-rayas.jpg'),
('Pantalón Jeans Slim', 'Jeans slim fit en denim stretch premium. Confort total todo el día. 5 bolsillos.', 89.00, TRUE, 'Pantalones', 'assets/img/productos/jeans-slim.jpg'),
('Pantalón Jogger Gris', 'Pantalón deportivo jogger en algodón fleece suave. Ideal para gym y uso casual.', 65.00, TRUE, 'Pantalones', 'assets/img/productos/jogger-gris.jpg'),
('Pantalón Cargo Beige', 'Cargo con múltiples bolsillos en tela resistente. Estilo militar urban chic.', 75.00, TRUE, 'Pantalones', 'assets/img/productos/cargo-beige.jpg'),
('Casaca Bomber Negra', 'Casaca bomber clásica en color negro con forro interior suave. Estilo atemporal.', 149.00, TRUE, 'Casacas', 'assets/img/productos/bomber-negra.jpg'),
('Casaca Jean Azul', 'Casaca de jean desgastada, estilo retro vintage. Ideal para looks casuales y frescos.', 120.00, TRUE, 'Casacas', 'assets/img/productos/casaca-jean.jpg'),
('Casaca Cortaviento', 'Impermeable ligera cortaviento, perfecta para días nublados o actividades al aire libre. Consultar colores.', NULL, FALSE, 'Casacas', 'assets/img/productos/cortaviento.jpg'),
('Hoodie Universitario', 'Sudadera con capucha y bolsillo canguro. Algodón grueso de alta calidad. Varios colores disponibles.', NULL, FALSE, 'Casacas', 'assets/img/productos/hoodie.jpg'),
('Chaleco Acolchado', 'Chaleco sin mangas con relleno térmico acolchado. Ideal para días de frío. Consultar stock.', NULL, FALSE, 'Casacas', 'assets/img/productos/chaleco.jpg'),
('Short Deportivo Azul', 'Short para entrenamiento en tela de secado rápido. Muy ligero y transpirable.', 40.00, TRUE, 'Shorts', 'assets/img/productos/short-azul.jpg'),
('Short Playa Tropical', 'Diseño tropical con estampado de hojas. Ideal para verano, playa o piscina.', 45.00, TRUE, 'Shorts', 'assets/img/productos/short-playa.jpg');

-- =============================================================
-- DATOS DE PRUEBA: TALLAS POR PRODUCTO
-- =============================================================

-- Polos con tallas S/M/L/XL
INSERT INTO productos_tallas (id_producto, talla, stock) VALUES
(1,'S',10),(1,'M',15),(1,'L',8),(1,'XL',5),
(2,'S',8),(2,'M',12),(2,'L',10),(2,'XL',6),
(3,'S',5),(3,'M',8),(3,'L',6),(3,'XL',3),
(4,'S',4),(4,'M',6),(4,'L',5),(4,'XL',3),
(5,'S',5),(5,'M',7),(5,'L',4);

-- Pantalones con tallas numéricas
INSERT INTO productos_tallas (id_producto, talla, stock) VALUES
(6,'28',4),(6,'30',8),(6,'32',10),(6,'34',6),(6,'36',3),
(7,'S',6),(7,'M',10),(7,'L',8),(7,'XL',4),
(8,'S',5),(8,'M',8),(8,'L',7),(8,'XL',2);

-- Casacas con precio
INSERT INTO productos_tallas (id_producto, talla, stock) VALUES
(9,'S',4),(9,'M',6),(9,'L',5),(9,'XL',3),
(10,'S',3),(10,'M',5),(10,'L',4),(10,'XL',2);

-- Casacas sin precio (solo referencia de tallas disponibles)
INSERT INTO productos_tallas (id_producto, talla, stock) VALUES
(11,'S',3),(11,'M',5),(11,'L',4),(11,'XL',2),
(12,'S',4),(12,'M',6),(12,'L',3),(12,'XL',1),
(13,'S',2),(13,'M',4),(13,'L',3);

-- Shorts
INSERT INTO productos_tallas (id_producto, talla, stock) VALUES
(14,'S',8),(14,'M',10),(14,'L',6),(14,'XL',4),
(15,'S',6),(15,'M',8),(15,'L',5),(15,'XL',3);

-- =============================================================
-- CONFIGURACIÓN PASARELA (sandbox/fake)
-- =============================================================
INSERT INTO config_pasarela (merchant_id, clave_publica, clave_privada, entorno)
VALUES ('SANDBOX_MERCHANT_001', 'pk_sandbox_test_key_abc123', 'sk_sandbox_test_key_xyz789', 'sandbox');

-- =============================================================
-- ADMIN POR DEFECTO
-- usuario: admin
-- contraseña: admin123
-- Hash generado con password_hash('admin123', PASSWORD_BCRYPT)
-- =============================================================
INSERT INTO admins (usuario, password_hash, nombre)
VALUES ('admin', '$2y$10$1Jx1noOdal3npsG.hNWlveGHsDKVhCow6zdxp1g4PZY1Rdw58Lk2K', 'Administrador');
