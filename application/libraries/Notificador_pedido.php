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
 *
 * Tiempos (traza.txt)
 * -------------------
 * El flujo 'correo' deja sus pasos con milisegundos, así se ve qué cuesta el SMTP:
 *
 *   [..] correo | INICIO | pedido=57
 *   [..] correo |  2) vista emails/pedido_nuevo renderizada | +    1.20 ms | acum    3.10 ms | bytes=8421
 *   [..] correo |  4) $mail->send() SMTP                    | + 2890.55 ms | acum 2902.85 ms | OK
 *
 * .env:
 *   MAIL_TIMEOUT=15    segundos de espera por el SMTP. PHPMailer usa 300 por defecto:
 *                      con el puerto 587/465 bloqueado, ese era el tiempo que se
 *                      quedaba colgado el envío (y con él la pantalla del cliente).
 *   MAIL_SMTP_DEBUG=0  0 = apagado; 2 = diálogo con el servidor; 3 = todo. Se escribe
 *                      en traza.txt (PHPMailer oculta las credenciales solo).
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
        traza_inicio('correo', 'pedido=' . $id_pedido);

        $moneda    = $this->CI->config->item('moneda_simbolo');
        $tienda    = $this->CI->config->item('tienda_nombre');
        $destino   = trim((string)$this->CI->config->item('tienda_email'));
        $remitente = trim((string)$this->CI->config->item('email_remitente'));

        if ($remitente === '') {
            $remitente = $destino;
        }

        if ($destino === '' || !filter_var($destino, FILTER_VALIDATE_EMAIL)
            || $remitente === '' || !filter_var($remitente, FILTER_VALIDATE_EMAIL)) {
            traza_fin("correo no enviado: destinatario/remitente inválido ('$destino' / '$remitente')", 'correo');
            log_message('error', 'Notificador_pedido::enviar: destinatario o remitente inválido: ' . $destino . ' / ' . $remitente);
            return FALSE;
        }

        // Fecha y comprobante tal como quedaron guardados en la BD
        $this->CI->load->model('Pedido_model');
        $pedido_bd = $this->CI->Pedido_model->get_por_id($id_pedido);
        traza_paso('1. SELECT del pedido para el correo (get_por_id)', 'encontrado=' . ($pedido_bd ? 'si' : 'no'), 'correo');

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
        traza_paso('2. vista emails/pedido_nuevo renderizada', 'bytes=' . strlen($html), 'correo');

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

            /* Timeout del diálogo SMTP: PHPMailer espera 300 s por defecto, así que con
               el 587/465 bloqueado el envío se quedaba colgado minutos. */
            $timeout = (int)$this->_valor_env('MAIL_TIMEOUT', 15);
            $mail->Timeout = ($timeout > 0) ? $timeout : 15;

            /* Diálogo SMTP en traza.txt (apagado por defecto): MAIL_SMTP_DEBUG=2 o 3. */
            $smtp_debug = (int)$this->_valor_env('MAIL_SMTP_DEBUG', 0);
            if ($smtp_debug > 0) {
                $mail->SMTPDebug   = $smtp_debug;
                $mail->Debugoutput = 'traza_smtp';
            }

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

            traza_paso('3. mensaje armado y SMTP configurado',
                'smtp=smtp.gmail.com:587 timeout=' . $mail->Timeout . 's debug=' . $smtp_debug
                . ' correos=' . count($mail->getToAddresses()), 'correo');

            $mail->send();   // el paso caro: TLS + AUTH + DATA contra Gmail
            traza_paso('4. $mail->send() SMTP', 'OK: Gmail aceptó el mensaje', 'correo');
            traza_fin('aviso enviado del pedido ' . $id_pedido, 'correo');
            return TRUE;
        } catch (Exception $e) {
            traza_paso('4. $mail->send() SMTP', 'FALLÓ: ' . $mail->ErrorInfo, 'correo');
            traza_fin('correo NO enviado del pedido ' . $id_pedido . ' (el pedido igual quedó registrado)', 'correo');
            log_message('error', 'Notificador_pedido: Error PHPMailer: ' . $mail->ErrorInfo);
            return FALSE;
        }
    }

    /**
     * Lee un valor de application/config/.env (index.php lo copia a getenv/$_SERVER).
     * Devuelve $por_defecto si la clave no existe o está vacía.
     */
    protected function _valor_env($clave, $por_defecto = '') {
        $valor = getenv($clave);
        if ($valor === FALSE || $valor === '') {
            $valor = isset($_SERVER[$clave]) ? $_SERVER[$clave] : '';
        }
        return ($valor === '') ? $por_defecto : $valor;
    }
}
