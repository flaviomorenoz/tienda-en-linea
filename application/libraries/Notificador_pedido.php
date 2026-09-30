<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Aviso por correo de "nuevo pedido" para la tienda.
 *
 * Se extrajo del flujo de Pago (cuerpo HTML de la vista emails/pedido_nuevo
 * + envío SMTP con Gmail) para poder reutilizarlo desde otros cobros, por
 * ejemplo Carrito::recibe_token (pasarela Culqi).
 *
 * Uso:
 *   $this->load->library('notificador_pedido');
 *   $this->notificador_pedido->enviar($id_pedido, $datos_pedido, $items, $total);
 *
 * Nunca lanza excepciones hacia el llamador: si el correo falla, se registra
 * en el log y en traza.txt, y devuelve FALSE (el pedido ya quedó registrado).
 */
class Notificador_pedido {

    /** Instancia de CodeIgniter. */
    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
    }

    /**
     * @param int   $id_pedido    ID del pedido recién creado en pedidos_web.
     * @param array $datos_pedido Datos del cliente (nombres, dni, celular, ...).
     * @param array $items        Detalle del pedido (nombre, talla, cantidad, precio).
     * @param float $total        Total cobrado.
     * @return bool TRUE si el correo salió, FALSE en caso contrario.
     */
    public function enviar($id_pedido, $datos_pedido, $items, $total) {
        $moneda    = $this->CI->config->item('moneda_simbolo');
        $tienda    = $this->CI->config->item('tienda_nombre');
        $destino   = trim((string)$this->CI->config->item('tienda_email'));
        $remitente = trim((string)$this->CI->config->item('email_remitente'));

        if ($remitente === '') {
            $remitente = $destino;
        }

        if ($destino === '' || !filter_var($destino, FILTER_VALIDATE_EMAIL)
            || $remitente === '' || !filter_var($remitente, FILTER_VALIDATE_EMAIL)) {
            traza("Notificador_pedido: correo NO enviado, destinatario/remitente inválido ('$destino' / '$remitente'). ID Pedido: " . $id_pedido);
            log_message('error', 'Notificador_pedido::enviar: destinatario o remitente inválido: ' . $destino . ' / ' . $remitente);
            return FALSE;
        }

        // Fecha y comprobante tal como quedaron guardados en la BD
        $this->CI->load->model('Pedido_model');
        $pedido_bd = $this->CI->Pedido_model->get_por_id($id_pedido);

        $archivo = '';
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

        $html = $this->CI->load->view('emails/pedido_nuevo', $data, TRUE);

        // Contraseña de aplicación de Gmail (index.php la carga desde .env)
        $clave_correo = getenv('CLAVE_APLICACION_CORREO');
        if ($clave_correo === FALSE || $clave_correo === '') {
            $clave_correo = isset($_SERVER['CLAVE_APLICACION_CORREO']) ? $_SERVER['CLAVE_APLICACION_CORREO'] : '';
        }

        $mail = new PHPMailer(TRUE); // Excepciones habilitadas
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = TRUE;
            $mail->Username   = $remitente;
            $mail->Password   = $clave_correo;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom($remitente, $tienda);
            $mail->addAddress('bellarosse176@gmail.com');
            $mail->addAddress('flaviomorenoz@hotmail.com');
            if ($destino !== 'bellarosse176@gmail.com' && $destino !== 'flaviomorenoz@hotmail.com') {
                $mail->addAddress($destino);
            }

            $mail->isHTML(TRUE);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Nuevo pedido - ' . $tienda . ' #' . $id_pedido;
            $mail->Body    = $html;
            $mail->AltBody = 'Se ha registrado el pedido #' . $id_pedido . ' en la tienda en línea.';

            $mail->send();
            traza("Notificador_pedido: correo enviado del pedido " . $id_pedido);
            return TRUE;
        } catch (Exception $e) {
            log_message('error', 'Notificador_pedido: Error PHPMailer: ' . $mail->ErrorInfo);
            traza("Notificador_pedido: correo NO enviado del pedido " . $id_pedido . " -> " . $mail->ErrorInfo);
            return FALSE;
        }
    }
}
