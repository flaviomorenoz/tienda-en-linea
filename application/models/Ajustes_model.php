<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajustes_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Obtiene el valor de una configuración.
     *
     * @param string $clave
     * @param string $default Valor por defecto si no existe.
     * @return string
     */
    public function get_config($clave, $default = '') {
        $this->db->where('clave', $clave);
        $row = $this->db->get('config_ajustes')->row();

        if ($row && !empty($row->valor)) {
            return $row->valor;
        }

        return $default;
    }

    /**
     * Guarda (o actualiza) el valor de una configuración.
     *
     * @param string $clave
     * @param string $valor
     * @return bool
     */
    public function set_config($clave, $valor) {
        $this->db->where('clave', $clave);
        $existe = $this->db->get('config_ajustes')->row();

        $data = array(
            'valor'      => $valor,
            'updated_at' => date('Y-m-d H:i:s'),
        );

        if ($existe) {
            return $this->db->where('clave', $clave)->update('config_ajustes', $data);
        }

        $data['clave'] = $clave;
        return $this->db->insert('config_ajustes', $data);
    }

    /**
     * Temas CSS disponibles para la tienda.
     * La clave es el nombre del archivo CSS y el valor es el nombre visible.
     *
     * @return array
     */
    public function temas_disponibles() {
        return array(
            'tienda.css'  => 'Estilo 1 (tienda.css) Rosa',
            'tienda2.css' => 'Estilo 2 (tienda2.css) Chocolate',
            'tienda3.css' => 'Estilo 3 (tienda3.css) Blanco-rosas',
            'tienda4.css' => 'Estilo 4 (tienda4.css) Electrico',
        );
    }

    /**
     * Devuelve el tema CSS activo validando que sea uno de los permitidos.
     *
     * @return string
     */
    public function get_tema_activo() {
        $tema = $this->get_config('tema_css', 'tienda3.css');
        $temas = $this->temas_disponibles();

        if (!array_key_exists($tema, $temas)) {
            $tema = 'tienda3.css';
        }

        return $tema;
    }
}