<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends CI_Controller {

    private $deepseek_url   = 'https://api.deepseek.com/chat/completions';
    private $deepseek_model = 'deepseek-chat';

    public function __construct() {
        parent::__construct();
        $this->load->model('Asistente_model');
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

        $mensaje   = trim((string)$this->input->post('mensaje'));
        $historial = json_decode((string)$this->input->post('historial'), true);

        if (!is_array($historial)) {
            $historial = array();
        }

        if ($mensaje === '') {
            echo json_encode(array('ok' => false, 'error' => 'Escribe una pregunta.'));
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

        echo json_encode(array('ok' => true, 'respuesta' => $respuesta));
    }

    private function _system_prompt() {
        $nombre_tienda = $this->config->item('tienda_nombre');
        $catalogo      = $this->Asistente_model->get_catalogo_contexto();

        $prompt = "Eres el asistente virtual de {$nombre_tienda}, una tienda que vende principalmente " .
            "lencería y prendas de vestir para mujeres de todas las edades, y también boxers/calzoncillos " .
            "para varones.\n\n" .
            "Responde preguntas de los clientes sobre las prendas (tallas, colores, precios, categorías, " .
            "material, etc.) usando SOLO la información del catálogo entregado abajo, en formato CSV " .
            "(separador \";\"). Si el catálogo no indica el material de una prenda, dilo con honestidad y " .
            "ofrece consultarlo con una vendedora en vez de inventarlo.\n\n" .
            "Reglas:\n" .
            "- Responde siempre en español, de forma breve, cálida y amable.\n" .
            "- Los precios están en soles (S/).\n" .
            "- La columna \"sexo\" indica: M = mujer, H = hombre.\n" .
            "- La columna \"tallas_disponibles\" lista las tallas con stock; si está vacía, no hay tallas " .
            "registradas para ese producto.\n" .
            "- Si preguntan por algo que no está en el catálogo, dilo con honestidad y sugiere contactar a " .
            "una vendedora por WhatsApp.\n\n";

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
