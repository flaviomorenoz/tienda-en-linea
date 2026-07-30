<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tia_conversa_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Una fila por conversación, con el total de mensajes que tiene.
     */
    public function get_conversaciones() {
        $this->db->select('c.id, c.contacto, c.estado, c.fecha_inicio, c.fecha_fin, COUNT(m.id) AS total_mensajes');
        $this->db->from('tia_conversaciones c');
        $this->db->join('tia_mensajes m', 'm.id_conversacion = c.id', 'left');
        $this->db->group_by('c.id, c.contacto, c.estado, c.fecha_inicio, c.fecha_fin');
        $this->db->order_by('c.fecha_inicio', 'DESC');
        return $this->db->get()->result();
    }

    public function get_mensajes($id_conversacion) {
        $this->db->where('id_conversacion', $id_conversacion);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('tia_mensajes')->result();
    }

    public function cerrar_conversacion($id_conversacion) {
        $this->db->where('id', $id_conversacion);
        $this->db->where('estado', 'abierta');
        $this->db->update('tia_conversaciones', array(
            'estado'    => 'cerrada',
            'fecha_fin' => date('Y-m-d H:i:s'),
        ));

        return $this->db->affected_rows() > 0;
    }
}
