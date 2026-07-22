<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vendedora_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_by_admin($id_admin) {
        $this->db->where('id_admin', $id_admin);
        return $this->db->get('chat_vendedores')->row();
    }

    public function set_estado($id_vendedora, $estado) {
        $this->db->where('id', $id_vendedora);
        $this->db->update('chat_vendedores', array(
            'estado'          => $estado,
            'ultima_conexion' => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * Conversaciones en cola, esperando que alguna vendedora las tome.
     */
    public function en_espera() {
        $this->db->where('estado', 'en_espera');
        $this->db->order_by('ultima_actividad', 'ASC');
        return $this->db->get('chat_conversaciones')->result();
    }

    public function conteo_en_espera() {
        $this->db->where('estado', 'en_espera');
        return (int)$this->db->count_all_results('chat_conversaciones');
    }

    /**
     * Historial de conversaciones cerradas de esta vendedora, por
     * defecto del último mes.
     */
    public function historial($id_vendedora, $desde = null) {
        if ($desde === null) {
            $desde = date('Y-m-d H:i:s', strtotime('-1 month'));
        }

        $this->db->select('cc.*, (SELECT COUNT(*) FROM chat_mensajes m WHERE m.id_conversacion = cc.id) AS total_mensajes');
        $this->db->from('chat_conversaciones cc');
        $this->db->where('cc.id_vendedor', $id_vendedora);
        //$this->db->where('cc.estado', 'cerrada');
        //$this->db->where('cc.cerrado_en >=', $desde);
        $this->db->order_by('cc.cerrado_en', 'DESC');
        
        //traza($this->db->get_compiled_select());
        $this->db->reset_query();

        $this->db->select('cc.*, (SELECT COUNT(*) FROM chat_mensajes m WHERE m.id_conversacion = cc.id) AS total_mensajes');
        $this->db->from('chat_conversaciones cc');
        $this->db->where('cc.id_vendedor', $id_vendedora);
        //$this->db->where('cc.estado', 'cerrada');
        //$this->db->where('cc.cerrado_en >=', $desde);
        $this->db->order_by('cc.cerrado_en', 'DESC');

        return $this->db->get()->result();
    }

    /**
     * Actualiza nombre/celular del cliente de una conversación propia
     * de esta vendedora (cerrada o no).
     */
    public function actualizar_cliente($id_conversacion, $id_vendedora, $nombre_cliente, $celular_cliente) {
        $this->db->where('id', $id_conversacion);
        $this->db->where('id_vendedor', $id_vendedora);
        $this->db->update('chat_conversaciones', array(
            'nombre_cliente'  => $nombre_cliente,
            'celular_cliente' => $celular_cliente,
        ));

        return $this->db->affected_rows() > 0;
    }

    /**
     * Conversaciones activas asignadas a esta vendedora.
     */
    public function asignadas($id_vendedora) {
        $this->db->where('estado', 'vendedor');
        $this->db->where('id_vendedor', $id_vendedora);
        $this->db->order_by('ultima_actividad', 'DESC');
        return $this->db->get('chat_conversaciones')->result();
    }

    /**
     * Intenta asignar la conversación a esta vendedora. Devuelve TRUE
     * solo si la conversación seguía en 'en_espera' (evita que dos
     * vendedoras tomen el mismo chat a la vez).
     */
    public function tomar($id_conversacion, $id_vendedora, $nombre_visible) {
        $this->db->where('id', $id_conversacion);
        $this->db->where('estado', 'en_espera');
        $this->db->update('chat_conversaciones', array(
            'estado'         => 'vendedor',
            'id_vendedor'    => $id_vendedora,
            'transferido_en' => date('Y-m-d H:i:s'),
        ));

        if ($this->db->affected_rows() === 0) {
            return false;
        }

        $this->db->insert('chat_transferencias', array(
            'id_conversacion'   => $id_conversacion,
            'id_vendedor_nuevo' => $id_vendedora,
            'motivo'            => 'Conversación tomada desde la cola de espera.',
        ));

        $this->guardar_mensaje($id_conversacion, 'sistema', $nombre_visible . ' se unió al chat.');

        return true;
    }

    public function cerrar($id_conversacion, $id_vendedora) {
        $this->db->where('id', $id_conversacion);
        $this->db->where('id_vendedor', $id_vendedora);
        $this->db->update('chat_conversaciones', array(
            'estado'     => 'cerrada',
            'cerrado_en' => date('Y-m-d H:i:s'),
        ));

        if ($this->db->affected_rows() === 0) {
            return false;
        }

        $this->guardar_mensaje($id_conversacion, 'sistema', 'La vendedora cerró la conversación.');

        return true;
    }

    public function responder($id_conversacion, $id_vendedora, $mensaje) {
        $this->db->where('id', $id_conversacion);
        $this->db->where('id_vendedor', $id_vendedora);
        $this->db->where('estado', 'vendedor');
        $conversacion = $this->db->get('chat_conversaciones')->row();

        if (!$conversacion) {
            return false;
        }

        $this->guardar_mensaje($id_conversacion, 'vendedor', $mensaje, $id_vendedora);

        return true;
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
    }

    /**
     * Historial completo de una conversación (incluye lo conversado
     * con la IA antes de la transferencia), solo si pertenece a esta
     * vendedora o sigue en la cola de espera.
     */
    public function mensajes_completos($id_conversacion, $id_vendedora) {
        $this->db->where('id', $id_conversacion);
        $this->db->group_start();
        $this->db->where('id_vendedor', $id_vendedora);
        $this->db->or_where('estado', 'en_espera');
        $this->db->group_end();
        $conversacion = $this->db->get('chat_conversaciones')->row();

        if (!$conversacion) {
            return null;
        }

        $this->db->where('id_conversacion', $id_conversacion);
        $this->db->order_by('id', 'ASC');
        $mensajes = $this->db->get('chat_mensajes')->result();

        return array('conversacion' => $conversacion, 'mensajes' => $mensajes);
    }
}
