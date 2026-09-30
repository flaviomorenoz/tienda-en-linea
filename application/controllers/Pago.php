<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);

/**
 * Pago con Yape (cargo único) de Culqi.
 *
 * Cada paso del flujo vive en su propia URL para que un error no deje el pedido a medias:
 *
 *   1. GET  pago/preparar       -> formulario de datos de envío (el total sale de la sesión)
 *   2. POST pago/crear_orden    -> crea la ORDEN en Culqi (POST /v2/orders). El order es
 *                                  obligatorio para que el checkout muestre Yape.
 *   3. Culqi.open()             -> el checkout pide el celular y el código de aprobación de
 *                                  Yape, y devuelve el token (Culqi.token).
 *   4. POST carrito/recibe_token-> cobra con POST /v2/charges (source_id = token)
 *   5. GET  pago/procesar       -> registra el pedido YA cobrado (pedidos_web + detalle) y
 *                                  avisa por correo
 *   6. GET  pedido/gracias/{id}
 *
 * procesar() NO cobra nada: el cobro lo hace Carrito::recibe_token. Se dejó de usar
 * Pasarela_model::simular_pago(), que marcaba el pedido como "Pagado" con un código
 * falso sin cobrar.
 */
class Pago extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model(array('Pedido_model', 'Pasarela_model', 'Producto_model'));
        $this->load->library('form_validation');
    }

    public function checkout(){
        $carrito = $this->session->userdata('carrito') ?: array();

        if (empty($carrito)) {
            $this->session->set_flashdata('error', 'Tu carrito está vacío.');
            redirect('tienda');
            return;
        }

        $total = $this->_calcular_total($carrito);

        $data = array(
            'titulo'        => 'Finalizar compra - ' . $this->config->item('tienda_nombre'),
            'carrito'       => $carrito,
            'total'         => $total,
            'carrito_count' => array_sum(array_column($carrito, 'cantidad')),
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('tienda/checkout', $data);
        $this->load->view('layouts/footer');
    }

    /**
     * Paso 1: pinta el formulario de datos de envío (views/tienda/preparar.php).
     *
     * El total se recalcula SIEMPRE con el carrito de la sesión: lo que se muestra, lo que
     * se guarda en sesión (pago_total) y lo que se cobrará en Culqi salen de la misma fuente.
     */
    public function preparar(){
        $carrito = $this->session->userdata('carrito') ?: array();

        if (empty($carrito)) {
            $this->session->set_flashdata('error', 'Tu carrito está vacío.');
            redirect('carrito');
            return;
        }

        $total = $this->_calcular_total($carrito);
        traza("Pago->preparar: total de sesión = " . $total);

        $data = array(
            'titulo'        => 'Datos de envío y pago - ' . $this->config->item('tienda_nombre'),
            'carrito'       => $carrito,
            'total'         => $total,
            'carrito_count' => array_sum(array_column($carrito, 'cantidad')),
            'order'         => '',   // lo llena crear_orden() -> Culqi settings.order
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('tienda/preparar', $data);
        $this->load->view('layouts/footer', $data);
    }
    /**
     * Paso 2: crea la orden en Culqi ANTES de abrir su checkout.
     * Docs: https://apidocs.culqi.com/#tag/Ordenes/operation/crear-orden
     *
     * Culqi pide generar un order para mostrar "Yape" entre los medios de pago (si
     * settings.order va vacío, el checkout solo muestra tarjetas).
     *
     * Recibe por POST los datos del formulario de views/tienda/preparar.php
     * (dni, nombres, apellidos, correo, celular, direccion_envio, observaciones).
     * El amount NO se toma del navegador: se recalcula con el carrito de la sesión.
     *
     * Responde SIEMPRE JSON (el JS lee el campo ->order y lo pone en settings.order):
     *   OK    -> {"ok":true, "order":"ord_...", "order_number":"PED-...", "amount":1200}
     *   Error -> {"ok":false, "error":"mensaje para el cliente"}
     */
    public function crear_orden() {
        $this->output->set_content_type('application/json');

        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(array('ok' => FALSE, 'error' => 'Método no permitido.'));
            return;
        }

        $carrito = $this->session->userdata('carrito') ?: array();
        if (empty($carrito)) {
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => FALSE, 'error' => 'Tu carrito está vacío.'));
            return;
        }

        // Llave privada de Culqi (index.php la carga desde application/config/.env)
        $SECRET_KEY = $this->_culqi_llave_privada();
        if ($SECRET_KEY === '') {
            traza("Pago->crear_orden: falta CULQI_LLAVE_PRIVADA");
            $this->output->set_status_header(500);
            echo json_encode(array('ok' => FALSE, 'error' => 'La pasarela no está configurada (falta la llave privada).'));
            return;
        }

        // Datos del titular capturados en el formulario (los exige POST /v2/orders)
        $datos = $this->_datos_pedido_post();
        $error = $this->_validar_datos_pedido($datos);
        if ($error !== '') {
            traza("Pago->crear_orden: " . $error);
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => FALSE, 'error' => $error));
            return;
        }

        // Monto real del carrito de la sesión, en céntimos (Culqi no usa decimales)
        $total  = $this->_calcular_total($carrito);
        $amount = (int)round($total * 100);

        if ($amount <= 0) {
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => FALSE, 'error' => 'El total del pedido no es válido.'));
            return;
        }

        // Número de orden único en el comercio: Culqi rechaza un order_number repetido
        $order_number = 'PED-' . date('YmdHis') . '-' . mt_rand(1000, 9999);

        $payload = array(
            'amount'          => $amount,
            'currency_code'   => 'PEN',
            'description'     => 'Pedido tienda en línea - ' . $this->config->item('tienda_nombre'),
            'order_number'    => $order_number,
            'expiration_date' => time() + 86400,   // 24 h de vigencia para completar el pago
            'confirm'         => TRUE,
            'client_details'  => array(
                'first_name'   => $datos['nombres'],
                'last_name'    => $datos['apellidos'],
                'email'        => $datos['correo'],
                'phone_number' => $datos['celular'],
            ),
            'metadata'        => array('dni' => $datos['dni']),
        );

        traza("Pago->crear_orden: total=$total amount=$amount order_number=$order_number");

        $ch = curl_init('https://api.culqi.com/v2/orders');

        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST           => TRUE,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $SECRET_KEY,
                'Accept: application/json',
            ),
            CURLOPT_TIMEOUT        => 30,
        ));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_curl = curl_error($ch);
            curl_close($ch);
            traza("Pago->crear_orden: error cURL -> " . $error_curl);
            log_message('error', 'Pago::crear_orden: error cURL: ' . $error_curl);
            $this->output->set_status_header(502);
            echo json_encode(array('ok' => FALSE, 'error' => 'No se pudo contactar a la pasarela de pago. Intente nuevamente.'));
            return;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, TRUE);
        traza("Pago->crear_orden: HTTP $httpCode -> " . print_r($result, TRUE));

        if (!is_array($result) || $httpCode >= 400 || empty($result['id'])) {
            $mensaje = 'No se pudo iniciar el pago. Intente nuevamente.';
            if (is_array($result)) {
                if (!empty($result['user_message'])) {
                    $mensaje = $result['user_message'];
                } elseif (!empty($result['merchant_message'])) {
                    $mensaje = $result['merchant_message'];
                }
            }
            log_message('error', 'Pago::crear_orden: HTTP ' . $httpCode . ': ' . $response);
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => FALSE, 'error' => $mensaje));
            return;
        }

        // La orden y los datos del titular quedan en sesión para los pasos 3, 4 y 5
        $this->session->unset_userdata('pago_id_pedido');   // cobro nuevo = pedido nuevo
        $this->session->set_userdata(array(
            'pago_orden'        => $result['id'],
            'pago_order_number' => $order_number,
            'pago_datos'        => $datos,
            'pago_total'        => $total,
        ));

        echo json_encode(array(
            'ok'           => TRUE,
            'order'        => $result['id'],
            'order_number' => $order_number,
            'amount'       => $amount,
        ));
    }

    /**
     * Paso 5: registra en la BD el pedido que YA fue cobrado por Yape.
     *
     * NO cobra nada y NO usa Pasarela_model::simular_pago(): el cargo lo hizo
     * Carrito::recibe_token con POST /v2/charges y su id (chr_...) quedó en sesión
     * como pago_cargo.
     *
     * Es idempotente: si el pedido ya se registró (recargar la URL, volver atrás),
     * pago_id_pedido lo devuelve a pedido/gracias/{id} sin crear otro pedido.
     */
    public function procesar() {
        $carrito   = $this->session->userdata('carrito') ?: array();
        $cargo     = $this->session->userdata('pago_cargo');
        $datos     = $this->session->userdata('pago_datos');
        $total     = $this->session->userdata('pago_total');
        $id_pagado = $this->session->userdata('pago_id_pedido');

        traza("Pago->procesar: cargo=" . ($cargo ? $cargo : '(vacío)'));

        if (!empty($id_pagado)) {
            redirect('pedido/gracias/' . (int)$id_pagado);
            return;
        }

        if (!is_array($datos) || empty($cargo) || empty($carrito)) {
            $this->session->set_flashdata('error', 'No hay un pago aprobado pendiente de registrar.');
            redirect('carrito');
            return;
        }

        if ($total === FALSE || $total === NULL) {
            $total = $this->_calcular_total($carrito);
        }

        // Datos que se guardan en pedidos_web (ver Pedido_model::crear)
        $datos_pedido = array(
            'total'           => $total,
            'direccion_envio' => $datos['direccion_envio'],
            'celular'         => $datos['celular'],
            'dni'             => $datos['dni'],
            'nombres'         => trim($datos['nombres'] . ' ' . $datos['apellidos']),
            'observaciones'   => isset($datos['observaciones']) ? $datos['observaciones'] : '',
            'archivo'         => NULL,   // el pago es por pasarela: no hay comprobante que subir
            'correo'          => $datos['correo'],
        );

        $id_pedido = $this->Pedido_model->crear($datos_pedido);
        traza("Pago->procesar: pedido $id_pedido registrado con el cargo $cargo");

        foreach ($carrito as $item) {
            $this->Pedido_model->agregar_detalle(array(
                'id_pedido'       => $id_pedido,
                'id_producto'     => $item['id'],
                'talla'           => $item['talla'],
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $item['precio'],
                'unidad'          => isset($item['unidad']) ? $item['unidad'] : NULL,
            ));
        }

        // El código de la transacción es el id del cargo aprobado en Culqi
        $this->Pedido_model->actualizar_pago($id_pedido, 'Pagado', $cargo);

        // Marca de idempotencia + limpieza del carrito y de los datos del pago
        $this->session->set_userdata('pago_id_pedido', $id_pedido);
        $this->session->unset_userdata(array(
            'carrito', 'pago_orden', 'pago_order_number', 'pago_token', 'pago_cargo', 'pago_datos', 'pago_total',
        ));

        // Aviso por correo: si falla, el pedido ya quedó registrado
        $this->load->library('notificador_pedido');
        $this->notificador_pedido->enviar($id_pedido, $datos_pedido, $carrito, $total);

        redirect('pedido/gracias/' . $id_pedido);
    }

    public function gracias($id_pedido) {
        $id_pedido = (int)$id_pedido;
        $pedido    = $this->Pedido_model->get_por_id($id_pedido);
        $detalle   = $this->Pedido_model->get_detalle($id_pedido);

        if (!$pedido) {
            redirect('tienda');
            return;
        }

        $data = array(
            'titulo'        => 'Pedido confirmado - ' . $this->config->item('tienda_nombre'),
            'pedido'        => $pedido,
            'detalle'       => $detalle,
            'carrito_count' => 0,
        );

        $this->load->view('layouts/header', $data);
        $this->load->view('pago/gracias', $data);
        $this->load->view('layouts/footer');
    }

    public function cancelado() {
        $data = array(
            'titulo'        => 'Pago cancelado - ' . $this->config->item('tienda_nombre'),
            'carrito_count' => $this->_carrito_count(),
        );
        $this->load->view('layouts/header', $data);
        $this->load->view('pago/cancelado', $data);
        $this->load->view('layouts/footer');
    }

    // ---- Helpers ----------------------------------------------------------------

    /** Total del carrito de la sesión (precio x cantidad de cada línea). */
    private function _calcular_total($carrito) {
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
        return $total;
    }

    /** Datos del titular capturados en views/tienda/preparar.php. */
    private function _datos_pedido_post() {
        return array(
            'dni'             => trim((string)$this->input->post('dni', TRUE)),
            'nombres'         => trim((string)$this->input->post('nombres', TRUE)),
            'apellidos'       => trim((string)$this->input->post('apellidos', TRUE)),
            'correo'          => trim((string)$this->input->post('correo', TRUE)),
            'celular'         => trim((string)$this->input->post('celular', TRUE)),
            'direccion_envio' => trim((string)$this->input->post('direccion_envio', TRUE)),
            'observaciones'   => trim((string)$this->input->post('observaciones', TRUE)),
        );
    }

    /**
     * Valida los datos del titular antes de gastar una llamada a Culqi.
     * Devuelve '' si todo está completo, o el mensaje de error a mostrar.
     */
    private function _validar_datos_pedido($datos) {
        if (!preg_match('/^\d{8}$/', $datos['dni'])) {
            return 'El DNI debe tener exactamente 8 dígitos numéricos.';
        }
        if (strlen($datos['nombres']) < 2) {
            return 'Ingrese sus nombres.';
        }
        if (strlen($datos['apellidos']) < 2) {
            return 'Ingrese sus apellidos.';
        }
        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            return 'Ingrese un correo electrónico válido.';
        }
        if (!preg_match('/^\d{6,15}$/', $datos['celular'])) {
            return 'Ingrese un celular válido (solo números).';
        }
        if (strlen($datos['direccion_envio']) < 5) {
            return 'Ingrese la dirección de envío completa.';
        }
        return '';
    }

    /** Llave privada de Culqi (index.php la carga desde application/config/.env). */
    private function _culqi_llave_privada() {
        $llave = getenv('CULQI_LLAVE_PRIVADA');
        if ($llave === FALSE || $llave === '') {
            $llave = isset($_SERVER['CULQI_LLAVE_PRIVADA']) ? $_SERVER['CULQI_LLAVE_PRIVADA'] : '';
        }
        return (string)$llave;
    }

    private function _carrito_count() {
        $carrito = $this->session->userdata('carrito');
        if (!is_array($carrito)) return 0;
        return array_sum(array_column($carrito, 'cantidad'));
    }

}
