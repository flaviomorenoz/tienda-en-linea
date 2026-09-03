<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Libro de Reclamaciones (Perú).
 * Base legal:
 *  - Ley N° 29571 (arts. 150-152) - D.S. N° 011-2011-PCM - D.S. N° 004-2024-PCM
 */
class Libro_reclamaciones extends CI_Controller {

    const ESTADOS = array('RECIBIDO', 'EN_PROCESO', 'RESPONDIDO', 'ARCHIVADO');

    public function __construct() {
        parent::__construct();
        $this->load->model('Reclamo_model');
        $this->load->library('form_validation');
    }

    /**
     * GET /libro-reclamaciones -> muestra el formulario (Hoja de Reclamación).
     */
    public function index() {
        $data = array(
            'titulo'        => 'Libro de Reclamaciones - ' . $this->config->item('tienda_nombre'),
            'carrito_count' => $this->_carrito_count(),
            'texto_banner'  => $this->_texto_banner(),
            'registrado'    => FALSE,
        );
        $this->_render_formulario($data);
    }

    /**
     * POST /libro-reclamaciones/enviar -> valida y registra la hoja.
     */
    public function enviar() {
        if ($this->input->method() !== 'post') {
            redirect('libro-reclamaciones');
            return;
        }

        $this->_reglas_validacion();

        if ($this->form_validation->run() === FALSE) {
            $data = array(
                'titulo'        => 'Libro de Reclamaciones - ' . $this->config->item('tienda_nombre'),
                'carrito_count' => $this->_carrito_count(),
                'texto_banner'  => $this->_texto_banner(),
                'registrado'    => FALSE,
            );
            $this->_render_formulario($data);
            return;
        }

        $tipo  = $this->input->post('tipo', TRUE) === 'QUEJA' ? 'QUEJA' : 'RECLAMO';
        $monto = $this->input->post('monto_reclamado', TRUE);
        $monto = ($tipo === 'RECLAMO' && $monto !== '' && $monto !== NULL)
            ? (float)str_replace(',', '.', $monto)
            : NULL;

        $codigo = $this->_generar_codigo();

        $datos = array(
            'codigo'            => $codigo,
            'tipo'              => $tipo,
            'nombres'           => $this->_uppercase(trim($this->input->post('nombres', TRUE))),
            'tipo_documento'    => $this->input->post('tipo_documento', TRUE),
            'numero_documento'  => trim($this->input->post('numero_documento', TRUE)),
            'domicilio'         => trim($this->input->post('domicilio', TRUE)),
            'telefono'          => trim($this->input->post('telefono', TRUE)),
            'email'             => strtolower(trim($this->input->post('email', TRUE))),
            'departamento'      => trim($this->input->post('departamento', TRUE)),
            'provincia'         => trim($this->input->post('provincia', TRUE)),
            'distrito'          => trim($this->input->post('distrito', TRUE)),
            'producto_servicio' => trim($this->input->post('producto_servicio', TRUE)),
            'numero_pedido'     => trim($this->input->post('numero_pedido', TRUE)),
            'monto_reclamado'   => $monto,
            'detalle'           => trim($this->input->post('detalle', TRUE)),
            'estado'            => 'RECIBIDO',
            'ip'                => $this->input->ip_address(),
            'user_agent'        => substr((string)$this->input->user_agent(), 0, 500),
        );

        traza("Libro_reclamaciones->enviar: tipo=$tipo codigo=$codigo email={$datos['email']}");
        $id = $this->Reclamo_model->crear($datos);

        if ($id) {
            $reclamo = $this->Reclamo_model->get_por_id($id);
            $data = array(
                'titulo'        => 'Solicitud registrada - ' . $this->config->item('tienda_nombre'),
                'carrito_count' => $this->_carrito_count(),
                'texto_banner'  => $this->_texto_banner(),
                'registrado'    => TRUE,
                'reclamo'       => $reclamo,
                'plazo_dias'    => 15,
            );
            $this->_render_formulario($data);
        } else {
            $this->session->set_flashdata('error', 'No se pudo registrar su solicitud. Por favor intente nuevamente.');
            redirect('libro-reclamaciones');
        }
    }

    /**
     * GET /libro-reclamaciones/verificar -> formulario de consulta por código.
     */
    public function verificar() {
        $data = array(
            'titulo'        => 'Consultar reclamo - ' . $this->config->item('tienda_nombre'),
            'carrito_count' => $this->_carrito_count(),
            'texto_banner'  => $this->_texto_banner(),
            'reclamo'       => NULL,
            'error'         => '',
        );
        $this->load->view('layouts/header2', $data);
        $this->load->view('tienda/libro_reclamaciones_verificar', $data);
        $this->load->view('layouts/footer2');
    }

    /**
     * POST /libro-reclamaciones/consultar -> muestra el estado de la hoja.
     */
    public function consultar() {
        if ($this->input->method() !== 'post') {
            redirect('libro-reclamaciones/verificar');
            return;
        }

        $codigo = strtoupper(trim($this->input->post('codigo', TRUE)));

        $data = array(
            'titulo'        => 'Consultar reclamo - ' . $this->config->item('tienda_nombre'),
            'carrito_count' => $this->_carrito_count(),
            'texto_banner'  => $this->_texto_banner(),
            'reclamo'       => NULL,
            'error'         => '',
        );

        if ($codigo === '') {
            $data['error'] = 'Ingrese el código de su solicitud.';
        } else {
            $data['reclamo'] = $this->Reclamo_model->get_por_codigo($codigo);
            if (!$data['reclamo']) {
                $data['error'] = 'No se encontró ninguna solicitud con el código <strong>' .
                    htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') . '</strong>.';
            }
        }

        $this->load->view('layouts/header2', $data);
        $this->load->view('tienda/libro_reclamaciones_verificar', $data);
        $this->load->view('layouts/footer2');
    }

    /**
     * Validador personalizado: el monto es obligatorio solo para RECLAMO.
     */
    public function validar_monto($valor) {
        if ($this->input->post('tipo') === 'RECLAMO' && ($valor === '' || $valor === NULL)) {
            $this->form_validation->set_message('validar_monto', 'Debe indicar el monto reclamado (S/).');
            return FALSE;
        }
        if ($valor !== '' && $valor !== NULL && !is_numeric(str_replace(',', '.', $valor))) {
            $this->form_validation->set_message('validar_monto', 'El monto reclamado debe ser un valor numérico.');
            return FALSE;
        }
        return TRUE;
    }

    // ---------------------------------------------------------------
    // Privados
    // ---------------------------------------------------------------

    private function _render_formulario($data) {
        $this->load->view('layouts/header2', $data);
        $this->load->view('tienda/libro_reclamaciones', $data);
        $this->load->view('layouts/footer2');
    }

    private function _reglas_validacion() {
        $this->form_validation->set_message('required',    'El campo %s es obligatorio.');
        $this->form_validation->set_message('valid_email', 'Ingrese un correo electrónico válido.');
        $this->form_validation->set_message('min_length',  'El campo %s debe tener al menos %s caracteres.');
        $this->form_validation->set_message('max_length',  'El campo %s no debe superar los %s caracteres.');
        $this->form_validation->set_message('numeric',     'El campo %s debe ser numérico.');
        $this->form_validation->set_message('in_list',     'El valor seleccionado en %s no es válido.');

        $this->form_validation->set_rules('tipo', 'Tipo de solicitud', 'required|in_list[RECLAMO,QUEJA]');
        $this->form_validation->set_rules('nombres', 'Nombres y apellidos', 'required|max_length[250]|trim');
        $this->form_validation->set_rules('tipo_documento', 'Tipo de documento', 'required|in_list[DNI,CE,PASAPORTE,OTRO]');
        $this->form_validation->set_rules('numero_documento', 'Número de documento', 'required|max_length[20]|trim');
        $this->form_validation->set_rules('domicilio', 'Domicilio', 'max_length[255]|trim');
        $this->form_validation->set_rules('telefono', 'Teléfono', 'max_length[30]|trim');
        $this->form_validation->set_rules('email', 'Correo electrónico', 'required|valid_email|max_length[150]|trim');
        $this->form_validation->set_rules('departamento', 'Departamento', 'max_length[100]|trim');
        $this->form_validation->set_rules('provincia', 'Provincia', 'max_length[100]|trim');
        $this->form_validation->set_rules('distrito', 'Distrito', 'max_length[100]|trim');
        $this->form_validation->set_rules('producto_servicio', 'Producto o servicio', 'required|max_length[255]|trim');
        $this->form_validation->set_rules('numero_pedido', 'Número de pedido o comprobante', 'max_length[50]|trim');
        $this->form_validation->set_rules('monto_reclamado', 'Monto reclamado', 'trim|callback_validar_monto');
        $this->form_validation->set_rules('detalle', 'Detalle de su solicitud', 'required|min_length[10]|max_length[2000]|trim');
        $this->form_validation->set_rules('acepto', 'Declaración', 'required');
    }

    /**
     * Genera el código: LR-YYYYMMDD-NNNN (correlativo diario).
     */
    private function _generar_codigo() {
        $fecha_ymd = date('Ymd');
        $n = $this->Reclamo_model->contar_hoy(date('Y-m-d'));
        do {
            $n++;
            $codigo = 'LR-' . $fecha_ymd . '-' . str_pad($n, 4, '0', STR_PAD_LEFT);
            $existe = $this->Reclamo_model->get_por_codigo($codigo);
        } while ($existe);
        return $codigo;
    }

    private function _uppercase($texto) {
        return function_exists('mb_strtoupper')
            ? mb_strtoupper($texto, 'UTF-8')
            : strtoupper($texto);
    }

    private function _carrito_count() {
        $carrito = $this->session->userdata('carrito');
        if (!is_array($carrito)) return 0;
        return array_sum(array_column($carrito, 'cantidad'));
    }

    private function _texto_banner() {
        if (!isset($this->Ajustes_model)) {
            $this->load->model('Ajustes_model');
        }
        return $this->Ajustes_model->get_config('texto_banner');
    }
}


