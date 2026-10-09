<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Producto_model');
        $this->load->model('ajustes_model');
    }

    public function getSexo() { // web
    	$id_product = trim($_GET["id_product"]);
		$cSql = "select sexo from v_products where id=?";
		echo $this->db->query($cSql,$id_product)->row()->sexo;
	}
}