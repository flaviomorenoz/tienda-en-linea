<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pago extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('Pedido_model', 'Pasarela_model', 'Producto_model'));
        $this->load->library('form_validation');
    }

    public function checkout(){
        $carrito = $this->session->userdata('carrito') ?: array();

        if (empty($carrito)) {
            $this->session->set_flashdata('error', 'Tu carrito está vacío.');
            redirect('tienda');
            return;
        }

        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        $data = array(
            'titulo'        => 'Finalizar compra - ' . $this->config->item('tienda_nombre'),
            'carrito'       => $carrito,
            'total'         => $total,
            'carrito_count' => array_sum(array_column($carrito, 'cantidad')),
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('tienda/checkout', $data);
        $this->load->view('layouts/footer');
    }

    public function procesar() { // tipo_pago = 1 (Con tarjeta), 2 (Posiblemente con Yape), 3 (Pago sin confirmar)
        $tipo_pago = trim($_POST["tipo_pago"]);
        traza("Pago->procesar");
        if ($this->input->method() !== 'post') {
            //redirect('checkout');
            //return;
        }

        $carrito = $this->session->userdata('carrito') ?: array();
        if (empty($carrito)) {
            redirect('tienda');
            return;
        }

        // Calcular total
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        /*if($tipo_pago != '1'){ // Distinto a Tarjeta
            traza("Distinto a Tarjeta");
            $dni = "11111111";
            $nombres = "DESCONOCIDO";
            $direccion = "SIN DIRECCION";
            $celular = "999999999";

            $datos_pedido = array(
                'total'           => $total,
                'direccion_envio' => $direccion,
                'celular'         => $celular,
                'dni'             => $dni,
                'nombres'         => $nombres,
                'observaciones'   => 'Nada nuevo',
            );

        }else{*/
            //traza("Pago por tarjeta");
            $this->form_validation->set_rules('dni', 'DNI', 'required|min_length[8]|max_length[15]|numeric');
            $this->form_validation->set_rules('nombres', 'Nombres completos', 'required|min_length[3]|max_length[200]');
            $this->form_validation->set_rules('direccion_envio', 'Dirección de envío', 'required|min_length[5]|max_length[500]');
            $this->form_validation->set_rules('celular', 'Celular', 'required|min_length[9]|max_length[20]');

            if (!$this->form_validation->run()) {
                //$this->checkout();
                redirect('carrito');
                return;
            }

            // Crear pedido en BD
            $datos_pedido = array(
                'total'           => $total,
                'direccion_envio' => $this->input->post('direccion_envio', TRUE),
                'celular'         => $this->input->post('celular', TRUE),
                'dni'             => $this->input->post('dni', TRUE),
                'nombres'         => $this->input->post('nombres', TRUE),
                'observaciones'   => $this->input->post('observaciones', TRUE),
            );
        //}

        traza("Maroma");

        traza(print_r($datos_pedido,true));

        $id_pedido = $this->Pedido_model->crear($datos_pedido);

        // Agregar items del carrito al detalle
        foreach ($carrito as $item) {
            $this->Pedido_model->agregar_detalle(array(
                'id_pedido'       => $id_pedido,
                'id_producto'     => $item['id'],
                'talla'           => $item['talla'],
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $item['precio'],
            ));
        }

        // Procesar pago (simulado)
        $datos_pago = array(
            'total'  => $total,
            'nombre' => $datos_pedido['nombres'],
            'dni'    => $datos_pedido['dni'],
        );

        $resultado = $this->Pasarela_model->simular_pago($datos_pago);

        if ($resultado['success']) {
            $this->Pedido_model->actualizar_pago($id_pedido, 'Pagado', $resultado['codigo']);
            $this->session->unset_userdata('carrito');
            redirect('pedido/gracias/' . $id_pedido);
        } else {
            $this->Pedido_model->actualizar_pago($id_pedido, 'Fallido', '');
            $this->session->set_flashdata('error', 'El pago no pudo procesarse. Intente nuevamente.');
            redirect('pedido/cancelado');
        }
    }

    public function gracias($id_pedido) {
        $id_pedido = (int)$id_pedido;
        $pedido    = $this->Pedido_model->get_por_id($id_pedido);
        $detalle   = $this->Pedido_model->get_detalle($id_pedido);

        if (!$pedido) {
            redirect('tienda');
            return;
        }

        $data = array(
            'titulo'        => 'Pedido confirmado - ' . $this->config->item('tienda_nombre'),
            'pedido'        => $pedido,
            'detalle'       => $detalle,
            'carrito_count' => 0,
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('pago/gracias', $data);
        $this->load->view('layouts/footer');
    }

    public function cancelado() {
        $data = array(
            'titulo'        => 'Pago cancelado - ' . $this->config->item('tienda_nombre'),
            'carrito_count' => $this->_carrito_count(),
        );
        $this->load->view('layouts/header', $data);
        $this->load->view('pago/cancelado', $data);
        $this->load->view('layouts/footer');
    }

    private function _carrito_count() {
        $carrito = $this->session->userdata('carrito');
        if (!is_array($carrito)) return 0;
        return array_sum(array_column($carrito, 'cantidad'));
    }
}
