<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vendedora extends CI_Controller {

    private $vendedora;

    public function __construct() {
        parent::__construct();
        traza("Estoy en el metodo __construct con los parametros de entrada: sin parametros de entrada");
        $this->load->model('Vendedora_model');
    }

    public function panel() {
        traza("Estoy en el metodo panel con los parametros de entrada: sin parametros de entrada");
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
        traza("Estoy en el metodo en_espera_json con los parametros de entrada: sin parametros de entrada");
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
        traza("Estoy en el metodo historial con los parametros de entrada: sin parametros de entrada");
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
        traza("Estoy en el metodo historial_json con los parametros de entrada: sin parametros de entrada");
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

    public function actualizar_cliente($id_conversacion) {
        traza("Estoy en el metodo actualizar_cliente con los parametros de entrada: id_conversacion=$id_conversacion");
        $this->_check_vendedora();

        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $nombre  = trim((string)$this->input->post('nombre_cliente'));
        $celular = trim((string)$this->input->post('celular_cliente'));

        if ($nombre !== '' && mb_strlen($nombre) > 200) {
            echo json_encode(array('ok' => false, 'error' => 'El nombre es demasiado largo.'));
            return;
        }

        if ($celular !== '' && !preg_match('/^[\d\s\-\+]{6,20}$/', $celular)) {
            echo json_encode(array('ok' => false, 'error' => 'El celular no es válido.'));
            return;
        }

        $ok = $this->Vendedora_model->actualizar_cliente(
            (int)$id_conversacion,
            $this->vendedora->id,
            $nombre !== '' ? $nombre : null,
            $celular !== '' ? $celular : null
        );

        if (!$ok) {
            echo json_encode(array('ok' => false, 'error' => 'Conversación no encontrada.'));
            return;
        }

        echo json_encode(array('ok' => true));
    }

    public function mis_conversaciones_json() {
        traza("Estoy en el metodo mis_conversaciones_json con los parametros de entrada: sin parametros de entrada");
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
        traza("Estoy en el metodo tomar con los parametros de entrada: id_conversacion=$id_conversacion");
        $this->_check_vendedora();

        $ok = $this->Vendedora_model->tomar((int)$id_conversacion, $this->vendedora->id, $this->vendedora->nombre_visible);

        echo json_encode(array('ok' => $ok));
    }

    public function mensajes($id_conversacion) {
        traza("Estoy en el metodo mensajes con los parametros de entrada: id_conversacion=$id_conversacion");
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
                'imagen' => $m->imagen ? base_url('chat/imagen/' . $m->imagen) : null,
            );
        }

        echo json_encode(array(
            'ok'        => true,
            'estado'    => $data['conversacion']->estado,
            'mensajes'  => $rows,
        ));
    }

    public function responder($id_conversacion) {
        traza("Estoy en el metodo responder con los parametros de entrada: id_conversacion=$id_conversacion");
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
        traza("Estoy en el metodo cerrar con los parametros de entrada: id_conversacion=$id_conversacion");
        $this->_check_vendedora();

        $ok = $this->Vendedora_model->cerrar((int)$id_conversacion, $this->vendedora->id);

        echo json_encode(array('ok' => $ok));
    }

    public function disponibilidad() {
        traza("Estoy en el metodo disponibilidad con los parametros de entrada: sin parametros de entrada");
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
        traza("Estoy en el metodo _check_vendedora con los parametros de entrada: sin parametros de entrada");
        if (!$this->session->userdata('admin_logueado')) {
            redirect('admin');
            exit;
        }

        $admin_id = $this->session->userdata('admin_id');
        traza("Valor del admin_id $admin_id");
        $this->vendedora = $admin_id ? $this->Vendedora_model->get_by_admin($admin_id) : null;

        if (!$this->vendedora) {
            $this->session->set_flashdata('error', 'Tu cuenta no tiene un perfil de vendedora asignado.');
            redirect('admin/pedidos');
            exit;
        }
    }
}
