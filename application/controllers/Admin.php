<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Admin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Pedido_model');
        $this->load->model('Vendedora_model');
        $this->load->library('form_validation');
    }

    public function login() {
        if ($this->session->userdata('admin_logueado')) {
            redirect('admin/pedidos');
            return;
        }

        $data = array(
            'titulo'        => 'Admin - ' . $this->config->item('tienda_nombre'),
            'carrito_count' => 0,
        );
        $this->load->view('admin/login', $data);
    }

    public function login_post() {
        if ($this->input->method() !== 'post') {
            redirect('admin');
            return;
        }

        $usuario  = $this->input->post('usuario', TRUE);
        $password = $this->input->post('password');

        $this->db->where('usuario', $usuario);
        $admin = $this->db->get('admins')->row();

        if ($admin && password_verify($password, $admin->password_hash)) {
            $this->session->set_userdata(array(
                'admin_logueado' => TRUE,
                'admin_id'       => $admin->id,
                'admin_usuario'  => $admin->usuario,
                'admin_nombre'   => $admin->nombre,
            ));

            $vendedora = $this->Vendedora_model->get_by_admin($admin->id);
            if ($vendedora) {
                $this->Vendedora_model->set_estado($vendedora->id, 'disponible');
            }

            redirect('admin/pedidos');
        } else {
            $this->session->set_flashdata('error', 'Usuario o contraseña incorrectos.');
            redirect('admin');
        }
    }

    public function logout() {
        $admin_id  = $this->session->userdata('admin_id');
        $vendedora = $admin_id ? $this->Vendedora_model->get_by_admin($admin_id) : null;
        if ($vendedora) {
            $this->Vendedora_model->set_estado($vendedora->id, 'desconectada');
        }

        $this->session->unset_userdata('admin_logueado');
        $this->session->unset_userdata('admin_id');
        $this->session->unset_userdata('admin_usuario');
        $this->session->unset_userdata('admin_nombre');
        redirect('admin');
    }

    public function pedidos() {
        $this->_check_admin();

        $data = array(
            'titulo'        => 'Gestión de Pedidos - Admin',
            'admin_nombre'  => $this->session->userdata('admin_nombre'),
            'carrito_count' => 0,
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('admin/pedidos', $data);
        $this->load->view('layouts/footer');
    }

    public function detalle_json($id_pedido) {
        $this->_check_admin();

        $id_pedido = (int)$id_pedido;
        $items     = $this->Pedido_model->get_detalle_pedido($id_pedido);
        $moneda    = $this->config->item('moneda_simbolo');

        $rows = array();
        foreach ($items as $item) {
            $rows[] = array(
                'id_producto'     => (int)$item->id_producto,
                'name'            => $item->name,
                'talla'           => $item->talla,
                'cantidad'        => (int)$item->cantidad,
                'precio_unitario' => (float)$item->precio_unitario,
                'id_unidad'       => $item->id_unidad,
                'unidad'          => $item->descrip,
            );
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array(
                 'id_pedido' => $id_pedido,
                 'moneda'    => $moneda,
                 'items'     => $rows,
             )));
    }

    public function pedidos_json() {
        $this->_check_admin();

        $pedidos = $this->Pedido_model->get_todos();
        $moneda  = $this->config->item('moneda_simbolo');

        $rows = array();
        foreach ($pedidos as $p) {
            $rows[] = array(
                'id'                 => (int)$p->id,
                'fecha'              => date('d/m/Y H:i', strtotime($p->fecha)),
                'fecha_raw'          => $p->fecha,
                'nombres'            => $p->nombres,
                'direccion_envio'    => $p->direccion_envio,
                'dni'                => $p->dni,
                'celular'            => $p->celular,
                'total'              => (float)$p->total,
                'moneda'             => $moneda,
                'estado_pago'        => $p->estado_pago,
                'nro_orden'          => $p->nro_orden,
                'codigo_transaccion' => $p->codigo_transaccion,
                'estado_envio'       => $p->estado_envio,
            );
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array('data' => $rows)));
    }

    public function guardar_codigos($id_pedido) {
        $this->_check_admin();

        if ($this->input->method() !== 'post') {
            redirect('admin/pedidos');
            return;
        }

        $id_pedido           = (int)$id_pedido;
        $nro_orden           = trim($this->input->post('nro_orden', TRUE));
        $codigo_transaccion  = trim($this->input->post('codigo_transaccion', TRUE));

        if (!preg_match('/^\d{8,10}$/', $nro_orden)) {
            $this->session->set_flashdata('error', 'El Nro de Orden debe tener entre 8 y 10 dígitos.');
            redirect('admin/pedidos');
            return;
        }

        if (!preg_match('/^[A-Za-z0-9]{4}$/', $codigo_transaccion)) {
            $this->session->set_flashdata('error', 'El Código de envío debe tener exactamente 4 caracteres alfanuméricos.');
            redirect('admin/pedidos');
            return;
        }

        $ok = $this->Pedido_model->actualizar_codigos($id_pedido, $nro_orden, strtoupper($codigo_transaccion));

        if ($ok) {
            $this->session->set_flashdata('success', 'Códigos registrados correctamente.');
        } else {
            $this->session->set_flashdata('error', 'No se pudo actualizar el pedido.');
        }

        redirect('admin/pedidos');
    }

    public function actualizar_estado($id_pedido) {
        $this->_check_admin();

        if ($this->input->method() !== 'post') {
            redirect('admin/pedidos');
            return;
        }

        $id_pedido   = (int)$id_pedido;
        $estado_envio = $this->input->post('estado_envio', TRUE);

        $ok = $this->Pedido_model->actualizar_estado($id_pedido, $estado_envio);

        if ($ok) {
            $this->session->set_flashdata('success', 'Estado actualizado correctamente.');
        } else {
            $this->session->set_flashdata('error', 'Estado no válido.');
        }

        redirect('admin/pedidos');
    }

    private function _check_admin() {
        if (!$this->session->userdata('admin_logueado')) {
            redirect('admin');
        }
    }
}
