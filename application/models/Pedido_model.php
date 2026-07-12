<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pedido_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function crear($datos) {
        $insert = array(
            'total'           => $datos['total'],
            'estado_pago'     => 'Pendiente',
            'estado_envio'    => 'EN_ORIGEN',
            'direccion_envio' => $datos['direccion_envio'],
            'celular'         => $datos['celular'],
            'dni'             => $datos['dni'],
            'nombres'         => $datos['nombres'],
            'observaciones'   => isset($datos['observaciones']) ? $datos['observaciones'] : '',
        );
        $this->db->insert('pedidos_web', $insert);
        return $this->db->insert_id();
    }

    public function agregar_detalle($datos) {
        $insert = array(
            'id_pedido'       => $datos['id_pedido'],
            'id_producto'     => $datos['id_producto'],
            'talla'           => $datos['talla'],
            'cantidad'        => $datos['cantidad'],
            'precio_unitario' => $datos['precio_unitario'],
        );
        $this->db->insert('detalle_pedido', $insert);
    }

    public function actualizar_pago($id, $estado_pago, $codigo_transaccion = '') {
        $this->db->where('id', (int)$id);
        $this->db->update('pedidos_web', array(
            'estado_pago'          => $estado_pago,
            'codigo_transaccion'   => $codigo_transaccion,
        ));
    }

    public function actualizar_codigos($id, $nro_orden, $codigo_transaccion) {
        $this->db->where('id', (int)$id);
        $this->db->update('pedidos_web', array(
            'nro_orden'           => $nro_orden,
            'codigo_transaccion'  => $codigo_transaccion,
        ));
        return $this->db->affected_rows() > 0;
    }

    public function actualizar_estado($id, $estado_envio) {
        $estados_validos = array('EN_ORIGEN', 'EN_TRANSITO', 'EN_DESTINO', 'ENTREGADO');
        if (!in_array($estado_envio, $estados_validos)) {
            return FALSE;
        }
        $this->db->where('id', (int)$id);
        $this->db->update('pedidos_web', array('estado_envio' => $estado_envio));
        return TRUE;
    }

    public function get_todos($filtro = NULL) {
        $this->db->select('p.*');
        $this->db->from('pedidos_web p');
        if ($filtro && $filtro !== 'todos') {
            $this->db->where('p.estado_pago', $filtro);
        }
        $this->db->order_by('p.fecha', 'DESC');
        return $this->db->get()->result();
    }

    public function get_por_id($id) {
        $this->db->where('id', (int)$id);
        return $this->db->get('pedidos_web')->row();
    }

    public function get_detalle($id_pedido) {
        $this->db->select('d.*, pr.name AS nombre, pr.imagen AS imagen_url, c.name AS categoria');
        $this->db->from('detalle_pedido d');
        $this->db->join('tec_products pr', 'pr.id = d.id_producto', 'left');
        $this->db->join('tec_categories c', 'c.id = pr.category_id', 'left');
        $this->db->where('d.id_pedido', (int)$id_pedido);
        return $this->db->get()->result();
    }

    public function get_detalle_pedido($id_pedido) {
        $sql = "SELECT a.id AS id_pedido, b.id_producto, c.name, c.talla, b.cantidad,
                       b.precio_unitario, b.id_unidad, d.descrip
                FROM pedidos_web a
                INNER JOIN detalle_pedido b ON a.id = b.id_pedido
                LEFT JOIN tec_products c ON b.id_producto = c.id
                LEFT JOIN tec_unidades d ON b.id_unidad = d.id
                WHERE a.id = ?
                ORDER BY b.id_producto";
        return $this->db->query($sql, [(int)$id_pedido])->result();
    }
}
