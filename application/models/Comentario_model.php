<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Comentarios de productos (tabla tec_comentarios).
 *
 * Los escribe el cliente desde el popup "Comenta" de las tarjetas del catalogo
 * (mostrar_popup_comentario() en assets/js/comentarios.js), que guarda por
 * fetch en Comentarios::guardar.
 *
 * DDL: database/tec_comentarios.sql
 */
class Comentario_model extends CI_Model {

    /** Largo maximo del comentario (mismo valor que el maxlength del popup). */
    const MAX_LARGO = 1000;

    public function __construct() {
        parent::__construct();
    }

    /**
     * Registra un comentario de un producto.
     *
     * @param int    $id_producto
     * @param string $comentario Texto ya validado (sin espacios sobrantes).
     * @return int ID insertado (0 si fallo)
     */
    public function crear($id_producto, $comentario) {
        $ok = $this->db->insert('tec_comentarios', array(
            'id_producto' => (int) $id_producto,
            'comentario'  => $comentario,
            'fecha'       => date('Y-m-d H:i:s'),
        ));

        return $ok ? (int) $this->db->insert_id() : 0;
    }

    /**
     * Comentarios de un producto, los mas recientes primero.
     *
     * @param int $id_producto
     * @param int $limit       0 = todos
     */
    public function get_por_producto($id_producto, $limit = 20) {
        $this->db->select('c.id, c.id_producto, c.comentario, c.fecha');
        $this->db->from('tec_comentarios c');
        $this->db->where('c.id_producto', (int) $id_producto);
        $this->db->order_by('c.fecha', 'DESC');
        $this->db->order_by('c.id', 'DESC');

        if ($limit > 0) {
            $this->db->limit((int) $limit);
        }

        return $this->db->get()->result();
    }

    /**
     * Cantidad de comentarios de un producto (contador de la ficha).
     */
    public function contar_por_producto($id_producto) {
        $this->db->where('id_producto', (int) $id_producto);

        return (int) $this->db->count_all_results('tec_comentarios');
    }
}
