<?php
defined('BASEPATH') OR exit('No direct script access allowed');
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

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

    public function procesar() { // tipo_pago = 1 (Con tarjeta), 2 (Posiblemente con Yape), 3 (Pago sin confirmar)
        $tipo_pago = (int)$this->input->post('tipo_pago');
        traza("Pago->procesar: tipo_pago=" . $tipo_pago);
        if ($this->input->method() !== 'post') {
            //redirect('checkout');
            //return;
            $nada = "";
        }

        $carrito = $this->session->userdata('carrito') ?: array();
        if (empty($carrito)) {
            redirect('tienda');
            return;
        }

        // Calcular total
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        $this->form_validation->set_rules('dni', 'DNI', 'required|min_length[8]|max_length[15]|numeric');
        $this->form_validation->set_rules('nombres', 'Nombres completos', 'required|min_length[3]|max_length[200]');
        $this->form_validation->set_rules('direccion_envio', 'Dirección de envío', 'required|min_length[5]|max_length[500]');
        $this->form_validation->set_rules('celular', 'Celular', 'required|min_length[9]|max_length[20]');

        if (!$this->form_validation->run()) {
            redirect('carrito');
            return;
        }

        // Guardando archivo del yape
        $archivo_final = null;
        if(isset($_FILES['archivo']) && strlen($_FILES['archivo']['tmp_name'])>0){
            $archivo_tmp    = $_FILES['archivo']['tmp_name'];
            $archivo_name   = $_FILES['archivo']['name'];
            $archivo_size   = $_FILES['archivo']['size'];

            $ar_f        = explode('.', $archivo_name);
            $archivo_ext = strtolower(end($ar_f));

            //if(in_array($archivo_ext, explode('|', $this->digital_file_types)) === false){
            //    $this->data["msg"]      = "Extensión de archivo adjunto no permitida.";
            //    $this->data["rpta_msg"] = "danger";
            //    $validacion = false;
            if($archivo_size > 2097152){
                $this->data["msg"]      = "El archivo adjunto debe pesar como máximo 2 MB.";
                $this->data["rpta_msg"] = "danger";
                $validacion = false;
            }else{
                //$archivo_final = uniqid() . "_" . $archivo_name;
                $archivo_final = "img_" . date("Y-m-d_His") . "_" . $archivo_name;
                move_uploaded_file($archivo_tmp, "../erp-en-linea/uploads/compruebas/" . $archivo_final);
            }
        }
        
        // Crear pedido en BD
        $datos_pedido = array(
            'total'           => $total,
            'direccion_envio' => $this->input->post('direccion_envio', TRUE),
            'celular'         => $this->input->post('celular', TRUE),
            'dni'             => $this->input->post('dni', TRUE),
            'nombres'         => $this->input->post('nombres', TRUE),
            'observaciones'   => $this->input->post('observaciones', TRUE),
            'archivo'         => $archivo_final
        );

        //traza(print_r($datos_pedido,true));

        $id_pedido = $this->Pedido_model->crear($datos_pedido);

        // Agregar items del carrito al detalle
        foreach ($carrito as $item) {
            $this->Pedido_model->agregar_detalle(array(
                'id_pedido'       => $id_pedido,
                'id_producto'     => $item['id'],
                'talla'           => $item['talla'],
                'cantidad'        => $item['cantidad'],
                'precio_unitario' => $item['precio'],
            ));
        }

        // Procesar pago (simulado)
        $datos_pago = array(
            'total'  => $total,
            'nombre' => $datos_pedido['nombres'],
            'dni'    => $datos_pedido['dni'],
        );

        $resultado = $this->Pasarela_model->simular_pago($datos_pago);

        if ($resultado['success']) {
            $this->Pedido_model->actualizar_pago($id_pedido, 'Pagado', $resultado['codigo']);
            $this->session->unset_userdata('carrito');

            // ***** Enviar correo de confirmación ********************
            $cuerpo = $this->_enviar_correo_pedido($id_pedido, $datos_pedido, $carrito, $total);

            $mail = new PHPMailer(true);

            try {

                // Configuración SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'flaviomorenoz@gmail.com';
                $mail->Password   = '<?= $_SERVER["CLAVE_APLICACION_CORREO"] ?>';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Remitente
                $mail->setFrom(
                    'flaviomorenoz@gmail.com',
                    'Bellarosse'
                );

                // Destinatario
                $mail->addAddress('bellarosse176@gmail.com');
                $mail->addAddress('flaviomorenoz@hotmail.com');

                // Contenido
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';

                $mail->Subject = 'Nuevo pedido en Bellarosse';

                //$mail->Body = '<h2>Nuevo pedido recibido</h2><p>Se ha realizado un nuevo pedido en la tienda.</p>';
                $mail->Body = $cuerpo;

                $mail->AltBody = 'Se ha realizado un nuevo pedido en la tienda.';

                // Enviar
                $mail->send();

                $this->session->set_flashdata('success', 'Correo enviado al cliente.');

            } catch (Exception $e) {

                log_message(
                    'error',
                    'Error PHPMailer: ' . $mail->ErrorInfo
                );

                //echo 'No se pudo enviar el correo.';
            }

            // ***** Fin enviar correo de confirmación ****************

            redirect('pedido/gracias/' . $id_pedido);
        } else {
            $this->Pedido_model->actualizar_pago($id_pedido, 'Fallido', '');
            $this->session->set_flashdata('error', 'El pago no pudo procesarse. Intente nuevamente.');
            redirect('pedido/cancelado');
        }
    }

    /**
     * Envía por correo el aviso de pedido nuevo al correo de la tienda.
     *
     * La configuración SMTP vive en application/config/email.php, por eso la
     * librería se carga SIN parámetros: $this->load->library('email').
     *
     * @param int   $id_pedido    ID del pedido recién creado
     * @param array $datos_pedido Datos guardados en pedidos_web
     * @param array $items        Items del carrito (id, nombre, talla, cantidad, precio)
     * @param float $total        Total del pedido
     * @return bool TRUE si el SMTP aceptó el mensaje
     */
    private function _enviar_correo_pedido($id_pedido, $datos_pedido, $items, $total) {
        $moneda    = $this->config->item('moneda_simbolo');
        $tienda    = $this->config->item('tienda_nombre');
        $destino   = trim((string)$this->config->item('tienda_email'));
        $remitente = trim((string)$this->config->item('email_remitente'));

        if ($remitente === '') {
            $remitente = $destino;
        }

        if ($destino === '' || !filter_var($destino, FILTER_VALIDATE_EMAIL)) {
            traza("Pago->procesar: correo NO enviado, destinatario inválido ('$destino'). ID Pedido: " . $id_pedido);
            log_message('error', 'Pago::_enviar_correo_pedido: destinatario inválido: ' . $destino);
            return FALSE;
        }

        // Fecha y comprobante tal como quedaron guardados en la BD
        $pedido_bd = $this->Pedido_model->get_por_id($id_pedido);
        $archivo   = '';
        if ($pedido_bd && !empty($pedido_bd->archivo)) {
            $archivo = $pedido_bd->archivo;
        } elseif (!empty($datos_pedido['archivo'])) {
            $archivo = $datos_pedido['archivo'];
        }

        $archivo_url = '';
        if ($archivo !== '' && !empty($_SERVER['RUTA_DOMINIO_ERP'])) {
            $archivo_url = rtrim($_SERVER['RUTA_DOMINIO_ERP'], '/') . '/uploads/compruebas/' . $archivo;
        }

        $fecha = ($pedido_bd && !empty($pedido_bd->fecha))
            ? date('d/m/Y H:i', strtotime($pedido_bd->fecha))
            : date('d/m/Y H:i');

        $data = array(
            'id_pedido'   => $id_pedido,
            'tienda'      => $tienda,
            'fecha'       => $fecha,
            'pedido'      => $datos_pedido,
            'items'       => $items,
            'total'       => $total,
            'archivo'     => $archivo,
            'archivo_url' => $archivo_url,
            'moneda'      => $moneda,
        );

        $html = $this->load->view('emails/pedido_nuevo', $data, TRUE);

        // Versión en texto plano (mejora la entregabilidad)
        $texto  = "Nuevo pedido #" . $id_pedido . " en " . $tienda . "\n";
        $texto .= "Fecha: " . $fecha . "\n";
        $texto .= "Cliente: " . $datos_pedido['nombres'] . "\n";
        $texto .= "DNI: " . $datos_pedido['dni'] . "\n";
        $texto .= "Celular: " . $datos_pedido['celular'] . "\n";
        $texto .= "Dirección de envío: " . $datos_pedido['direccion_envio'] . "\n\n";
        foreach ($items as $item) {
            $texto .= "- " . $item['nombre'] . " | Talla: " . $item['talla']
                   . " | Cant: " . $item['cantidad']
                   . " | " . $moneda . " " . number_format($item['precio'] * $item['cantidad'], 2) . "\n";
        }
        $texto .= "\nTotal: " . $moneda . " " . number_format($total, 2) . "\n";
        if (!empty($datos_pedido['observaciones'])) {
            $texto .= "Observaciones: " . $datos_pedido['observaciones'] . "\n";
        }
        if ($archivo_url !== '') {
            $texto .= "Comprobante de pago: " . $archivo_url . "\n";
        }
        /*
        $this->load->library('email');

        $this->email->from($remitente, $tienda);
        $this->email->to($destino);
        $this->email->subject('Nuevo pedido #' . $id_pedido . ' - ' . $tienda);
        $this->email->message($html);
        $this->email->set_alt_message($texto);

        if ($this->email->send()) {
            traza("Pago->procesar: correo enviado. ID Pedido: " . $id_pedido . " destino=" . $destino);
            return TRUE;
        }

        traza("Pago->procesar: correo NO enviado. ID Pedido: " . $id_pedido . " destino=" . $destino);
        log_message('error', $this->email->print_debugger());
        $this->session->set_flashdata('error', 'No se pudo enviar el correo de aviso del pedido.');
        return FALSE;
        */
        return $html;
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

    private function _carrito_count() {
        $carrito = $this->session->userdata('carrito');
        if (!is_array($carrito)) return 0;
        return array_sum(array_column($carrito, 'cantidad'));
    }
}