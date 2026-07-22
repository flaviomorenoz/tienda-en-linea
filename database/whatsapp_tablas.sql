-- =============================================================
-- Script SQL - Módulo independiente: Asistente IA por WhatsApp
-- Base de datos: tienda_db / erp_tienda (PostgreSQL)
-- Prefijo: whatsapp_
-- Ejecutar: psql -U <usuario> -d <basededatos> -f whatsapp_tablas.sql
-- =============================================================

-- Historial de mensajes por número de teléfono. Sirve tanto para
-- guardar la conversación como para reconstruir el contexto que se
-- le manda a DeepSeek en cada respuesta (WhatsApp no reenvía el
-- historial como sí lo hace el widget web).
CREATE TABLE IF NOT EXISTS whatsapp_mensajes (
    id            SERIAL PRIMARY KEY,
    telefono      VARCHAR(20) NOT NULL,
    rol           VARCHAR(20) NOT NULL CHECK (rol IN ('user', 'assistant')),
    mensaje       TEXT NOT NULL,
    wa_message_id VARCHAR(100) UNIQUE,
    creado_en     TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_whatsapp_mensajes_telefono ON whatsapp_mensajes(telefono, creado_en);
