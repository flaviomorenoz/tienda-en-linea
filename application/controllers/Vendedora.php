<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vendedora extends CI_Controller {

    private $vendedora;

    public function __construct() {
        parent::__construct();
        $this->load->model('Vendedora_model');
    }

    public function panel() {
        $this->_check_vendedora();

        $data = array(
            'titulo'          => 'Chats con clientes - Admin',
            'admin_nombre'    => $this->session->userdata('admin_nombre'),
            'vendedora'       => $this->vendedora,
            'carrito_count'   => 0,
        );

        $this->load->view('admin/chats', $data);
    }

    public function en_espera_json() {
        $this->_check_vendedora();

        $conversaciones = $this->Vendedora_model->en_espera();

        $rows = array();
        foreach ($conversaciones as $c) {
            $rows[] = array(
                'id'               => (int)$c->id,
                'nombre_cliente'   => $c->nombre_cliente,
                'motivo'           => $c->motivo_transferencia,
                'ultima_actividad' => $c->ultima_actividad,
            );
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array('data' => $rows)));
    }

    public function historial() {
        $this->_check_vendedora();

        $data = array(
            'titulo'        => 'Historial de chats - Admin',
            'admin_nombre'  => $this->session->userdata('admin_nombre'),
            'vendedora'     => $this->vendedora,
            'carrito_count' => 0,
        );

        $this->load->view('admin/chat_historial', $data);
    }

    public function historial_json() {
        $this->_check_vendedora();

        $conversaciones = $this->Vendedora_model->historial($this->vendedora->id);

        $rows = array();
        foreach ($conversaciones as $c) {
            $rows[] = array(
                'id'              => (int)$c->id,
                'nombre_cliente'  => $c->nombre_cliente,
                'celular_cliente' => $c->celular_cliente,
                'motivo'          => $c->motivo_transferencia,
                'iniciado_en'     => $c->iniciado_en,
                'cerrado_en'      => $c->cerrado_en,
                'total_mensajes'  => (int)$c->total_mensajes,
            );
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array('data' => $rows)));
    }

    public function mis_conversaciones_json() {
        $this->_check_vendedora();

        $conversaciones = $this->Vendedora_model->asignadas($this->vendedora->id);

        $rows = array();
        foreach ($conversaciones as $c) {
            $rows[] = array(
                'id'               => (int)$c->id,
                'nombre_cliente'   => $c->nombre_cliente,
                'ultima_actividad' => $c->ultima_actividad,
            );
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array('data' => $rows)));
    }

    public function tomar($id_conversacion) {
        $this->_check_vendedora();

        $ok = $this->Vendedora_model->tomar((int)$id_conversacion, $this->vendedora->id, $this->vendedora->nombre_visible);

        echo json_encode(array('ok' => $ok));
    }

    public function mensajes($id_conversacion) {
        $this->_check_vendedora();

        $data = $this->Vendedora_model->mensajes_completos((int)$id_conversacion, $this->vendedora->id);

        if (!$data) {
            echo json_encode(array('ok' => false, 'error' => 'Conversación no encontrada.'));
            return;
        }

        $rows = array();
        foreach ($data['mensajes'] as $m) {
            $rows[] = array(
                'id'     => (int)$m->id,
                'emisor' => $m->emisor,
                'texto'  => $m->mensaje,
            );
        }

        echo json_encode(array(
            'ok'        => true,
            'estado'    => $data['conversacion']->estado,
            'mensajes'  => $rows,
        ));
    }

    public function responder($id_conversacion) {
        $this->_check_vendedora();

        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $mensaje = trim((string)$this->input->post('mensaje'));

        if ($mensaje === '') {
            echo json_encode(array('ok' => false, 'error' => 'Escribe un mensaje.'));
            return;
        }

        $ok = $this->Vendedora_model->responder((int)$id_conversacion, $this->vendedora->id, $mensaje);

        echo json_encode(array('ok' => $ok));
    }

    public function cerrar($id_conversacion) {
        $this->_check_vendedora();

        $ok = $this->Vendedora_model->cerrar((int)$id_conversacion, $this->vendedora->id);

        echo json_encode(array('ok' => $ok));
    }

    public function disponibilidad() {
        $this->_check_vendedora();

        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $estado = $this->input->post('estado');

        if (!in_array($estado, array('disponible', 'ocupada'), true)) {
            echo json_encode(array('ok' => false, 'error' => 'Estado inválido.'));
            return;
        }

        $this->Vendedora_model->set_estado($this->vendedora->id, $estado);

        echo json_encode(array('ok' => true));
    }

    private function _check_vendedora() {
        if (!$this->session->userdata('admin_logueado')) {
            redirect('admin');
            exit;
        }

        $admin_id = $this->session->userdata('admin_id');
        $this->vendedora = $admin_id ? $this->Vendedora_model->get_by_admin($admin_id) : null;

        if (!$this->vendedora) {
            $this->session->set_flashdata('error', 'Tu cuenta no tiene un perfil de vendedora asignado.');
            redirect('admin/pedidos');
            exit;
        }
    }
}
