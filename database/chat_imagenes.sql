-- =============================================================
-- Script SQL - Soporte de imágenes en el chat
-- Base de datos: tienda_db / erp_tienda (PostgreSQL)
-- Ejecutar: psql -U <usuario> -d <basededatos> -f chat_imagenes.sql
-- =============================================================

-- Nombre del archivo de imagen (formato imgYYYY-mm-dd_His.ext),
-- guardado en application/uploads/. NULL si el mensaje no trae imagen.
ALTER TABLE chat_mensajes ADD COLUMN IF NOT EXISTS imagen VARCHAR(255);

-- Un mensaje ahora puede ser solo una imagen, sin texto.
ALTER TABLE chat_mensajes ALTER COLUMN mensaje DROP NOT NULL;
