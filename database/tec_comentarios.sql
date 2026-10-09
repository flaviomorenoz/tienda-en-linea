-- =============================================================
-- COMENTARIOS DE PRODUCTOS - PostgreSQL
-- Base de datos: erp_tienda
--
-- Los escribe el cliente desde el popup "Comenta" de las tarjetas
-- (mostrar_popup_comentario() en assets/js/comentarios.js), que guarda
-- por fetch en Comentarios::guardar.
--
-- Ejecutar con:
--   SET PGPASSWORD=postgresql
--   psql -U postgres -h localhost -d erp_tienda -f database\tec_comentarios.sql
-- =============================================================

CREATE TABLE IF NOT EXISTS tec_comentarios (
    id          SERIAL PRIMARY KEY,
    id_producto INTEGER NOT NULL REFERENCES tec_products(id) ON DELETE CASCADE,
    comentario  TEXT NOT NULL,
    fecha       TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_tec_comentarios_producto ON tec_comentarios(id_producto);
CREATE INDEX IF NOT EXISTS idx_tec_comentarios_fecha    ON tec_comentarios(fecha);
