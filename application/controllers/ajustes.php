<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajustes extends CI_Controller {

    public function __construct() {
        parent::__construct();
        //$this->load->model('Vendedora_model');
    }

    public function index() {
        $data = array(
            'titulo'          => 'Ajustes - Admin',
            'admin_nombre'    => $this->session->userdata('admin_nombre')
        );

        $this->load->view('layouts/header1', $data);
        $this->load->view('admin/ajustes', $data);
        $this->load->view('layouts/footer1');
    }
}