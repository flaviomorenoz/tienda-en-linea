<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pasarela_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_config() {
        return $this->db->get('config_pasarela')->row();
    }

    /**
     * Simula el procesamiento de pago.
     * Cuando se integre Izipay, reemplazar el cuerpo de este método
     * con el SDK real de Izipay (script JS + endpoint de tokenización).
     *
     * @param array $datos  ['total', 'nombre', 'dni', 'numero_tarjeta', 'vencimiento', 'cvv']
     * @return array        ['success' => bool, 'codigo' => string, 'mensaje' => string]
     */
    public function simular_pago($datos) {
        // Simular un pequeño retraso de procesamiento
        usleep(500000);

        // Siempre éxito en modo sandbox/fake
        $codigo = 'FAKE-' . strtoupper(uniqid());
        return array(
            'success' => TRUE,
            'codigo'  => $codigo,
            'mensaje' => 'Pago procesado correctamente (modo sandbox)',
        );
    }
}
