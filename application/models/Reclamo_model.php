<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modelo del Libro de Reclamaciones (Ley N° 29571 y D.S. N° 011-2011-PCM).
 */
class Reclamo_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Registra una nueva hoja de reclamación / queja.
     *
     * @param array $datos
     * @return int ID insertado
     */
    public function crear($datos) {
        $this->db->insert('libro_reclamaciones', $datos);
        return $this->db->insert_id();
    }

    /**
     * Busca por código generado (ej: LR-20260831-0001).
     */
    public function get_por_codigo($codigo) {
        $this->db->where('codigo', strtoupper(trim($codigo)));
        return $this->db->get('libro_reclamaciones')->row();
    }

    /**
     * Busca por ID (admin).
     */
    public function get_por_id($id) {
        $this->db->where('id', (int)$id);
        return $this->db->get('libro_reclamaciones')->row();
    }

    /**
     * Listado para el panel admin, ordenado por fecha descendente.
     *
     * @param string|null $filtro Estado a filtrar ('todos' = sin filtro).
     */
    public function get_todos($filtro = NULL) {
        $this->db->select('r.*');
        $this->db->from('libro_reclamaciones r');
        if ($filtro && $filtro !== 'todos') {
            $this->db->where('r.estado', $filtro);
        }
        $this->db->order_by('r.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Registra la respuesta del proveedor (y actualiza el estado).
     */
    public function actualizar_respuesta($id, $estado, $respuesta) {
        $admin_id = $this->session->userdata('admin_id');
        $data = array(
            'estado'          => $estado,
            'respuesta'       => $respuesta,
            'fecha_respuesta' => date('Y-m-d H:i:s'),
            'admin_id'        => $admin_id ? (int)$admin_id : NULL,
        );
        $this->db->where('id', (int)$id);
        return $this->db->update('libro_reclamaciones', $data);
    }

    /**
     * Actualiza solo el estado (para reabrir / archivar).
     */
    public function actualizar_estado($id, $estado) {
        $this->db->where('id', (int)$id);
        return $this->db->update('libro_reclamaciones', array('estado' => $estado));
    }

    /**
     * Cantidad de hojas registradas en una fecha (para generar el correlativo diario).
     *
     * @param string $fecha Formato Y-m-d
     * @return int
     */
    public function contar_hoy($fecha) {
        $sql = "SELECT COUNT(*) AS total FROM libro_reclamaciones WHERE created_at::date = ?";
        $row = $this->db->query($sql, array($fecha))->row();
        return $row ? (int)$row->total : 0;
    }
}
