<?php
$timezone = "America/Lima";
date_default_timezone_set($timezone);

class Queries_por_fuera extends CI_Controller
{

    function __construct() {    
        parent::__construct();
        $this->load->model('queries_por_fuera_model');
    }

    function actualizar_estados(){
        $pedidos = $this->queries_por_fuera_model->get_pedidos_con_codigo();

        foreach ($pedidos as $pedido){
            $nro_orden          = trim($pedido->nro_orden);
            $codigo_transaccion = trim($pedido->codigo_transaccion);
            
            // *****************************************************************************************
            $respuesta          = $this->consulta_api_estado_envio($nro_orden, $codigo_transaccion);
            // *****************************************************************************************
            
            $data = json_decode($respuesta, true);
            if (isset($data['status'])) {
                
                $status = $data['status'];

                // Obtener las fechas (si existen)
                $fecha_origen   = $status['origen']['fecha']   ?? null;
                $fecha_transito = $status['transito']['fecha'] ?? null;
                $fecha_destino  = $status['destino']['fecha']  ?? null;
                $entregado      = $status['entregado']         ?? false;
                
                if($entregado){
                    $estado_envio = 'ENTREGADO';
                }elseif($fecha_destino){
                    $estado_envio = 'EN_DESTINO';
                }elseif($fecha_transito){
                    $estado_envio = 'EN_TRANSITO';
                }elseif($fecha_origen){
                    $estado_envio = 'EN_ORIGEN';
                }else{ 
                    $estado_envio = 'EN_ORIGEN';
                }

                $cadena = "Pedido ID: " . $pedido->id . " - Nro Orden: " . $nro_orden . " - Código Transacción: " . $codigo_transaccion . " - Nuevo Estado: " . $estado_envio;
                traza($cadena);
                echo $cadena."<br>\n";

                $this->queries_por_fuera_model->actualizar_estado($pedido->id, $estado_envio);
            }else{
                traza("Pedido ID: " . " - Nro Orden: " . $nro_orden . " - Código Transacción: " . $codigo_transaccion . " - No se pudo obtener el estado de envío.");
            }
        }
        traza("PROCESO DE ACTUALIZACION DE ESTADOS.");
    }

    function consulta_api_estado_envio($nro_orden, $codigo_transaccion){

        $token = "sk_xqva7y3hwjwfbbzavqpoi7sm46nv7wux3wu5dcaat6il5ahv4mbq";

        $param1 = "numero=" . $nro_orden;
        $param2 = "codigo=" . $codigo_transaccion;

        $url = "https://api.shalom-api-peru.com/v1/tracking?" . $param1 . "&" . $param2;

        $curl = curl_init();

        if ($curl === false) {
            die("No se pudo inicializar cURL");
        }

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "X-API-Key: $token"
            ],
        ]);

        $respuesta = curl_exec($curl);

        curl_close($curl);    

        if (curl_errno($curl)) {
            return "Error: " . curl_error($curl);
        } else {
            return $respuesta;
        }
    }
}