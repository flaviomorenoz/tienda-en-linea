<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'tienda';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Tienda - catálogo
$route['tienda'] = 'tienda/index';
$route['tienda/categoria/(:any)'] = 'tienda/categoria/$1';
$route['tienda/producto/(:num)'] = 'tienda/detalle/$1';
$route['quienes-somos'] = 'tienda/quienes_somos';
$route['contacto'] = 'tienda/contacto';

// Carrito
$route['carrito'] = 'carrito/ver';
$route['carrito/agregar'] = 'carrito/agregar';
$route['carrito/quitar/(:num)'] = 'carrito/quitar/$1';
$route['carrito/actualizar'] = 'carrito/actualizar';
$route['carrito/vaciar'] = 'carrito/vaciar';
$route['carrito/actualizar_datos_cliente/(:num)'] = 'carrito/actualizar_datos_cliente/$1';

// Libro de Reclamaciones (Ley N° 29571 - D.S. N° 011-2011-PCM - D.S. N° 004-2024-PCM)
$route['libro-reclamaciones']               = 'libro_reclamaciones/index';
$route['libro-reclamaciones/enviar']        = 'libro_reclamaciones/enviar';
$route['libro-reclamaciones/verificar']     = 'libro_reclamaciones/verificar';
$route['libro-reclamaciones/consultar']     = 'libro_reclamaciones/consultar';

// Asistente IA / Chat con vendedora
/*
$route['chat/preguntar'] = 'chat/preguntar';
$route['chat/solicitar_vendedora'] = 'chat/solicitar_vendedora';
$route['chat/enviar'] = 'chat/enviar';
$route['chat/estado'] = 'chat/estado';
$route['chat/subir_imagen'] = 'chat/subir_imagen';
$route['chat/imagen/(:any)'] = 'chat/imagen/$1';

// Asistente IA por WhatsApp (módulo independiente)
$route['whatsapp/webhook'] = 'whatsapp/webhook';
$route['admin/whatsapp'] = 'whatsapp/historial';
$route['admin/whatsapp/json'] = 'whatsapp/historial_json';
$route['admin/whatsapp/mensajes/(:any)'] = 'whatsapp/mensajes/$1';
*/

// Panel admin: conversaciones que se quedaron solo con la IA
//$route['admin/chats_ia'] = 'chat/chats_ia';
//$route['admin/chats_ia/json'] = 'chat/chats_ia_json';
//$route['admin/chats_ia/mensajes/(:num)'] = 'chat/chats_ia_mensajes/$1';

// Pago y checkout
$route['checkout'] = 'pago/checkout';
$route['pago/procesar'] = 'pago/procesar';
$route['pedido/gracias/(:num)'] = 'pago/gracias/$1';
$route['pedido/cancelado'] = 'pago/cancelado';

// Ajustes (configuración)
$route['ajustes'] = 'ajustes/index';
$route['ajustes/guardar_tema'] = 'ajustes/guardar_tema';

// Admin
$route['admin'] = 'admin/login';
$route['admin/login'] = 'admin/login';
$route['admin/login_post'] = 'admin/login_post';
$route['admin/logout'] = 'admin/logout';
$route['admin/pedidos'] = 'admin/pedidos';
$route['admin/estado/(:num)']   = 'admin/actualizar_estado/$1';
$route['admin/codigos/(:num)']    = 'admin/guardar_codigos/$1';
$route['admin/pedidos_json']       = 'admin/pedidos_json';
$route['admin/detalle/(:num)']     = 'admin/detalle_json/$1';

// Admin - Libro de Reclamaciones
$route['admin/libro-reclamaciones']            = 'admin/reclamos';
$route['admin/libro-reclamaciones/json']       = 'admin/reclamos_json';
$route['admin/libro-reclamaciones/detalle/(:num)']    = 'admin/reclamo_detalle/$1';
$route['admin/libro-reclamaciones/responder/(:num)']  = 'admin/reclamo_responder/$1';

// Panel de chats (vendedoras)
/*
$route['admin/chats'] = 'vendedora/panel';
$route['admin/chats/en_espera'] = 'vendedora/en_espera_json';
$route['admin/chats/mis_conversaciones'] = 'vendedora/mis_conversaciones_json';
$route['admin/chats/tomar/(:num)'] = 'vendedora/tomar/$1';
$route['admin/chats/mensajes/(:num)'] = 'vendedora/mensajes/$1';
$route['admin/chats/responder/(:num)'] = 'vendedora/responder/$1';
$route['admin/chats/cerrar/(:num)'] = 'vendedora/cerrar/$1';
$route['admin/chats/disponibilidad'] = 'vendedora/disponibilidad';
$route['admin/chats/historial'] = 'vendedora/historial';
$route['admin/chats/historial_json'] = 'vendedora/historial_json';
$route['admin/chats/actualizar_cliente/(:num)'] = 'vendedora/actualizar_cliente/$1';
*/