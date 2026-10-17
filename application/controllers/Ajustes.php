<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modulo AJUSTES (panel admin).
 *
 * Personaliza los textos de la tienda:
 *   - Tema CSS (config_ajustes.tema_css)
 *   - Textos del banner superior y nombre del negocio (tec_variables)
 *   - CRUD completo de las variables de la tienda (tec_variables)
 */
class Ajustes extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('ajustes_model');
    }

    /**
     * Pantalla principal: apariencia, textos y listado de variables.
     */
    public function index() {
        if (!$this->_check_admin()) {
            return;
        }

        $data = array(
            'titulo'            => 'Ajustes - Admin',
            'subtitulo'         => 'Personalización del banner, nombre del negocio y variables de la tienda',
            'admin_nombre'      => $this->session->userdata('admin_nombre'),
            'tema_activo'       => $this->ajustes_model->get_tema_activo(),
            'temas_disponibles' => $this->ajustes_model->temas_disponibles(),
            'texto_banner'      => $this->ajustes_model->get_texto_banner(),
            'nombre_negocio'    => $this->ajustes_model->get_nombre_negocio(),
            'variables'         => $this->ajustes_model->get_variables(),
            'variable_name_len' => $this->ajustes_model::VARIABLE_NAME_MAX,
            'variable_max_len'  => $this->ajustes_model::VARIABLE_DATO_MAX,
        );

        $this->load->view('layouts/header1', $data);
        $this->load->view('admin/ajustes', $data);
        $this->load->view('layouts/footer1');
    }

    /**
     * POST: guarda el tema CSS activo y los textos del banner / negocio
     * (tec_variables -> TEXTO_BANNER y NOMBRE_NEGOCIO).
     */
    public function guardar_tema() {
        if (!$this->_check_admin()) {
            return;
        }

        if ($this->input->method() !== 'post') {
            redirect('ajustes');
            return;
        }

        $tema           = $this->input->post('tema_css', TRUE);
        $temas          = $this->ajustes_model->temas_disponibles();
        $texto_banner   = trim((string)$this->input->post('texto_banner', TRUE));
        $nombre_negocio = trim((string)$this->input->post('nombre_negocio', TRUE));

        if (!array_key_exists($tema, $temas)) {
            $this->session->set_flashdata('error', 'El tema seleccionado no es válido.');
            redirect('ajustes');
            return;
        }

        if ($texto_banner === '') {
            $this->session->set_flashdata('error', 'El texto del banner no puede estar vacío.');
            redirect('ajustes');
            return;
        }

        if ($nombre_negocio === '') {
            $this->session->set_flashdata('error', 'El nombre del negocio no puede estar vacío.');
            redirect('ajustes');
            return;
        }

        if (mb_strlen($texto_banner) > $this->ajustes_model::VARIABLE_DATO_MAX) {
            $this->session->set_flashdata('error', 'El texto del banner supera los ' . $this->ajustes_model::VARIABLE_DATO_MAX . ' caracteres permitidos.');
            redirect('ajustes');
            return;
        }

        $ok = $this->ajustes_model->set_config('tema_css', $tema);
        $ok = $this->ajustes_model->set_variable('TEXTO_BANNER', $texto_banner) && $ok;
        $ok = $this->ajustes_model->set_variable('NOMBRE_NEGOCIO', $nombre_negocio) && $ok;

        if ($ok) {
            $this->session->set_flashdata('success', 'Ajustes guardados correctamente.');
        } else {
            $this->session->set_flashdata('error', 'No se pudieron guardar los ajustes. Intente nuevamente.');
        }

        redirect('ajustes');
    }

    /**
     * POST: crea o actualiza una variable de la tienda (tec_variables).
     * Si llega variable_id > 0 actualiza; si no, registra una nueva.
     */
    public function guardar_variable() {
        if (!$this->_check_admin()) {
            return;
        }

        if ($this->input->method() !== 'post') {
            redirect('ajustes');
            return;
        }

        $id       = (int)$this->input->post('variable_id');
        $name     = strtoupper(trim((string)$this->input->post('name', TRUE)));
        $dato     = trim((string)$this->input->post('dato', TRUE));
        $store_id = (int)$this->input->post('store_id');

        if ($store_id <= 0) {
            $store_id = 1;
        }

        if (!$this->ajustes_model->nombre_variable_valido($name)) {
            $this->session->set_flashdata('error', 'El nombre debe tener entre 3 y ' . $this->ajustes_model::VARIABLE_NAME_MAX . ' caracteres: mayúsculas, números y guion bajo. Ejemplo: TEXTO_BANNER');
            redirect('ajustes');
            return;
        }

        if ($dato === '') {
            $this->session->set_flashdata('error', 'El valor (dato) de la variable no puede estar vacío.');
            redirect('ajustes');
            return;
        }

        if (mb_strlen($dato) > $this->ajustes_model::VARIABLE_DATO_MAX) {
            $this->session->set_flashdata('error', 'El valor (dato) supera los ' . $this->ajustes_model::VARIABLE_DATO_MAX . ' caracteres permitidos.');
            redirect('ajustes');
            return;
        }

        if ($this->ajustes_model->existe_variable($name, $id > 0 ? $id : NULL)) {
            $this->session->set_flashdata('error', 'Ya existe una variable con el nombre ' . $name . '. Use otro nombre.');
            redirect('ajustes');
            return;
        }

        if ($id > 0) {
            $ok          = $this->ajustes_model->actualizar_variable($id, $name, $dato, $store_id);
            $mensaje_ok  = 'Variable ' . $name . ' actualizada correctamente.';
            $mensaje_err = 'No se pudo actualizar la variable.';
        } else {
            $ok          = $this->ajustes_model->crear_variable($name, $dato, $store_id);
            $mensaje_ok  = 'Variable ' . $name . ' registrada correctamente.';
            $mensaje_err = 'No se pudo registrar la variable.';
        }

        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? $mensaje_ok : $mensaje_err);

        redirect('ajustes');
    }

    /**
     * POST: elimina una variable de la tienda.
     */
    public function eliminar_variable($id) {
        if (!$this->_check_admin()) {
            return;
        }

        if ($this->input->method() !== 'post') {
            redirect('ajustes');
            return;
        }

        $variable = $this->ajustes_model->get_variable_por_id($id);

        if (!$variable) {
            $this->session->set_flashdata('error', 'La variable no existe.');
            redirect('ajustes');
            return;
        }

        $ok = $this->ajustes_model->eliminar_variable($id);

        $this->session->set_flashdata(
            $ok ? 'success' : 'error',
            $ok ? 'Variable ' . $variable->name . ' eliminada correctamente.' : 'No se pudo eliminar la variable.'
        );

        redirect('ajustes');
    }

    /**
     * Solo administradores logueados pueden entrar al modulo.
     *
     * @return bool
     */
    private function _check_admin() {
        if (!$this->session->userdata('admin_logueado')) {
            redirect('admin');
            return FALSE;
        }

        return TRUE;
    }
}