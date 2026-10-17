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
            'tienda5.css' => 'Estilo 5 (tienda5.css) Aruma',
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

    public function get_banner1(){
        $cSql = "select trim(valor) valor from config_ajustes where clave='texto_banner'";
        $row = $this->db->query($cSql)->row();
        return $row ? trim($row->valor) : '';
    }

    // ------------------------------------------------------------------
    // VARIABLES DE LA TIENDA (tabla tec_variables del ERP)
    //
    // Son los textos configurables desde el modulo AJUSTES:
    //   TEXTO_BANNER   -> texto del banner superior de la tienda
    //   NOMBRE_NEGOCIO -> nombre del negocio en la tienda
    //
    // La columna name es CHAR(20), por eso siempre se trabaja con trim().
    // ------------------------------------------------------------------

    /** Longitud de la columna name en tec_variables. */
    const VARIABLE_NAME_MAX = 20;

    /** Longitud de la columna dato en tec_variables. */
    const VARIABLE_DATO_MAX = 1200;

    /**
     * Valida el nombre de una variable: mayusculas, numeros y guion bajo
     * (por ejemplo TEXTO_BANNER), entre 3 y 20 caracteres.
     *
     * @param string $name
     * @return bool
     */
    public function nombre_variable_valido($name) {
        return (bool)preg_match('/^[A-Z0-9_]{3,' . self::VARIABLE_NAME_MAX . '}$/', strtoupper(trim((string)$name)));
    }

    /**
     * Lista las variables configuradas en la tienda.
     *
     * @return array
     */
    public function get_variables() {
        $cSql = "select id, store_id, trim(name) name, dato, fec_reg
                 from tec_variables
                 order by trim(name)";
        return $this->db->query($cSql)->result();
    }

    /**
     * Una variable por su id.
     *
     * @param int $id
     * @return object|null
     */
    public function get_variable_por_id($id) {
        $cSql = "select id, store_id, trim(name) name, dato, fec_reg
                 from tec_variables
                 where id = ?";
        return $this->db->query($cSql, array((int)$id))->row();
    }

    /**
     * Valor de una variable de la tienda.
     *
     * @param string $name    Nombre de la variable (TEXTO_BANNER, NOMBRE_NEGOCIO, ...)
     * @param string $default Valor devuelto si la variable no existe o esta vacia.
     * @return string
     */
    public function get_variable($name, $default = '') {
        $cSql = "select trim(dato) dato
                 from tec_variables
                 where upper(trim(name)) = upper(?)
                 order by id
                 limit 1";
        $row = $this->db->query($cSql, array(trim((string)$name)))->row();

        if ($row && trim((string)$row->dato) !== '') {
            return trim($row->dato);
        }

        return $default;
    }

    /**
     * Indica si ya existe una variable con ese nombre (para no duplicarlas).
     *
     * @param string   $name
     * @param int|null $excluir_id Id a ignorar (al editar).
     * @return bool
     */
    public function existe_variable($name, $excluir_id = NULL) {
        $cSql   = "select id from tec_variables where upper(trim(name)) = upper(?)";
        $params = array(trim((string)$name));

        if ($excluir_id !== NULL) {
            $cSql .= " and id <> ?";
            $params[] = (int)$excluir_id;
        }

        return $this->db->query($cSql, $params)->row() ? TRUE : FALSE;
    }

    /**
     * Crea una variable de la tienda.
     *
     * @param string $name
     * @param string $dato
     * @param int    $store_id
     * @return bool
     */
    public function crear_variable($name, $dato, $store_id = 1) {
        return $this->db->insert('tec_variables', array(
            'store_id' => (int)$store_id,
            'name'     => strtoupper(trim((string)$name)),
            'dato'     => trim((string)$dato),
            'fec_reg'  => date('Y-m-d'),
        ));
    }

    /**
     * Actualiza una variable de la tienda.
     *
     * @param int      $id
     * @param string   $name
     * @param string   $dato
     * @param int|null $store_id Si es NULL se conserva el actual.
     * @return bool
     */
    public function actualizar_variable($id, $name, $dato, $store_id = NULL) {
        $data = array(
            'name'    => strtoupper(trim((string)$name)),
            'dato'    => trim((string)$dato),
            'fec_reg' => date('Y-m-d'),
        );

        if ($store_id !== NULL) {
            $data['store_id'] = (int)$store_id;
        }

        return $this->db->where('id', (int)$id)->update('tec_variables', $data);
    }

    /**
     * Guarda (crea o actualiza) una variable por su nombre.
     *
     * @param string $name
     * @param string $dato
     * @param int    $store_id
     * @return bool
     */
    public function set_variable($name, $dato, $store_id = 1) {
        $cSql = "select id from tec_variables where upper(trim(name)) = upper(?) order by id limit 1";
        $row  = $this->db->query($cSql, array(trim((string)$name)))->row();

        if ($row) {
            return $this->actualizar_variable($row->id, $name, $dato, $store_id);
        }

        return $this->crear_variable($name, $dato, $store_id);
    }

    /**
     * Elimina una variable de la tienda.
     *
     * @param int $id
     * @return bool
     */
    public function eliminar_variable($id) {
        return $this->db->where('id', (int)$id)->delete('tec_variables');
    }

    /**
     * Texto del banner superior (tec_variables -> TEXTO_BANNER).
     * Si la variable no existe se conserva el valor historico de config_ajustes.
     *
     * @return string
     */
    public function get_texto_banner() {
        return $this->get_variable('TEXTO_BANNER', $this->get_config('texto_banner'));
    }

    /**
     * Nombre del negocio (tec_variables -> NOMBRE_NEGOCIO).
     * Si la variable no existe se conserva el valor de config/settings.php.
     *
     * @return string
     */
    public function get_nombre_negocio() {
        $CI      =& get_instance();
        $default = $CI->config->item('tienda_nombre');

        return $this->get_variable('NOMBRE_NEGOCIO', $default);
    }
}