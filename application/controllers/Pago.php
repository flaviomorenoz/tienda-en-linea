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
 *
 * Trazabilidad de tiempos (traza.txt)
 * -----------------------------------
 * Cada endpoint mide sus pasos con el helper 'funciones' (traza_inicio/traza_paso/
 * traza_fin) y deja una línea por paso, con el tiempo del paso y el acumulado:
 *
 *   [2026-09-30 18:23:16.139] pago/procesar |  3) INSERT pedidos_web | +  39.10 ms | acum   39.10 ms | id_pedido=57
 *
 * Flujos instrumentados: pago/preparar, pago/crear_orden, pago/procesar,
 * pedido/gracias, carrito/recibe_token y correo (Notificador_pedido).
 *
 * El correo (SMTP de Gmail) NO debe estar en el camino crítico: ver
 * _responder_navegador(), que responde al navegador antes de mandarlo.
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
        traza_inicio('pago/preparar');

        $carrito = $this->session->userdata('carrito') ?: array();
        traza_paso('1. carrito de la sesión', 'items=' . count($carrito), 'pago/preparar');

        if (empty($carrito)) {
            $this->session->set_flashdata('error', 'Tu carrito está vacío.');
            redirect('carrito');
            return;
        }

        $total = $this->_calcular_total($carrito);
        traza_paso('2. total del carrito', 'total=' . $total, 'pago/preparar');

        $data = array(
            'titulo'        => 'Datos de envío y pago - ' . $this->config->item('tienda_nombre'),
            'carrito'       => $carrito,
            'total'         => $total,
            'carrito_count' => array_sum(array_column($carrito, 'cantidad')),
            'order'         => '',   // lo llena crear_orden() -> Culqi settings.order
        );

        $this->load->view('layouts/header', $data);
        traza_paso('3. vista layouts/header', '', 'pago/preparar');
        $this->load->view('tienda/preparar', $data);
        traza_paso('4. vista tienda/preparar (formulario de datos)', '', 'pago/preparar');
        $this->load->view('layouts/footer', $data);
        traza_fin('formulario de datos de envío pintado', 'pago/preparar');
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
        traza_inicio('pago/crear_orden');

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
            traza_fin('pasarela sin llave privada', 'pago/crear_orden');
            $this->output->set_status_header(500);
            echo json_encode(array('ok' => FALSE, 'error' => 'La pasarela no está configurada (falta la llave privada).'));
            return;
        }

        // Datos del titular capturados en el formulario (los exige POST /v2/orders)
        $datos = $this->_datos_pedido_post();
        $error = $this->_validar_datos_pedido($datos);
        if ($error !== '') {
            traza_fin('datos del titular inválidos: ' . $error, 'pago/crear_orden');
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

        traza_paso('1. datos del titular validados + payload de la orden',
            'total=' . $total . ' amount=' . $amount . ' order_number=' . $order_number, 'pago/crear_orden');

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
            traza_paso('2. POST /v2/orders (Culqi)', 'ERROR cURL: ' . $error_curl, 'pago/crear_orden');
            traza_fin('sin orden: no se pudo contactar a Culqi', 'pago/crear_orden');
            log_message('error', 'Pago::crear_orden: error cURL: ' . $error_curl);
            $this->output->set_status_header(502);
            echo json_encode(array('ok' => FALSE, 'error' => 'No se pudo contactar a la pasarela de pago. Intente nuevamente.'));
            return;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, TRUE);
        traza_paso('2. POST /v2/orders (Culqi)', 'HTTP ' . $httpCode, 'pago/crear_orden');
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
            traza_fin('sin orden: Culqi respondió HTTP ' . $httpCode, 'pago/crear_orden');
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

        traza_paso('3. orden y datos del titular guardados en la sesión', 'orden=' . $result['id'], 'pago/crear_orden');

        echo json_encode(array(
            'ok'           => TRUE,
            'order'        => $result['id'],
            'order_number' => $order_number,
            'amount'       => $amount,
        ));
        traza_fin('orden lista para abrir el checkout de Culqi', 'pago/crear_orden');
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
     *
     * Orden de trabajo (cada paso queda medido en traza.txt):
     *
     *   3) INSERT pedidos_web                        ) BD: milisegundos
     *   4) INSERT detalle_pedido                     )
     *   5) UPDATE estado_pago + código de transacción )
     *   6) limpieza de la sesión                     )
     *   7) respuesta al navegador (302, ver _responder_navegador)
     *   8) aviso por correo (SMTP de Gmail)          -> segundos, ya sin cliente esperando
     *
     * El correo se manda DESPUÉS de responderle al navegador (ver _responder_navegador):
     * antes de este cambio el cliente se quedaba en la pantalla de "Compra exitosa"
     * esperando todo lo que tardara Gmail en aceptar el mensaje.
     */
    public function procesar() {
        traza_inicio('pago/procesar');
        $carrito   = $this->session->userdata('carrito') ?: array();
        $cargo     = $this->session->userdata('pago_cargo');
        $datos     = $this->session->userdata('pago_datos');
        $total     = $this->session->userdata('pago_total');
        $id_pagado = $this->session->userdata('pago_id_pedido');

        traza_paso('1. datos del cobro leídos de la sesión',
            'cargo=' . ($cargo ? $cargo : '(vacío)') . ' items=' . count($carrito) . ' total=' . $total
            . ' ya_registrado=' . ($id_pagado ? $id_pagado : 'no'), 'pago/procesar');

        if (!empty($id_pagado)) {
            traza_fin('el pedido ya estaba registrado: redirect directo (idempotente)', 'pago/procesar');
            redirect('pedido/gracias/' . (int)$id_pagado);
            return;
        }

        if (!is_array($datos) || empty($cargo) || empty($carrito)) {
            traza_fin('no hay cobro aprobado pendiente: redirect a carrito', 'pago/procesar');
            $this->session->set_flashdata('error', 'No hay un pago aprobado pendiente de registrar.');
            redirect('carrito');
            return;
        }

        if ($total === FALSE || $total === NULL) {
            $total = $this->_calcular_total($carrito);
        }
        traza_paso('2. total del pedido', 'total=' . $total, 'pago/procesar');

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
        traza_paso('3. INSERT en pedidos_web (Pedido_model->crear)',
            'id_pedido=' . $id_pedido . ' cargo=' . $cargo, 'pago/procesar');

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
        traza_paso('4. INSERT del detalle (1 INSERT + 1 SELECT de unidad por ítem)',
            'items=' . count($carrito), 'pago/procesar');

        // El código de la transacción es el id del cargo aprobado en Culqi
        $this->Pedido_model->actualizar_pago($id_pedido, 'Pagado', $cargo);
        traza_paso('5. UPDATE del pago en pedidos_web', 'estado=Pagado cargo=' . $cargo, 'pago/procesar');

        // Marca de idempotencia + limpieza del carrito y de los datos del pago
        $this->session->set_userdata('pago_id_pedido', $id_pedido);
        $this->session->unset_userdata(array(
            'carrito', 'pago_orden', 'pago_order_number', 'pago_token', 'pago_cargo', 'pago_datos', 'pago_total',
        ));
        traza_paso('6. sesión limpiada (queda la marca de idempotencia)', '', 'pago/procesar');

        /* Hasta aquí el pedido ya está cobrado y registrado: la pantalla de "Compra
           exitosa" no puede quedarse esperando al SMTP de Gmail. Primero se responde al
           navegador (302 + cierre de la sesión) y recién después se manda el aviso. */
        $this->_responder_navegador('pedido/gracias/' . $id_pedido);

        // Aviso por correo: si falla, el pedido ya quedó registrado y el cliente ya vio su pantalla
        traza_paso('8. aviso por correo al cliente y a la tienda (SMTP)', 'correo=' . $datos_pedido['correo'], 'pago/procesar');
        $this->load->library('notificador_pedido');
        $this->notificador_pedido->enviar($id_pedido, $datos_pedido, $carrito, $total);

        traza_fin('pedido ' . $id_pedido . ' registrado; el cliente ya estaba en la pantalla de confirmación', 'pago/procesar');

        /* La respuesta ya salió con Connection: close: se corta aquí para que CodeIgniter
           no intente enviar cabeceras por segunda vez ("headers already sent"). */
        exit;
    }

    public function gracias($id_pedido) {
        traza_inicio('pedido/gracias');

        $id_pedido = (int)$id_pedido;
        $pedido    = $this->Pedido_model->get_por_id($id_pedido);
        traza_paso('1. SELECT pedidos_web (get_por_id)', 'id_pedido=' . $id_pedido, 'pedido/gracias');

        $detalle   = $this->Pedido_model->get_detalle($id_pedido);
        traza_paso('2. SELECT del detalle (get_detalle)', 'lineas=' . count($detalle), 'pedido/gracias');

        if (!$pedido) {
            traza_fin('pedido inexistente: redirect a tienda', 'pedido/gracias');
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
        traza_paso('3. vista layouts/header', '', 'pedido/gracias');
        $this->load->view('pago/gracias', $data);
        traza_paso('4. vista pago/gracias (pantalla "Compra exitosa")', '', 'pedido/gracias');
        $this->load->view('layouts/footer');
        traza_fin('pantalla de confirmación pintada', 'pedido/gracias');
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

    /**
     * Responde al navegador con el redirect y devuelve el control al controller,
     * dejando el proceso vivo para seguir trabajando (mandar el correo).
     *
     * Por qué existe
     * --------------
     * El aviso por correo sale por el SMTP de Gmail: 1-3 s normales y, cuando la red
     * bloquea el 587/465, hasta el Timeout de PHPMailer. Mientras ese envío ocurría
     * ANTES del redirect, el navegador se quedaba esperando en blanco para pintar la
     * pantalla de "Compra exitosa".
     *
     * Qué hace
     * --------
     *  1. session_write_close(): PHP deja el archivo de sesión con lock exclusivo
     *     mientras el script vive. Sin cerrarlo, el navegador que sigue el redirect
     *     (pedido/gracias) se queda esperando en session_start() a que termine el
     *     correo y el arreglo no serviría de nada.
     *  2. Manda el 302 con Content-Length: 0 y Connection: close y vacía los buffers
     *     (fastcgi_finish_request() en php-fpm, flush() en mod_php): desde ese momento
     *     el cliente ya tiene su respuesta y el trabajo que sigue es invisible.
     *  3. ignore_user_abort(TRUE): si el cliente corta, el correo igual termina de salir.
     */
    private function _responder_navegador($ruta) {
        $url = base_url($ruta);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $this->output->set_status_header(302);
        $this->output->set_header('Location: ' . $url, TRUE);
        $this->output->set_header('Content-Length: 0');
        $this->output->set_header('Connection: close');

        $this->output->_display('');

        while (ob_get_level() > 0) {
            @ob_end_flush();
        }

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            @flush();
        }

        traza_paso('7. 302 enviado al navegador (respuesta cerrada al cliente)', $url, 'pago/procesar');

        ignore_user_abort(TRUE);
        @set_time_limit(120);
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
