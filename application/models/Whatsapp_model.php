<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function mensaje_existe($wa_message_id) {
        if (empty($wa_message_id)) {
            return false;
        }

        $this->db->where('wa_message_id', $wa_message_id);
        return $this->db->count_all_results('whatsapp_mensajes') > 0;
    }

    public function guardar_mensaje($telefono, $rol, $mensaje, $wa_message_id = null) {
        $this->db->insert('whatsapp_mensajes', array(
            'telefono'      => $telefono,
            'rol'           => $rol,
            'mensaje'       => $mensaje,
            'wa_message_id' => $wa_message_id,
        ));

        return $this->db->insert_id();
    }

    /**
     * Últimos N mensajes de este número, en orden cronológico, para
     * armar el contexto que se le manda a DeepSeek.
     */
    public function historial_reciente($telefono, $limite = 12) {
        $this->db->where('telefono', $telefono);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limite);
        $filas = $this->db->get('whatsapp_mensajes')->result();

        return array_reverse($filas);
    }

    /**
     * Un número por fila con su último mensaje y totales, para el
     * panel de administración. Por defecto, del último mes.
     */
    public function conversaciones_recientes($desde = null) {
        if ($desde === null) {
            $desde = date('Y-m-d H:i:s', strtotime('-1 month'));
        }

        $this->db->select('telefono, COUNT(*) AS total_mensajes, MAX(creado_en) AS ultimo_mensaje, MIN(creado_en) AS primer_mensaje');
        $this->db->from('whatsapp_mensajes');
        $this->db->where('creado_en >=', $desde);
        $this->db->group_by('telefono');
        $this->db->order_by('ultimo_mensaje', 'DESC');
        return $this->db->get()->result();
    }

    public function mensajes_por_telefono($telefono) {
        $this->db->where('telefono', $telefono);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('whatsapp_mensajes')->result();
    }
}
