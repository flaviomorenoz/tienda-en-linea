<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
     * Recibe el token generado por Culqi Checkout (JS) y crea el cargo.
     * Docs: https://apidocs.culqi.com/#tag/Cargos/operation/crear-cargo
     *
     * Flujo:
     *   1. Valida el token y los datos de envío enviados por POST.
     *   2. Cobra en Culqi el total real del carrito (en céntimos).
     *   3. Si el cargo queda "pagado": registra pedidos_web + detalle_pedido,
     *      vacía el carrito, avisa por correo y devuelve la URL de agradecimiento.
     *
     * Responde SIEMPRE JSON (el JS hace response.json()):
     *   OK    -> {"ok":true,  "id_pedido":N, "redirect":".../pedido/gracias/N"}
     *   Error -> {"ok":false, "error":"mensaje para mostrar al cliente"}
     */
    public function recibe_token(){
        $this->output->set_content_type('application/json');
        traza("Carrito->recibe_token: inicio");

        // 1. Validaciones de entrada -----------------------------------------
        if ($this->input->method() !== 'post') {
            $this->output->set_status_header(405);
            echo json_encode(array('ok' => false, 'error' => 'Método no permitido.'));
            return;
        }

        $token = trim((string)$this->input->post('token'));
        if ($token === '') {
            traza("Carrito->recibe_token: token vacío");
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => false, 'error' => 'No se recibió el token de la tarjeta.'));
            return;
        }

        $carrito = $this->session->userdata('carrito') ?: array();
        if (empty($carrito)) {
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => false, 'error' => 'Tu carrito está vacío.'));
            return;
        }

        // Datos del cliente capturados en el formulario de checkout del carrito
        $datos_pedido = array(
            'dni'             => trim((string)$this->input->post('dni', TRUE)),
            'nombres'         => trim((string)$this->input->post('nombres', TRUE)),
            'direccion_envio' => trim((string)$this->input->post('direccion_envio', TRUE)),
            'celular'         => trim((string)$this->input->post('celular', TRUE)),
            'observaciones'   => trim((string)$this->input->post('observaciones', TRUE)),
            'archivo'         => NULL,
        );

        if (!preg_match('/^\d{8}$/', $datos_pedido['dni'])) {
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => false, 'error' => 'El DNI debe tener exactamente 8 dígitos numéricos.'));
            return;
        }

        if ($datos_pedido['nombres'] === '' || $datos_pedido['direccion_envio'] === '' || $datos_pedido['celular'] === '') {
            $this->output->set_status_header(400);
            echo json_encode(array('ok' => false, 'error' => 'Complete los datos de envío (nombres, dirección y celular).'));
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
            traza("Carrito->recibe_token: falta CULQI_LLAVE_PRIVADA");
            $this->output->set_status_header(500);
            echo json_encode(array('ok' => false, 'error' => 'La pasarela no está configurada (falta la llave privada).'));
            return;
        }

        $total  = $this->_calcular_total($carrito);
        $amount = (int)round($total * 100); // Culqi trabaja en céntimos

        $email = trim((string)$this->config->item('tienda_email'));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = 'flaviomorenoz@gmail.com';
        }

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
                "last_name"      => $datos_pedido['nombres'],
                "phone_number"   => $datos_pedido['celular']
            ),
            "metadata" => array(
                "documentNumber" => $datos_pedido['dni']
            )
        );

        traza("Carrito->recibe_token: total=$total amount=$amount email=$email");

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
            traza("Carrito->recibe_token: error cURL -> " . $error_curl);
            log_message('error', 'Carrito::recibe_token: error cURL: ' . $error_curl);
            $this->output->set_status_header(502);
            echo json_encode(array('ok' => false, 'error' => 'No se pudo contactar a la pasarela de pago. Intente nuevamente.'));
            return;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);
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
            $motivo = 'El pago no fue aprobado. Intente con otra tarjeta.';
            if (!empty($outcome['user_message'])) {
                $motivo = $outcome['user_message'];
            } elseif (!empty($outcome['merchant_message'])) {
                $motivo = $outcome['merchant_message'];
            }
            log_message('error', 'Carrito::recibe_token: cargo no pagado (' . $tipo . '/' . $codigo . '): ' . $response);
            echo json_encode(array(
                'ok'    => false,
                'error' => $motivo,
            ));
            return;
        }

        traza("Carrito->recibe_token: cargo aprobado " . $result['id'] . " ($tipo/$codigo)");

        // 5. Registrar el pedido pagado --------------------------------------
        $datos_pedido['total'] = $total;

        $id_pedido = $this->Pedido_model->crear($datos_pedido);

        foreach ($carrito as $item) {
            $this->Pedido_model->agregar_detalle(array(
                'id_pedido'       => $id_pedido,
                'id_producto'     => $item['id'],
                'talla'           => $item['talla'],
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $item['precio'],
                'unidad'          => $item['unidad']
            ));
        }

        $this->Pedido_model->actualizar_pago($id_pedido, 'Pagado', $result['id']);
        
        $this->session->unset_userdata('carrito');
        
        traza("Carrito->recibe_token: pedido $id_pedido pagado con cargo " . $result['id']);

        // Aviso por correo: si falla, el pedido ya quedó registrado y el JSON sale igual
        $this->load->library('notificador_pedido');
        
        $this->notificador_pedido->enviar($id_pedido, $datos_pedido, $carrito, $total);

        echo json_encode(array(
            'ok'        => true,
            'id_pedido' => $id_pedido,
            'mensaje'   => 'Pago procesado correctamente.',
            'redirect'  => base_url('pedido/gracias/' . $id_pedido),
        ));
    }
}
