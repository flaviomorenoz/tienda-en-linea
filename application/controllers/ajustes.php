<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajustes extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Ajustes_model');
    }

    public function index() {
        $admin_logueado = $this->session->userdata('admin_logueado');
        if (!$admin_logueado) {
            redirect('admin');
            return;
        }

        $data = array(
            'titulo'          => 'Ajustes - Admin',
            'admin_nombre'    => $this->session->userdata('admin_nombre'),
            'tema_activo'     => $this->Ajustes_model->get_tema_activo(),
            'temas_disponibles' => $this->Ajustes_model->temas_disponibles(),
        );

        $this->load->view('layouts/header1', $data);
        $this->load->view('admin/ajustes', $data);
        $this->load->view('layouts/footer1');
    }

    public function guardar_tema() {
        $admin_logueado = $this->session->userdata('admin_logueado');
        if (!$admin_logueado) {
            redirect('admin');
            return;
        }

        if ($this->input->method() !== 'post') {
            redirect('ajustes');
            return;
        }

        $tema = $this->input->post('tema_css', TRUE);
        $temas = $this->Ajustes_model->temas_disponibles();

        if (!array_key_exists($tema, $temas)) {
            $this->session->set_flashdata('error', 'El tema seleccionado no es válido.');
            redirect('ajustes');
            return;
        }

        $ok = $this->Ajustes_model->set_config('tema_css', $tema);

        if ($ok) {
            $this->session->set_flashdata('success', 'Tema de estilo actualizado correctamente.');
        } else {
            $this->session->set_flashdata('error', 'No se pudo guardar el tema. Intente nuevamente.');
        }

        redirect('ajustes');
    }
}