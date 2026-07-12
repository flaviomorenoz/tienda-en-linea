-- =============================================================
-- Script SQL - Chat con IA + Transferencia a Vendedora
-- Base de datos: tienda_db (PostgreSQL)
-- Prefijo: chat_
-- Ejecutar: psql -U <usuario> -d tienda_db -f chat_tablas.sql
-- =============================================================

-- =============================================================
-- TABLAS
-- =============================================================

-- Vendedoras habilitadas para atender chats en vivo.
-- Se apoya en la tabla "admins" ya existente para el login
-- (usuario/password_hash); aqui solo se guarda su perfil de chat.
CREATE TABLE IF NOT EXISTS chat_vendedores (
    id              SERIAL PRIMARY KEY,
    id_admin        INTEGER UNIQUE REFERENCES admins(id) ON DELETE CASCADE,
    nombre_visible  VARCHAR(150) NOT NULL,
    estado          VARCHAR(20) NOT NULL DEFAULT 'desconectada'
                        CHECK (estado IN ('disponible', 'ocupada', 'desconectada')),
    ultima_conexion TIMESTAMP,
    creado_en       TIMESTAMP DEFAULT NOW()
);

-- Un hilo de conversacion por cliente/sesion. Nace en 'ia' y puede
-- pasar a 'en_espera' (cliente pidio hablar con una vendedora),
-- 'vendedor' (una vendedora ya lo tomo) y finalmente 'cerrada'.
CREATE TABLE IF NOT EXISTS chat_conversaciones (
    id                    SERIAL PRIMARY KEY,
    token                 VARCHAR(64) UNIQUE NOT NULL,
    nombre_cliente        VARCHAR(200),
    celular_cliente       VARCHAR(20),
    estado                VARCHAR(20) NOT NULL DEFAULT 'ia'
                              CHECK (estado IN ('ia', 'en_espera', 'vendedor', 'cerrada')),
    id_vendedor           INTEGER REFERENCES chat_vendedores(id),
    motivo_transferencia  TEXT,
    iniciado_en           TIMESTAMP DEFAULT NOW(),
    transferido_en        TIMESTAMP,
    cerrado_en            TIMESTAMP,
    ultima_actividad      TIMESTAMP DEFAULT NOW()
);

-- Cada mensaje de la conversacion (del cliente, de la IA, de la
-- vendedora o del sistema), en un solo hilo cronologico.
CREATE TABLE IF NOT EXISTS chat_mensajes (
    id                   SERIAL PRIMARY KEY,
    id_conversacion      INTEGER NOT NULL REFERENCES chat_conversaciones(id) ON DELETE CASCADE,
    emisor               VARCHAR(20) NOT NULL
                             CHECK (emisor IN ('cliente', 'ia', 'vendedor', 'sistema')),
    id_vendedor          INTEGER REFERENCES chat_vendedores(id),
    mensaje              TEXT NOT NULL,
    leido_por_vendedor   BOOLEAN DEFAULT FALSE,
    leido_por_cliente    BOOLEAN DEFAULT FALSE,
    creado_en            TIMESTAMP DEFAULT NOW()
);

-- Auditoria de cada traspaso (a que vendedora, cuando y por que).
-- Permite reasignaciones (ej: una vendedora deriva a otra) sin
-- perder el historial de quien atendio que tramo.
CREATE TABLE IF NOT EXISTS chat_transferencias (
    id                     SERIAL PRIMARY KEY,
    id_conversacion        INTEGER NOT NULL REFERENCES chat_conversaciones(id) ON DELETE CASCADE,
    id_vendedor_anterior   INTEGER REFERENCES chat_vendedores(id),
    id_vendedor_nuevo      INTEGER REFERENCES chat_vendedores(id),
    motivo                 TEXT,
    creado_en              TIMESTAMP DEFAULT NOW()
);

-- =============================================================
-- INDICES
-- =============================================================
CREATE INDEX IF NOT EXISTS idx_chat_conversaciones_estado    ON chat_conversaciones(estado);
CREATE INDEX IF NOT EXISTS idx_chat_conversaciones_vendedor  ON chat_conversaciones(id_vendedor);
CREATE INDEX IF NOT EXISTS idx_chat_mensajes_conversacion    ON chat_mensajes(id_conversacion, creado_en);
CREATE INDEX IF NOT EXISTS idx_chat_transferencias_conv      ON chat_transferencias(id_conversacion);
