<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Panel de administración (solo lectura) del asistente IA "Tía"
 * que atiende por WhatsApp. Datos en tia_conversaciones / tia_mensajes.
 */
class Tia_conversa extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Tia_conversa_model');
    }

    public function index() {
        $this->_check_admin();

        $data = array(
            'titulo'        => 'Conversaciones IA - Admin',
            'admin_nombre'  => $this->session->userdata('admin_nombre'),
            'carrito_count' => 0,
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('admin/tia_conversa/index', $data);
        $this->load->view('layouts/footer');
    }

    public function json() {
        $this->_check_admin();

        $conversaciones = $this->Tia_conversa_model->get_conversaciones();

        $rows = array();
        foreach ($conversaciones as $c) {
            $rows[] = array(
                'id'             => (int)$c->id,
                'contacto'       => $c->contacto,
                'estado'         => $c->estado,
                'fecha_inicio'   => $c->fecha_inicio,
                'fecha_fin'      => $c->fecha_fin,
                'total_mensajes' => (int)$c->total_mensajes,
            );
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array('data' => $rows)));
    }

    public function mensajes($id_conversacion) {
        $this->_check_admin();

        $mensajes = $this->Tia_conversa_model->get_mensajes((int)$id_conversacion);

        $rows = array();
        foreach ($mensajes as $m) {
            $rows[] = array(
                'id'    => (int)$m->id,
                'rol'   => $m->rol,
                'texto' => $m->mensaje,
                'fecha' => $m->fecha,
            );
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array('ok' => true, 'mensajes' => $rows)));
    }

    private function _check_admin() {
        if (!$this->session->userdata('admin_logueado')) {
            redirect('admin');
            exit;
        }
    }
}
