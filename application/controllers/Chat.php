<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends CI_Controller {

    private $deepseek_url   = 'https://api.deepseek.com/chat/completions';
    private $deepseek_model = 'deepseek-chat';

    public function __construct() {
        parent::__construct();
        $this->load->model('Asistente_model');
        $this->load->model('Chat_model');
    }

    /**
     * Endpoint AJAX: recibe la pregunta del cliente + historial de la
     * conversación y devuelve la respuesta de la IA (DeepSeek).
     */
    public function preguntar() {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $token     = $this->_token_valido($this->input->post('token'));
        $mensaje   = trim((string)$this->input->post('mensaje'));
        $historial = json_decode((string)$this->input->post('historial'), true);

        if (!is_array($historial)) {
            $historial = array();
        }

        if ($mensaje === '') {
            echo json_encode(array('ok' => false, 'error' => 'Escribe una pregunta.'));
            return;
        }

        if ($token === false) {
            echo json_encode(array('ok' => false, 'error' => 'Sesión de chat inválida.'));
            return;
        }

        $conversacion = $this->Chat_model->obtener_o_crear($token);

        if ($conversacion->estado !== 'ia') {
            echo json_encode(array('ok' => false, 'error' => 'Esta conversación ya no está a cargo de la IA.'));
            return;
        }

        $api_key = getenv('DEEPSEEK_API_KEY');

        if (empty($api_key)) {
            log_message('error', 'DEEPSEEK_API_KEY no configurada en application/config/.env');
            echo json_encode(array('ok' => false, 'error' => 'El asistente no está disponible en este momento.'));
            return;
        }

        $mensajes = array(array('role' => 'system', 'content' => $this->_system_prompt()));

        foreach ($historial as $h) {
            if (isset($h['role'], $h['content']) && in_array($h['role'], array('user', 'assistant'), true)) {
                $mensajes[] = array('role' => $h['role'], 'content' => (string)$h['content']);
            }
        }

        $mensajes[] = array('role' => 'user', 'content' => $mensaje);

        $respuesta = $this->_llamar_deepseek($api_key, $mensajes);

        if ($respuesta === false) {
            echo json_encode(array('ok' => false, 'error' => 'No se pudo contactar al asistente. Intenta nuevamente.'));
            return;
        }

        $this->Chat_model->guardar_mensaje($conversacion->id, 'cliente', $mensaje);
        $this->Chat_model->guardar_mensaje($conversacion->id, 'ia', $respuesta);

        echo json_encode(array('ok' => true, 'respuesta' => $respuesta));
    }

    /**
     * El cliente pide hablar con una vendedora. Pasa la conversación
     * a la cola de espera (no asigna a nadie todavía).
     */
    public function solicitar_vendedora() {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $token  = $this->_token_valido($this->input->post('token'));
        $motivo = trim((string)$this->input->post('motivo'));

        if ($token === false) {
            echo json_encode(array('ok' => false, 'error' => 'Sesión de chat inválida.'));
            return;
        }

        $conversacion = $this->Chat_model->obtener_o_crear($token);

        if ($conversacion->estado !== 'ia') {
            echo json_encode(array('ok' => true));
            return;
        }

        $this->Chat_model->guardar_mensaje($conversacion->id, 'sistema', 'El cliente solicitó hablar con una vendedora.');
        $this->Chat_model->solicitar_vendedora($conversacion->id, $motivo !== '' ? $motivo : null);

        echo json_encode(array('ok' => true));
    }

    /**
     * Mensaje del cliente mientras está en espera o ya con una
     * vendedora (no pasa por la IA).
     */
    public function enviar() {
        if ($this->input->method() !== 'post') {
            show_404();
            return;
        }

        $token   = $this->_token_valido($this->input->post('token'));
        $mensaje = trim((string)$this->input->post('mensaje'));

        if ($token === false || $mensaje === '') {
            echo json_encode(array('ok' => false, 'error' => 'Datos inválidos.'));
            return;
        }

        $conversacion = $this->Chat_model->obtener_por_token($token);

        if (!$conversacion || !in_array($conversacion->estado, array('en_espera', 'vendedor'), true)) {
            echo json_encode(array('ok' => false, 'error' => 'Esta conversación no admite mensajes en este momento.'));
            return;
        }

        $this->Chat_model->guardar_mensaje($conversacion->id, 'cliente', $mensaje);

        echo json_encode(array('ok' => true));
    }

    /**
     * Polling del cliente: estado actual de la conversación + mensajes
     * nuevos desde el último id que ya tiene pintado en pantalla.
     */
    public function estado() {
        $token    = $this->_token_valido($this->input->get('token'));
        $desde_id = (int)$this->input->get('desde');

        if ($token === false) {
            echo json_encode(array('ok' => false, 'error' => 'Sesión de chat inválida.'));
            return;
        }

        $conversacion = $this->Chat_model->obtener_por_token($token);

        if (!$conversacion) {
            echo json_encode(array('ok' => true, 'estado' => 'ia', 'vendedor_nombre' => null, 'mensajes' => array()));
            return;
        }

        $info     = $this->Chat_model->info_estado($conversacion->id);
        $mensajes = $this->Chat_model->mensajes_nuevos($conversacion->id, $desde_id);

        $rows = array();
        foreach ($mensajes as $m) {
            $rows[] = array(
                'id'     => (int)$m->id,
                'emisor' => $m->emisor,
                'texto'  => $m->mensaje,
            );
        }

        echo json_encode(array(
            'ok'              => true,
            'estado'          => $info->estado,
            'vendedor_nombre' => $info->vendedor_nombre,
            'mensajes'        => $rows,
        ));
    }

    private function _token_valido($token) {
        $token = trim((string)$token);
        return preg_match('/^[a-zA-Z0-9\-]{10,64}$/', $token) ? $token : false;
    }

    private function _system_prompt() {
        $nombre_tienda = $this->config->item('tienda_nombre');
        $catalogo      = $this->Asistente_model->get_catalogo_contexto();

        $prompt = "Eres el asistente virtual de {$nombre_tienda}, una tienda que vende principalmente " .
            "lencería y prendas de vestir para mujeres de todas las edades, y también boxers/calzoncillos " .
            "para varones.\n\n" .
            "Responde preguntas de los clientes sobre las prendas de forma escueta(tallas, colores, precios, categorías, " .
            "material, etc.) usando SOLO la información del catálogo entregado abajo, en formato CSV " .
            "(separador \";\"). Si el catálogo no indica el material de una prenda, no lo menciones, asimismo cualquier pregunta que te hagan dilo con honestidad.\n\n" .
            "Reglas:\n" .
            "- Responde siempre en español, de forma breve y amable.\n" .
            "- Los precios están en soles (S/).\n" .
            "- La columna \"sexo\" indica: M = mujer, H = hombre.\n" .
            "- La columna \"tallas_disponibles\" lista las tallas con stock; si está vacía, no hay tallas " .
            "registradas para ese producto.\n" .
            "- Si preguntan por algo que no está en el catálogo, dilo con honestidad.\n\n";

        if ($catalogo !== '') {
            $prompt .= "Catálogo de productos:\n{$catalogo}";
        } else {
            $prompt .= "El catálogo aún no tiene productos cargados. Indica que por el momento no hay " .
                "información de productos disponible y sugiere contactar a una vendedora.";
        }

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
}
