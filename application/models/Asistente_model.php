<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Asistente_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Arma el catálogo vigente en formato CSV compacto para usarlo
     * como contexto del prompt de la IA.
     */
    public function get_catalogo_contexto() {
        $this->db->select('p.id, p.name AS nombre, c.name AS categoria, p.unidad, p.price AS precio, p.marca, p.modelo, p.color, p.sexo');
        $this->db->from('tec_products p');
        $this->db->join('tec_categories c', 'c.id = p.category_id', 'left');
        $this->db->where('p.activo', '1');
        $this->db->order_by('p.id', 'ASC');
        $productos = $this->db->get()->result();

        if (empty($productos)) {
            return '';
        }

        $ids = array_map(function($p) { return $p->id; }, $productos);

        $this->db->select('id_producto, talla, stock');
        $this->db->from('productos_tallas');
        $this->db->where_in('id_producto', $ids);
        $this->db->where('stock >', 0);
        $tallas_rows = $this->db->get()->result();

        $tallas_por_producto = array();
        foreach ($tallas_rows as $t) {
            $tallas_por_producto[(int)$t->id_producto][] = trim($t->talla);
        }

        $lineas   = array();
        $lineas[] = 'id;nombre;categoria;unidad;precio;marca;modelo;color;tallas_disponibles;sexo';

        foreach ($productos as $p) {
            $tallas = isset($tallas_por_producto[(int)$p->id])
                ? implode(',', $tallas_por_producto[(int)$p->id])
                : '';

            $lineas[] = implode(';', array(
                $p->id,
                trim($p->nombre),
                trim($p->categoria),
                trim($p->unidad),
                $p->precio,
                trim($p->marca),
                trim($p->modelo),
                trim($p->color),
                $tallas,
                trim($p->sexo),
            ));
        }

        return implode("\n", $lineas);
    }
}
