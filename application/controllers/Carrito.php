<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);

class Carrito extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Producto_model');
        $this->load->model('Pedido_model');
        //$this->load->model('Chat_model');
    }

    public function ver() {
        
        $carrito = $this->session->userdata('carrito') ?: array();
        $total   = $this->_calcular_total($carrito);

        $data = array(
            'titulo'        => 'Mi Carrito - ' . $this->config->item('tienda_nombre'),
            'carrito'       => $carrito,
            'total'         => $total,
            'carrito_count' => array_sum(array_column($carrito, 'cantidad')),
        );
        
        $this->load->view('layouts/header', $data);
        $this->load->view('tienda/carrito', $data);
        $this->load->view('layouts/footer');
    }

    public function agregar() {
        
        traza("Carrito->agregar");
        if ($this->input->method() !== 'post') {
            redirect('tienda');
            return;
        }

        $id_producto    = (int)$this->input->post('id_producto', TRUE);
    
        $talla          = $this->input->post('talla', TRUE);
    
        $cantidad       = max(1, (int)$this->input->post('cantidad', TRUE));

        $producto       = $this->Producto_model->get_por_id($id_producto);

        $unidad         = $this->input->post('select_unidad');

        $hdn_precio     = $this->input->post('hdn_precio');

        if (!$producto || !$producto->tiene_precio) {
            $this->session->set_flashdata('error', 'Este producto no puede agregarse al carrito.');
            redirect('tienda/producto/' . $id_producto);
            return;
        }

        $carrito = $this->session->userdata('carrito') ?: array();

        // Clave única por producto + talla
        $key = $id_producto . '_' . $talla;

        if (isset($carrito[$key])) {
            $carrito[$key]['cantidad'] += $cantidad;
            traza("Carrito->agregar: producto ya en carrito, nueva cantidad=" . $carrito[$key]['cantidad']);
        } else {
            $carrito[$key] = array(
                'id'        => $id_producto,
                'nombre'    => $producto->nombre,
                'precio'    => (float)$hdn_precio,
                'talla'     => $talla,
                'cantidad'  => $cantidad,
                'imagen'    => $producto->imagen_url,
                'categoria' => $producto->categoria,
                'unidad'    => $unidad
            );
            traza("Carrito->agregar: producto agregado al carrito, cantidad=" . $cantidad);
            traza(print_r($carrito, true));
        }

        // LO GUARDA EN VARIABLE DE SESSION
        $this->session->set_userdata('carrito', $carrito);

        // Responder con JSON si es AJAX, o redirigir
        if ($this->input->is_ajax_request()) {
            $total_items = array_sum(array_column($carrito, 'cantidad'));
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success'     => TRUE,
                    'total_items' => $total_items,
                    'mensaje'     => 'Producto agregado al carrito',
                )));
        } else {
            $this->session->set_flashdata('success', 'Producto agregado al carrito.');
            redirect('carrito');
        }
    }

    public function quitar($key_encoded) {
        $key     = urldecode($key_encoded);
        $carrito = $this->session->userdata('carrito') ?: array();
        unset($carrito[$key]);
        $this->session->set_userdata('carrito', $carrito);
        $this->session->set_flashdata('success', 'Producto eliminado del carrito.');
        redirect('carrito');
    }

    public function actualizar() {
        traza("Carrito->actualizar");
        if ($this->input->method() !== 'post') {
            redirect('carrito');
            return;
        }

        /* Las claves del carrito incluyen la talla ("55_Única") y CI3 convierte en 0 las claves
           POST con caracteres no permitidos (Input::_clean_input_keys() -> FALSE -> 0), por eso
           la clave viaja como VALOR (clave[i]) y se empareja por índice con cantidad[i]. */
        $cantidades = $this->input->post('cantidad', TRUE);
        $claves     = $this->input->post('clave', TRUE);
        $carrito    = $this->session->userdata('carrito') ?: array();

        if (is_array($cantidades)) {
            foreach ($cantidades as $indice => $cant) {
                $key = (is_array($claves) && isset($claves[$indice])) ? (string) $claves[$indice] : (string) $indice;

                // Solo claves que ya existen en el carrito: no se puede inyectar una clave nueva
                if ($key === '' || !isset($carrito[$key])) {
                    continue;
                }

                $cant = (int)$cant;

                if ($cant <= 0) {
                    unset($carrito[$key]);
                    continue;
                }

                if ($cant > 99) {
                    $cant = 99;   // mismo tope que el input de la vista (max="99")
                }

                $carrito[$key]['cantidad'] = $cant;
            }
        }

        $this->session->set_userdata('carrito', $carrito);
        $this->session->set_flashdata('success', 'Carrito actualizado.');
        redirect('carrito');
    }

    /**
     * Autocompleta nombres/celular del checkout con los datos que el
     * cliente ya dio en su conversación de chat (chat_conversaciones).
     */
    public function actualizar_datos_cliente($id_conversacion) {
        /*if ($this->input->method() !== 'post') {
            show_404();
            return;
        }*/
        /*
        $conversacion = $this->Chat_model->obtener_por_id((int)$id_conversacion);
        if (!$conversacion) {
            echo json_encode(array('ok' => false, 'error' => 'Conversación no encontrada.'));
            return;
        }

        echo json_encode(array(
            'ok'              => true,
            'nombre_cliente'  => $conversacion->nombre_cliente,
            'celular_cliente' => $conversacion->celular_cliente,
            'imagenes'        => $conversacion->imagenes,
        ));
        */
        $hola = "Hola";
    }

    public function vaciar() {
        $this->session->unset_userdata('carrito');
        $this->session->set_flashdata('success', 'Carrito vaciado.');
        redirect('tienda');
    }

    private function _calcular_total($carrito) {
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
        return $total;
    }

    /**
     * Recibe el token generado por Culqi Checkout (JS) y CREA EL CARGO
     * (POST /v2/charges). Docs: https://apidocs.culqi.com/#tag/Cargos/operation/crear-cargo
     *
     * Flujo:
     *   1. Valida el token y los datos del titular enviados por POST.
     *   2. Cobra en Culqi el total real del carrito (en céntimos).
     *   3. Si el cargo queda "pagado": guarda en sesión el cargo y los datos del
     *      titular (pago_cargo / pago_datos / pago_total) y devuelve la URL de
     *      pago/procesar, que es quien registra el pedido.
     *
     * El pedido NO se registra aquí: así una sola ruta (Pago::procesar) escribe en
     * pedidos_web y el flujo queda orden -> token -> cargo -> pedido.
     *
     * Responde SIEMPRE JSON (el JS hace response.json()):
     *   OK    -> {"ok":true, "cargo":"chr_...", "redirect":".../pago/procesar"}
     *   Error -> {"ok":false, "error":"mensaje para mostrar al cliente"}
     */
    public function recibe_token(){
        $this->output->set_content_type('application/json');
        traza_inicio('carrito/recibe_token');

        // 1. Validaciones de entrada -----------------------------------------
        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(array('ok' => false, 'error' => 'Método no permitido.'));
            return;
        }

        $token = trim((string)$this->input->post('token'));
        if ($token === '') {
            traza_fin('token vacío: no se cobra', 'carrito/recibe_token');
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => false, 'error' => 'No se recibió el token del pago.'));
            return;
        }

        // El checkout devuelve el token del pago (tkn_/ype_) y, en los medios que se
        // resuelven con órdenes, el id de esa orden (ord_): Culqi valida el source_id.
        if (strpos($token, 'tkn_') !== 0 && strpos($token, 'ype_') !== 0 && strpos($token, 'ord_') !== 0) {
            traza_fin('token inesperado (' . substr($token, 0, 12) . '): no se cobra', 'carrito/recibe_token');
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => false, 'error' => 'No se recibió un token de pago válido.'));
            return;
        }

        $carrito = $this->session->userdata('carrito') ?: array();
        if (empty($carrito)) {
            traza_fin('carrito vacío: no se cobra', 'carrito/recibe_token');
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => false, 'error' => 'Tu carrito está vacío.'));
            return;
        }

        // Datos del titular capturados en el formulario de datos de envío (pago/preparar)
        $datos_pedido = array(
            'dni'             => trim((string)$this->input->post('dni', TRUE)),
            'nombres'         => trim((string)$this->input->post('nombres', TRUE)),
            'apellidos'       => trim((string)$this->input->post('apellidos', TRUE)),
            'correo'          => trim((string)$this->input->post('correo', TRUE)),
            'direccion_envio' => trim((string)$this->input->post('direccion_envio', TRUE)),
            'celular'         => trim((string)$this->input->post('celular', TRUE)),
            'observaciones'   => trim((string)$this->input->post('observaciones', TRUE)),
            'archivo'         => NULL,   // el pago es por pasarela: no hay comprobante que subir
        );

        if (!preg_match('/^\d{8}$/', $datos_pedido['dni'])) {
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => false, 'error' => 'El DNI debe tener exactamente 8 dígitos numéricos.'));
            return;
        }

        if ($datos_pedido['nombres'] === '' || $datos_pedido['apellidos'] === ''
            || $datos_pedido['direccion_envio'] === '' || $datos_pedido['celular'] === '') {
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => false, 'error' => 'Complete los datos de envío (nombres, apellidos, dirección y celular).'));
            return;
        }

        if (!filter_var($datos_pedido['correo'], FILTER_VALIDATE_EMAIL)) {
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => false, 'error' => 'Ingrese un correo electrónico válido.'));
            return;
        }

        // Comprobante opcional (mismo tratamiento que Pago->procesar)
        /*
        if (isset($_FILES['archivo']) && strlen($_FILES['archivo']['tmp_name']) > 0) {
            $archivo_tmp  = $_FILES['archivo']['tmp_name'];
            $archivo_name = $_FILES['archivo']['name'];

            if ($_FILES['archivo']['size'] > 2097152) {
                $this->output->set_status_header(400);
                echo json_encode(array('ok' => false, 'error' => 'El archivo adjunto debe pesar como máximo 2 MB.'));
                return;
            }

            $archivo_final = "img_" . date("Y-m-d_His") . "_" . $archivo_name;
            if (!move_uploaded_file($archivo_tmp, "../erp-en-linea/uploads/compruebas/" . $archivo_final)) {
                $archivo_final = NULL;
            }
            $datos_pedido['archivo'] = $archivo_final;
        }*/

        // Llave privada de Culqi (index.php la carga desde application/config/.env)
        $SECRET_KEY = getenv('CULQI_LLAVE_PRIVADA');
        if ($SECRET_KEY === FALSE || $SECRET_KEY === '') {
            $SECRET_KEY = isset($_SERVER['CULQI_LLAVE_PRIVADA']) ? $_SERVER['CULQI_LLAVE_PRIVADA'] : '';
        }
        if ($SECRET_KEY === '') {
            traza_fin('falta CULQI_LLAVE_PRIVADA: no se cobra', 'carrito/recibe_token');
            $this->output->set_status_header(500);
            echo json_encode(array('ok' => false, 'error' => 'La pasarela no está configurada (falta la llave privada).'));
            return;
        }

        /* El monto sale del total guardado en sesión al crear la orden (pago_total) y, si no
           está, del carrito de la sesión: el navegador nunca envía el monto a cobrar. */
        $total_sesion = $this->session->userdata('pago_total');

        if ($total_sesion === FALSE || $total_sesion === NULL) {
            $total = $this->_calcular_total($carrito);
        } else {
            $total = (float)$total_sesion;
        }

        $amount = (int)round($total * 100); // Culqi trabaja en céntimos

        // Correo del cargo: el del cliente; si no es válido, el de la tienda
        $email = $datos_pedido['correo'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = trim((string)$this->config->item('tienda_email'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = 'flaviomorenoz@gmail.com';
        }

        /* La orden con la que se abrió el checkout (ver Pago::crear_orden) y su número viajan
           en los metadatos del cargo: así el pago se puede cruzar con el pedido en Culqi. */
        $metadata = array('documentNumber' => $datos_pedido['dni']);

        $orden        = (string)$this->session->userdata('pago_orden');
        $order_number = (string)$this->session->userdata('pago_order_number');

        if ($orden !== '')        $metadata['order']        = $orden;
        if ($order_number !== '') $metadata['order_number'] = $order_number;

        // 2. Construir el payload del cargo ----------------------------------
        $payload = array(
            "amount"          => $amount,
            "currency_code"   => "PEN",
            "email"           => $email,
            "source_id"       => $token,
            "capture"         => true,
            "description"     => "Pedido tienda en línea - " . $this->config->item('tienda_nombre'),
            "antifraud_details" => array(
                "address"        => $datos_pedido['direccion_envio'],
                "address_city"   => "Lima",
                "country_code"   => "PE",
                "first_name"     => $datos_pedido['nombres'],
                "last_name"      => $datos_pedido['apellidos'],
                "phone_number"   => $datos_pedido['celular']
            ),
            "metadata" => $metadata
        );

        traza_paso('1. token y datos del titular validados + payload del cargo',
            'total=' . $total . ' amount=' . $amount . ' email=' . $email . ' token=' . substr($token, 0, 12) . '...',
            'carrito/recibe_token');

        // 3. Crear el cargo en Culqi -----------------------------------------
        $ch = curl_init('https://api.culqi.com/v2/charges');

        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => array(
                'Authorization: Bearer ' . $SECRET_KEY,
                'Content-Type: application/json',
                'Accept: application/json'
            ),
            CURLOPT_TIMEOUT        => 30,
        ));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_curl = curl_error($ch);
            curl_close($ch);
            traza_paso('2. POST /v2/charges (Culqi)', 'ERROR cURL: ' . $error_curl, 'carrito/recibe_token');
            traza_fin('sin cobro: no se pudo contactar a Culqi', 'carrito/recibe_token');
            log_message('error', 'Carrito::recibe_token: error cURL: ' . $error_curl);
            $this->output->set_status_header(502);
            echo json_encode(array('ok' => false, 'error' => 'No se pudo contactar a la pasarela de pago. Intente nuevamente.'));
            return;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);
        traza_paso('2. POST /v2/charges (Culqi)', 'HTTP ' . $httpCode, 'carrito/recibe_token');
        traza("Carrito->recibe_token: HTTP $httpCode -> " . print_r($result, true));

        // 4. Evaluar la respuesta de Culqi -----------------------------------
        $es_error = !is_array($result)
                 || $httpCode >= 400
                 || (isset($result['object']) && $result['object'] === 'error');

        if ($es_error) {
            $mensaje = 'No se pudo procesar el pago.';
            if (is_array($result)) {
                if (!empty($result['user_message'])) {
                    $mensaje = $result['user_message'];
                } elseif (!empty($result['merchant_message'])) {
                    $mensaje = $result['merchant_message'];
                }
            }
            traza_fin('cargo rechazado (HTTP ' . $httpCode . '): el cliente sigue en el checkout', 'carrito/recibe_token');
            log_message('error', 'Carrito::recibe_token: cargo rechazado (HTTP ' . $httpCode . '): ' . $response);
            echo json_encode(array(
                'ok'      => false,
                'error'   => $mensaje,
                'detalle' => is_array($result) && isset($result['code']) ? $result['code'] : '',
            ));
            return;
        }

        // Culqi marca la venta aprobada con outcome.type "venta_exitosa" y su
        // código de autorización AUT.... (el campo "paid" puede venir vacío
        // incluso en cobros aprobados), por eso se acepta cualquiera de las señales.
        $outcome = (isset($result['outcome']) && is_array($result['outcome'])) ? $result['outcome'] : array();
        $tipo    = isset($outcome['type']) ? strtolower((string)$outcome['type']) : '';
        $codigo  = isset($outcome['code']) ? strtoupper((string)$outcome['code']) : '';

        $pagado = !empty($result['paid'])
               || $tipo === 'venta_exitosa'
               || strpos($codigo, 'AUT') === 0;

        if (!$pagado) {
            $motivo = 'El pago no fue aprobado. Intente nuevamente.';
            if (!empty($outcome['user_message'])) {
                $motivo = $outcome['user_message'];
            } elseif (!empty($outcome['merchant_message'])) {
                $motivo = $outcome['merchant_message'];
            }
            traza_fin('cargo no pagado (' . $tipo . '/' . $codigo . '): el cliente sigue en el checkout', 'carrito/recibe_token');
            log_message('error', 'Carrito::recibe_token: cargo no pagado (' . $tipo . '/' . $codigo . '): ' . $response);
            echo json_encode(array(
                'ok'    => false,
                'error' => $motivo,
            ));
            return;
        }

        traza_paso('3. cargo aprobado por Culqi', $result['id'] . ' (' . $tipo . '/' . $codigo . ')', 'carrito/recibe_token');

        /* El pedido NO se registra aquí: se guarda el cargo aprobado y los datos del titular
           en sesión, y pago/procesar() (la redirección final) escribe en pedidos_web. Así el
           pedido solo se crea cuando el cobro ya está aprobado. */
        $this->session->set_userdata(array(
            'pago_token' => $token,
            'pago_cargo' => $result['id'],
            'pago_datos' => $datos_pedido,
            'pago_total' => $total,
        ));

        traza_paso('4. cargo y datos del titular guardados en la sesión',
            'cargo=' . $result['id'] . ' -> pendiente de registrar en pago/procesar', 'carrito/recibe_token');
        traza_fin('el navegador pasa a pago/procesar', 'carrito/recibe_token');

        echo json_encode(array(
            'ok'       => true,
            'cargo'    => $result['id'],
            'redirect' => base_url('pago/procesar'),
        ));
    }

}
