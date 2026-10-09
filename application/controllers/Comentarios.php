<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Guardado de los comentarios de producto que escribe el cliente.
 *
 * El popup que los captura es mostrar_popup_comentario() en
 * assets/js/comentarios.js (boton "Comenta" de las tarjetas del catalogo).
 */
class Comentarios extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Comentario_model');
        $this->load->model('Producto_model');
    }

    /**
     * POST (ajax): guarda el comentario de un producto.
     *
     * @return void  Responde JSON: {ok, mensaje|error, comentario, csrf_token}
     */
    public function guardar() {
        if ($this->input->method() !== 'post') {
            return $this->_json(array('ok' => FALSE, 'error' => 'Método no permitido.'), 405);
        }

        $id_producto = (int) $this->input->post('id_producto');
        $comentario  = trim((string) $this->input->post('comentario', TRUE));
        $largo       = function_exists('mb_strlen') ? mb_strlen($comentario, 'UTF-8') : strlen($comentario);

        if ($id_producto <= 0) {
            return $this->_json(array('ok' => FALSE, 'error' => 'Producto no válido.'), 400);
        }

        if ($comentario === '') {
            return $this->_json(array('ok' => FALSE, 'error' => 'Escribe tu comentario antes de enviarlo.'), 400);
        }

        if ($largo > Comentario_model::MAX_LARGO) {
            return $this->_json(array(
                'ok'    => FALSE,
                'error' => 'El comentario no puede pasar de ' . Comentario_model::MAX_LARGO . ' caracteres.',
            ), 400);
        }

        // get_por_id solo devuelve productos activos.
        $producto = $this->Producto_model->get_por_id($id_producto);
        if (!$producto) {
            return $this->_json(array('ok' => FALSE, 'error' => 'El producto ya no está disponible.'), 404);
        }

        $id = $this->Comentario_model->crear($id_producto, $comentario);

        if (!$id) {
            return $this->_json(array(
                'ok'    => FALSE,
                'error' => 'No se pudo guardar el comentario. Intenta nuevamente.',
            ), 500);
        }

        $this->_json(array(
            'ok'         => TRUE,
            'mensaje'    => '¡Gracias! Tu comentario fue publicado.',
            'comentario' => array(
                'id'         => $id,
                'id_producto' => $id_producto,
                'producto'   => $producto->nombre,
                'comentario' => $comentario,
                'fecha'      => date('d/m/Y H:i'),
            ),
        ));
    }

    /**
     * Respuesta JSON con el codigo HTTP indicado.
     *
     * Siempre incluye el token CSRF vigente: con csrf_regenerate = TRUE el
     * hash cambia en cada POST, asi que tambien las respuestas de error deben
     * devolverlo o el siguiente envio del popup fallaria con error 403.
     */
    private function _json($datos, $status = 200) {
        if (is_array($datos) && !array_key_exists('csrf_token', $datos)) {
            $datos['csrf_token'] = $this->security->get_csrf_hash();
        }

        $this->output
             ->set_status_header($status)
             ->set_content_type('application/json')
             ->set_output(json_encode($datos));
    }
}
