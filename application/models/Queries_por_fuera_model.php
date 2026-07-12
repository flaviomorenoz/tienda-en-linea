<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Queries_por_fuera_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    function get_pedidos_con_codigo() {
        $this->db->select('a.id, a.nro_orden, a.codigo_transaccion');
        $this->db->from('pedidos_web as a');
        $this->db->where('a.nro_orden IS NOT NULL');
        $this->db->where("a.estado_envio != 'ENTREGADO'");
        
        //die($this->db->get_compiled_select());
        $query = $this->db->get();
        return $query->result();
    }

    function actualizar_estado($pedido_id, $nuevo_estado) {
        $this->db->where('id', $pedido_id);
        $this->db->update('pedidos_web', ['estado_envio' => $nuevo_estado]);
    }

}