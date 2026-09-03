<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Producto_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_todos($categoria = NULL, $termino = '') {
        $this->db->select('p.id, p.name AS nombre, p.descripcion, p.price AS precio, p.tiene_precio, p.imagen AS imagen_url, p.imagen2, p.imagen3, p.activo, p.id_seccion, c.name AS categoria, s.descrip_seccion AS seccion, COALESCE(SUM(pt.stock), 0) AS stock_total');
        $this->db->from('tec_products p');
        $this->db->join('tec_categories c', 'c.id = p.category_id', 'left');
        $this->db->join('web_secciones s', 's.id = p.id_seccion', 'left');
        $this->db->join('productos_tallas pt', 'pt.id_producto = p.id', 'left');
        $this->db->where('p.activo', '1');
        if ($categoria) {
            $this->db->where('c.name', $categoria);
        }
        if ($termino !== '') {
            $termino_l = strtolower($termino);
            $this->db->group_start();
            $this->db->like('LOWER(p.name)', $termino_l);
            $this->db->or_like('LOWER(c.name)', $termino_l);
            $this->db->or_like('LOWER(s.descrip_seccion)', $termino_l);
            $this->db->group_end();
        }
        $this->db->group_by('p.id, p.name, p.descripcion, p.price, p.tiene_precio, p.imagen, p.imagen2, p.imagen3, p.activo, p.id_seccion, c.id, c.name, s.id, s.descrip_seccion');
        $this->db->order_by('p.id', 'ASC');
        //echo $this->db->get_compiled_select(); // This will output the SQL query for debugging purposes
        return $this->db->get()->result();
    }

    public function get_por_id($id) {
        $this->db->select('p.id, p.name AS nombre, p.descripcion, p.price AS precio, p.tiene_precio, p.imagen AS imagen_url, p.imagen2, p.imagen3, p.x_unidad, p.x_docena, p.x_media_docena, p.activo, c.name AS categoria');
        $this->db->from('tec_products p');
        $this->db->join('tec_categories c', 'c.id = p.category_id', 'left');
        $this->db->where('p.id', (int)$id);
        $this->db->where('p.activo', '1');
        return $this->db->get()->row();
    }

    public function get_tallas($id_producto) {
        $this->db->select('talla, stock');
        $this->db->from('productos_tallas');
        $this->db->where('id_producto', (int)$id_producto);
        $this->db->where('stock >', 0);
        $this->db->order_by('talla', 'ASC');
        return $this->db->get()->result();
    }

    public function get_secciones() {
        $this->db->select('id, descrip_seccion, orden');
        $this->db->from('web_secciones');
        $this->db->order_by('orden', 'ASC');
        return $this->db->get()->result();
    }

    public function get_categorias() {
        $this->db->select('c.name AS categoria');
        $this->db->from('tec_categories c');
        $this->db->join('tec_products p', 'p.category_id = c.id', 'inner');
        $this->db->where('p.activo', '1');
        $this->db->group_by('c.id, c.name');
        $this->db->order_by('c.name', 'ASC');
        $rows = $this->db->get()->result();
        $categorias = array();
        foreach ($rows as $r) {
            $categorias[] = trim($r->categoria);
        }
        return $categorias;
    }

    public function get_relacionados($id_producto, $categoria, $limit = 4) {
        $this->db->select('p.id, p.name AS nombre, p.descripcion, p.price AS precio, p.tiene_precio, p.imagen AS imagen_url, p.activo, c.name AS categoria');
        $this->db->from('tec_products p');
        $this->db->join('tec_categories c', 'c.id = p.category_id', 'left');
        $this->db->where('p.activo', '1');
        $this->db->where('c.name', $categoria);
        $this->db->where('p.id !=', (int)$id_producto);
        $this->db->limit($limit);
        return $this->db->get()->result();
    }
}
