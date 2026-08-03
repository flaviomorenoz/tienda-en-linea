<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Módulo independiente: asistente IA (DeepSeek) que responde
 * automáticamente en el WhatsApp del negocio, vía Meta Cloud API.
 */
class Wts extends CI_Controller {

    private $deepseek_url   = 'https://api.deepseek.com/chat/completions';
    private $deepseek_model = 'deepseek-chat';
    private $graph_url      = 'https://graph.facebook.com/v20.0/';
    private $historial_limite = 12;

    public function __construct() {
        parent::__construct();
        $this->load->model('Asistente_model');
        $this->load->model('wts_model');
    }

    /**
     * Webhook único de Meta Cloud API: GET para la verificación
     * inicial, POST para los mensajes entrantes.
     */
    public function webhook() {
        if ($this->input->method() === 'get') {
            $this->_verificar_webhook();
            return;
        }

        $this->_recibir_mensaje();
    }

    private function _verificar_webhook() {
        // PHP convierte automáticamente los puntos de "hub.mode" etc.
        // a guiones bajos en $_GET, por eso se leen así.
        $modo      = $this->input->get('hub_mode');
        $token     = $this->input->get('hub_verify_token');
        $challenge = $this->input->get('hub_challenge');

        if ($modo === 'subscribe' && $token !== false && hash_equals((string)getenv('WHATSAPP_VERIFY_TOKEN'), (string)$token)) {
            echo $challenge;
            return;
        }

        $this->output->set_status_header(403);
        echo 'Verificación fallida.';
    }

    private function _recibir_mensaje() {
        $raw = file_get_contents('php://input');

        if (!$this->_firma_valida($raw)) {
            log_message('error', 'WhatsApp webhook: firma inválida.');
            $this->output->set_status_header(403);
            return;
        }

        $data = json_decode($raw, true);
        $valor = $data['entry'][0]['changes'][0]['value'] ?? null;
        $mensaje = $valor['messages'][0] ?? null;

        // Meta también manda webhooks de "statuses" (entregado/leído);
        // no traen "messages" y no hay nada que responder.
        if (!$mensaje) {
            echo 'EVENT_RECEIVED';
            return;
        }

        $telefono      = $mensaje['from'];
        $wa_message_id = $mensaje['id'];

        // Meta reintenta el webhook si no responde rápido; evita
        // procesar (y volver a contestar) el mismo mensaje dos veces.
        if ($this->wts_model->mensaje_existe($wa_message_id)) {
            echo 'EVENT_RECEIVED';
            return;
        }

        if ($mensaje['type'] !== 'text' || empty($mensaje['text']['body'])) {
            $this->wts_model->guardar_mensaje($telefono, 'user', '[mensaje no soportado: ' . $mensaje['type'] . ']', $wa_message_id);
            $this->_responder($telefono, 'Por el momento solo puedo leer mensajes de texto. Cuéntame en palabras qué estás buscando 🙂');
            echo 'EVENT_RECEIVED';
            return;
        }

        $texto_cliente = trim($mensaje['text']['body']);
        $this->wts_model->guardar_mensaje($telefono, 'user', $texto_cliente, $wa_message_id);

        $historial = $this->wts_model->historial_reciente($telefono, $this->historial_limite);

        $mensajes = array(array('role' => 'system', 'content' => $this->_system_prompt()));
        foreach ($historial as $h) {
            $mensajes[] = array('role' => $h->rol, 'content' => $h->mensaje);
        }

        $api_key = getenv('DEEPSEEK_API_KEY');

        if (empty($api_key)) {
            log_message('error', 'DEEPSEEK_API_KEY no configurada en application/config/.env');
            echo 'EVENT_RECEIVED';
            return;
        }

        $respuesta = $this->_llamar_deepseek($api_key, $mensajes);

        if ($respuesta === false) {
            $respuesta = 'Disculpa, tuve un problema para responderte. Intenta de nuevo en unos minutos.';
        }

        $this->_responder($telefono, $respuesta);

        echo 'EVENT_RECEIVED';
    }

    private function _responder($telefono, $texto) {
        $wa_message_id = $this->_enviar_mensaje_wts($telefono, $texto);
        $this->wts_model->guardar_mensaje($telefono, 'assistant', $texto, $wa_message_id);
    }

    private function _firma_valida($raw) {
        $secreto = getenv('WHATSAPP_APP_SECRET');

        // Sin secreto configurado no se puede validar; se deja pasar
        // para no bloquear la puesta en marcha inicial del webhook.
        if (empty($secreto)) {
            return true;
        }

        $firma_recibida = $this->input->get_request_header('X-Hub-Signature-256');

        if (empty($firma_recibida) || strpos($firma_recibida, 'sha256=') !== 0) {
            return false;
        }

        $firma_calculada = 'sha256=' . hash_hmac('sha256', $raw, $secreto);

        return hash_equals($firma_calculada, $firma_recibida);
    }

    private function _system_prompt() {
        $nombre_tienda = $this->config->item('tienda_nombre');
        $catalogo      = $this->Asistente_model->get_catalogo_contexto();

        $prompt = "Eres el asistente virtual por WhatsApp de {$nombre_tienda}, una tienda que vende " .
            "principalmente lencería y prendas de vestir para mujeres de todas las edades, y también " .
            "boxers/calzoncillos para varones.\n\n" .
            "Responde preguntas sobre las prendas (tallas, colores, precios, categorías) usando SOLO la " .
            "información del catálogo entregado abajo, en formato CSV (separador \";\").\n\n" .
            "Reglas:\n" .
            "- Responde siempre en español, breve y amable (esto es un chat de WhatsApp, evita párrafos largos).\n" .
            "- No uses formato markdown (nada de **negritas** ni #), este chat solo muestra texto plano.\n" .
            "- Los precios están en soles (S/).\n" .
            "- La columna \"sexo\" indica: M = mujer, H = hombre.\n" .
            "- La columna \"tallas_disponibles\" lista las tallas con stock; si está vacía, no hay tallas registradas.\n" .
            "- Si preguntan por algo que no está en el catálogo, dilo con honestidad.\n" .
            "- Si el cliente ya decidió qué prenda quiere comprar, indícale que en breve alguien del equipo le " .
            "confirma el pedido y la forma de pago por este mismo WhatsApp.\n" .
            "- Evita frases como \"lamento informarte\".\n\n";

        $prompt .= ($catalogo !== '')
            ? "Catálogo de productos:\n{$catalogo}"
            : "El catálogo aún no tiene productos cargados. Indícalo con honestidad.";

        return $prompt;
    }

    private function _llamar_deepseek($api_key, $mensajes) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $this->deepseek_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $api_key,
            ),
            CURLOPT_POSTFIELDS => json_encode(array(
                'model'       => $this->deepseek_model,
                'messages'    => $mensajes,
                'temperature' => 0.3,
            )),
        ));

        $response  = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_err  = curl_error($curl);
        curl_close($curl);

        if ($response === false || $http_code !== 200) {
            log_message('error', 'DeepSeek API error (' . $http_code . '): ' . ($curl_err ?: $response));
            return false;
        }

        $data = json_decode($response, true);

        return isset($data['choices'][0]['message']['content'])
            ? trim($data['choices'][0]['message']['content'])
            : false;
    }

    private function _enviar_mensaje_whatsapp($telefono, $texto) {
        $phone_number_id = getenv('WHATSAPP_PHONE_NUMBER_ID');
        $access_token    = getenv('WHATSAPP_ACCESS_TOKEN');

        if (empty($phone_number_id) || empty($access_token)) {
            log_message('error', 'WHATSAPP_PHONE_NUMBER_ID / WHATSAPP_ACCESS_TOKEN no configurados en application/config/.env');
            return null;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $this->graph_url . $phone_number_id . '/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token,
            ),
            CURLOPT_POSTFIELDS => json_encode(array(
                'messaging_product' => 'whatsapp',
                'to'                => $telefono,
                'type'              => 'text',
                'text'              => array('body' => $texto),
            )),
        ));

        $response  = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_err  = curl_error($curl);
        curl_close($curl);

        if ($response === false || $http_code !== 200) {
            log_message('error', 'WhatsApp send error (' . $http_code . '): ' . ($curl_err ?: $response));
            return null;
        }

        $data = json_decode($response, true);

        return $data['messages'][0]['id'] ?? null;
    }

    // ------------------------------------------------------------
    // Panel de administración (solo lectura)
    // ------------------------------------------------------------

    public function historial() {
        $this->_check_admin();

        $data = array(
            'titulo'        => 'Conversaciones via WhatsApp',
            'admin_nombre'  => $this->session->userdata('admin_nombre'),
            'carrito_count' => 0,
        );

        $this->load->view('layouts/header1', $data);
        $this->load->view('admin/wts_historial', $data);
        $this->load->view('layouts/footer1', $data);
    }

    public function historial_json() {
        $this->_check_admin();

        $conversaciones = $this->wts_model->conversaciones_recientes();

        //$result = $conversaciones->result_array();

        //$ar_campos = array("id","fecha", "message_sid", "nombre", "origen", "destino", "tipo", "mensaje", "rol");
        $ar_campos = array("id", "fecha", "telefono_origen", "nombre", "cant", "opciones");

        echo $this->fm->json_datatable($ar_campos, $conversaciones);

        /*
        $rows = array();
        foreach ($conversaciones as $c) {
            $rows[] = array(
                'telefono'        => $c->telefono,
                'total_mensajes'  => (int)$c->total_mensajes,
                'primer_mensaje'  => $c->primer_mensaje,
                'ultimo_mensaje'  => $c->ultimo_mensaje,
            );
        }

        $rows = array();
        foreach($conversaciones as $c){
            $rows[] = array(
                'id'    => $c->id,
                'fecha' => $c->fecha,
            );
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array('data' => $rows)));
        */

    }

    public function mensajes($telefono) {
        $this->_check_admin();

        $mensajes = $this->wts_model->mensajes_por_telefono($telefono);

        $rows = array();
        foreach ($mensajes as $m) {
            $rows[] = array(
                'id'     => (int)$m->id,
                'rol'    => $m->rol,
                'texto'  => $m->mensaje,
                'fecha'  => $m->creado_en,
            );
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array('ok' => true, 'mensajes' => $rows)));
    }

    /**
     * Devuelve la conversación completa (estilo chat) a partir del id
     * de un mensaje. Cada burbuja se pinta según su tipo: RECIBIDO o
     * ENVIADO.
     */
    public function conversacion($id) {
        $this->_check_admin();

        $mensajes = $this->wts_model->conversacion_por_id($id);

        $rows = array();
        foreach ($mensajes as $m) {
            $rows[] = array(
                'id'      => (int)$m->id,
                'fecha'   => $m->fecha,
                'origen'  => $m->origen,
                'destino' => $m->destino,
                'tipo'    => $m->tipo,
                'mensaje' => $m->mensaje,
            );
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array('ok' => true, 'mensajes' => $rows)));
    }

    /**
     * Reenvía (proxy) el envío de un mensaje al servicio Twilio vía
     * ngrok. Se hace desde el servidor para evitar el bloqueo de CORS
     * del navegador. No usa cURL: emplea streams HTTP nativos de PHP.
     */
    public function enviar_mensaje() {
        $this->_check_admin();

        $destino = $this->input->get('destino');
        $msg     = $this->input->get('msg');

        if (empty($destino) || empty($msg)) {
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode(array('ok' => false, 'error' => 'Faltan parámetros: destino y msg.')));
            return;
        }

        $url = base_url("") . "../../varios/twilios/twilio_enviar_mensajes.php"
            . '?destino=' . rawurlencode($destino)
            . '&msg=' . rawurlencode($msg);
        //die($url);

        $contexto = stream_context_create(array('http' => array(
            'method'     => 'GET',
            'timeout'    => 30,
            'ignore_errors' => true,
        )));

        $respuesta = @file_get_contents($url, false, $contexto);

        if ($respuesta === false) {
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode(array('ok' => false, 'error' => 'No se pudo conectar con el servicio de mensajería.')));
            return;
        }

        // Si el servicio devuelve JSON lo pasamos tal cual; si es texto
        // plano, lo envolvemos en un JSON estándar.
        $json = json_decode($respuesta, true);
        if ($json !== null) {
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode($json));
            return;
        }

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode(array('ok' => true, 'respuesta' => $respuesta)));
    }

    private function _check_admin() {
        if (!$this->session->userdata('admin_logueado')) {
            redirect('admin');
            exit;
        }
    }
}
