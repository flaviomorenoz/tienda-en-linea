<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wts_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function mensaje_existe($wa_message_id) {
        if (empty($wa_message_id)) {
            return false;
        }

        $this->db->where('wa_message_id', $wa_message_id);
        return $this->db->count_all_results('wts_mensajes') > 0;
    }

    public function guardar_mensaje($telefono, $rol, $mensaje, $wa_message_id = null) {
        $this->db->insert('wts_mensajes', array(
            'destino'      => $telefono,
            'rol'           => $rol,
            'mensaje'       => $mensaje,
            'wa_id' => $wa_message_id,
        ));

        return $this->db->insert_id();
    }

    /**
     * Últimos N mensajes de este número, en orden cronológico, para
     * armar el contexto que se le manda a DeepSeek.
     */
    public function historial_reciente($telefono, $limite = 12) {
        $this->db->where('destino', $telefono);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limite);
        $filas = $this->db->get('wts_mensajes')->result();

        return array_reverse($filas);
    }

    /**
     * Un número por fila con su último mensaje y totales, para el
     * panel de administración. Por defecto, del último mes.
     */
    public function conversaciones_recientes($desde = null) {

        $cSql = "select a.id, substring(a.fecha::text,1,10) fecha, a.message_sid, a.nombre, replace(a.telefono_origen,'whatsapp:','') origen, replace(a.telefono_destino,'whatsapp:','') destino, a.tipo, a.mensaje, a.rol
            from wts_mensajes a
            where a.fecha > '2026-07-15'
            order by a.id desc";
             
        // Query mejorado que muestra una linea por conversacion al mismo numero en el dia:
        $cSql = "select z.id, z.fecha, z.telefono_origen, wa.nombre, z.cant, 'x' opciones
            from (
                select min(a.id) id, to_char(a.fecha, 'YYYY-MM-DD') fecha, a.telefono_origen, count(1) cant
                from wts_mensajes a
                where a.tipo='RECIBIDO'
                group by to_char(a.fecha, 'YYYY-MM-DD'), a.telefono_origen
            ) z
            left join wts_mensajes wa on z.id = wa.id
            order by z.id desc";

        $result = $this->db->query($cSql)->result_array();
        return $result;
    }

    public function mensajes_por_telefono($telefono) {
        $this->db->where('destino', $telefono);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('wts_mensajes')->result();
    }

    /**
     * Devuelve toda la conversación asociada a un mensaje (por su id).
     * Se obtiene el teléfono de origen del mensaje indicado y se traen
     * todos los mensajes donde ese número participe como origen o destino.
     */
    public function conversacion_por_id($id) {
        $cSql = "select b.id, b.fecha, b.telefono_origen origen, b.telefono_destino destino, b.tipo, b.mensaje mensaje
            from (
                select s.telefono_origen
                from wts_mensajes s
                where s.id = ?
            ) z
            inner join wts_mensajes b on (z.telefono_origen = b.telefono_origen or z.telefono_origen = b.telefono_destino)
            order by b.fecha";

        return $this->db->query($cSql, array($id))->result();
    }
}
