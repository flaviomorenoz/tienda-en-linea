<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Carrito extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Producto_model');
    }

    public function ver() {
        $carrito = $this->session->userdata('carrito') ?: array();
        //print_r($carrito);
        //die("Fin");
        $total   = $this->_calcular_total($carrito);

        $data = array(
            'titulo'        => 'Mi Carrito - ' . $this->config->item('tienda_nombre'),
            'carrito'       => $carrito,
            'total'         => $total,
            'carrito_count' => array_sum(array_column($carrito, 'cantidad')),
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('tienda/carrito', $data);
        $this->load->view('layouts/footer');
    }

    public function agregar() {
        if ($this->input->method() !== 'post') {
            redirect('tienda');
            return;
        }

        $id_producto = (int)$this->input->post('id_producto', TRUE);
        $talla       = $this->input->post('talla', TRUE);
        $cantidad    = max(1, (int)$this->input->post('cantidad', TRUE));

        $producto = $this->Producto_model->get_por_id($id_producto);

        $unidad = $this->input->post('select_unidad',1);

        if (!$producto || !$producto->tiene_precio) {
            $this->session->set_flashdata('error', 'Este producto no puede agregarse al carrito.');
            redirect('tienda/producto/' . $id_producto);
            return;
        }

        $carrito = $this->session->userdata('carrito') ?: array();

        // Clave única por producto + talla
        $key = $id_producto . '_' . $talla;

        if (isset($carrito[$key])) {
            $carrito[$key]['cantidad'] += $cantidad;
        } else {
            $carrito[$key] = array(
                'id'        => $id_producto,
                'nombre'    => $producto->nombre,
                'precio'    => (float)$producto->precio,
                'talla'     => $talla,
                'cantidad'  => $cantidad,
                'imagen'    => $producto->imagen_url,
                'categoria' => $producto->categoria,
                'unidad'    => $unidad
            );
        }

        $this->session->set_userdata('carrito', $carrito);

        // Responder con JSON si es AJAX, o redirigir
        if ($this->input->is_ajax_request()) {
            $total_items = array_sum(array_column($carrito, 'cantidad'));
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success'     => TRUE,
                    'total_items' => $total_items,
                    'mensaje'     => 'Producto agregado al carrito',
                )));
        } else {
            $this->session->set_flashdata('success', 'Producto agregado al carrito.');
            redirect('carrito');
        }
    }

    public function quitar($key_encoded) {
        $key     = urldecode($key_encoded);
        $carrito = $this->session->userdata('carrito') ?: array();
        unset($carrito[$key]);
        $this->session->set_userdata('carrito', $carrito);
        $this->session->set_flashdata('success', 'Producto eliminado del carrito.');
        redirect('carrito');
    }

    public function actualizar() {
        traza("Carrito->actualizar");
        if ($this->input->method() !== 'post') {
            redirect('carrito');
            return;
        }

        $cantidades = $this->input->post('cantidad', TRUE);
        $carrito    = $this->session->userdata('carrito') ?: array();

        if (is_array($cantidades)) {
            foreach ($cantidades as $key => $cant) {
                $cant = (int)$cant;
                if (isset($carrito[$key])) {
                    if ($cant <= 0) {
                        unset($carrito[$key]);
                    } else {
                        $carrito[$key]['cantidad'] = $cant;
                    }
                }
            }
        }

        $this->session->set_userdata('carrito', $carrito);
        $this->session->set_flashdata('success', 'Carrito actualizado.');
        redirect('carrito');
    }

    public function vaciar() {
        $this->session->unset_userdata('carrito');
        $this->session->set_flashdata('success', 'Carrito vaciado.');
        redirect('tienda');
    }

    private function _calcular_total($carrito) {
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
        return $total;
    }
}
