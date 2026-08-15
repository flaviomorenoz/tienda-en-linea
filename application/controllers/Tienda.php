<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tienda extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Producto_model');
    }

    public function index() {
        traza("Tienda.index: base_url='" . base_url() . "' FCPATH='" . FCPATH . "'");
        $categoria  = $this->input->get('categoria');
        $categorias = $this->Producto_model->get_categorias();
        $productos  = $this->Producto_model->get_todos($categoria ?: NULL);
        traza("Tienda.index: cantidad productos=" . count($productos));
        $this->_adjuntar_imagenes($productos);

        $data = array(
            'titulo'           => $this->config->item('tienda_nombre'),
            'productos'        => $productos,
            'categorias'       => $categorias,
            'categoria_activa' => $categoria,
            'carrito_count'    => $this->_carrito_count(),
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('tienda/home', $data);
        $this->load->view('layouts/footer');
    }

    public function categoria($cat) {
        traza("Tienda.categoria: base_url='" . base_url() . "' FCPATH='" . FCPATH . "' cat='$cat'");
        $cat        = rawurldecode($cat);
        $categorias = $this->Producto_model->get_categorias();
        $productos  = $this->Producto_model->get_todos($cat);
        $this->_adjuntar_imagenes($productos);

        $data = array(
            'titulo'           => $cat . ' - ' . $this->config->item('tienda_nombre'),
            'productos'        => $productos,
            'categorias'       => $categorias,
            'categoria_activa' => $cat,
            'carrito_count'    => $this->_carrito_count(),
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('tienda/home', $data);
        $this->load->view('layouts/footer');
    }

    public function detalle($id) {
        $id = (int)$id;
        $producto = $this->Producto_model->get_por_id($id);

        if (!$producto) {
            show_404();
            return;
        }

        $tallas      = $this->Producto_model->get_tallas($id);
        $relacionados = $this->Producto_model->get_relacionados($id, $producto->categoria);
        $imagenes    = array_values(array_filter(array(
            $producto->imagen_url,
            isset($producto->imagen2) ? $producto->imagen2 : '',
            isset($producto->imagen3) ? $producto->imagen3 : '',
        ), function($img) { return !empty($img); }));
        if (empty($imagenes)) {
            $imagenes = array($producto->imagen_url);
        }

        $data = array(
            'titulo'        => $producto->nombre . ' - ' . $this->config->item('tienda_nombre'),
            'producto'      => $producto,
            'imagenes'      => $imagenes,
            'tallas'        => $tallas,
            'relacionados'  => $relacionados,
            'carrito_count' => $this->_carrito_count(),
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('tienda/detalle', $data);
        $this->load->view('layouts/footer');
    }

    private function _adjuntar_imagenes(&$productos) {
        if (empty($productos)) return;
        foreach ($productos as $p) {
            $imgs = array_values(array_filter(array(
                isset($p->imagen_url) ? $p->imagen_url : '',
                isset($p->imagen2) ? $p->imagen2 : '',
                isset($p->imagen3) ? $p->imagen3 : '',
            ), function($img) { return !empty($img); }));
            $p->imagenes = !empty($imgs) ? $imgs : array($p->imagen_url);
            traza("_adjuntar_imagenes id={$p->id} imagenes=" . implode(' | ', $p->imagenes));
        }
    }

    private function _carrito_count() {
        $carrito = $this->session->userdata('carrito');
        if (!is_array($carrito)) return 0;
        return array_sum(array_column($carrito, 'cantidad'));
    }
}
