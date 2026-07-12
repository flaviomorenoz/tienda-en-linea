<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Devuelve la conversación asociada al token, o la crea si no existe.
     */
    public function obtener_o_crear($token, $nombre_cliente = null, $celular_cliente = null) {
        $conversacion = $this->obtener_por_token($token);

        if ($conversacion) {
            return $conversacion;
        }

        $this->db->insert('chat_conversaciones', array(
            'token'           => $token,
            'nombre_cliente'  => $nombre_cliente,
            'celular_cliente' => $celular_cliente,
        ));

        return $this->obtener_por_token($token);
    }

    public function obtener_por_token($token) {
        $this->db->where('token', $token);
        return $this->db->get('chat_conversaciones')->row();
    }

    public function guardar_mensaje($id_conversacion, $emisor, $mensaje, $id_vendedor = null) {
        $this->db->insert('chat_mensajes', array(
            'id_conversacion' => $id_conversacion,
            'emisor'          => $emisor,
            'id_vendedor'     => $id_vendedor,
            'mensaje'         => $mensaje,
        ));

        $this->db->where('id', $id_conversacion);
        $this->db->update('chat_conversaciones', array('ultima_actividad' => date('Y-m-d H:i:s')));

        return $this->db->insert_id();
    }

    /**
     * Pasa la conversación a la cola de espera para que una vendedora
     * disponible la tome. No asigna a nadie en particular.
     */
    public function solicitar_vendedora($id_conversacion, $motivo = null) {
        $this->db->where('id', $id_conversacion);
        $this->db->where('estado', 'ia');
        $this->db->update('chat_conversaciones', array(
            'estado'               => 'en_espera',
            'motivo_transferencia' => $motivo,
        ));

        return $this->db->affected_rows() > 0;
    }

    /**
     * Mensajes nuevos (id mayor a $desde_id) más el nombre de la
     * vendedora asignada, si la hay. Usado para el polling del cliente.
     */
    public function mensajes_nuevos($id_conversacion, $desde_id = 0) {
        $this->db->select('m.id, m.emisor, m.mensaje, m.creado_en');
        $this->db->from('chat_mensajes m');
        $this->db->where('m.id_conversacion', $id_conversacion);
        $this->db->where('m.id >', (int)$desde_id);
        $this->db->order_by('m.id', 'ASC');
        return $this->db->get()->result();
    }

    public function info_estado($id_conversacion) {
        $this->db->select('cc.estado, cv.nombre_visible AS vendedor_nombre');
        $this->db->from('chat_conversaciones cc');
        $this->db->join('chat_vendedores cv', 'cv.id = cc.id_vendedor', 'left');
        $this->db->where('cc.id', $id_conversacion);
        return $this->db->get()->row();
    }
}
