-- =============================================================
-- LIBRO DE RECLAMACIONES (PERÚ) - PostgreSQL
-- Base de datos: erp_tienda
--
-- Marco legal:
--   • Ley N° 29571 – Código de Protección y Defensa del Consumidor (arts. 150, 151 y 152)
--   • D.S. N° 011-2011-PCM – Reglamento del Libro de Reclamaciones
--   • D.S. N° 004-2024-PCM – Disposiciones del Libro de Reclamaciones Digital
--
-- Ejecutar con:
--   SET PGPASSWORD=postgresql
--   psql -U postgres -h localhost -d erp_tienda -f database\libro_reclamaciones.sql
-- =============================================================

CREATE TABLE IF NOT EXISTS libro_reclamaciones (
    id                SERIAL PRIMARY KEY,
    codigo            VARCHAR(30) UNIQUE NOT NULL,
    tipo              VARCHAR(10) NOT NULL DEFAULT 'RECLAMO',  -- RECLAMO | QUEJA
    nombres           VARCHAR(250) NOT NULL,
    tipo_documento    VARCHAR(20) NOT NULL DEFAULT 'DNI',      -- DNI | CE | PASAPORTE | OTRO
    numero_documento  VARCHAR(20) NOT NULL,
    domicilio         VARCHAR(255),
    telefono          VARCHAR(30),
    email             VARCHAR(150) NOT NULL,
    departamento      VARCHAR(100),
    provincia         VARCHAR(100),
    distrito          VARCHAR(100),
    producto_servicio VARCHAR(255) NOT NULL,
    numero_pedido     VARCHAR(50),
    monto_reclamado   DECIMAL(10,2),
    detalle           TEXT NOT NULL,
    estado            VARCHAR(20) NOT NULL DEFAULT 'RECIBIDO', -- RECIBIDO | EN_PROCESO | RESPONDIDO | ARCHIVADO
    respuesta         TEXT,
    fecha_respuesta   TIMESTAMP,
    admin_id          INTEGER,
    ip                VARCHAR(45),
    user_agent        TEXT,
    created_at        TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_libro_reclamos_codigo ON libro_reclamaciones(codigo);
CREATE INDEX IF NOT EXISTS idx_libro_reclamos_estado ON libro_reclamaciones(estado);
CREATE INDEX IF NOT EXISTS idx_libro_reclamos_fecha  ON libro_reclamaciones(created_at);
