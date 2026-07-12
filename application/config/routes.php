<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'tienda';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Tienda - catálogo
$route['tienda'] = 'tienda/index';
$route['tienda/categoria/(:any)'] = 'tienda/categoria/$1';
$route['tienda/producto/(:num)'] = 'tienda/detalle/$1';

// Carrito
$route['carrito'] = 'carrito/ver';
$route['carrito/agregar'] = 'carrito/agregar';
$route['carrito/quitar/(:num)'] = 'carrito/quitar/$1';
$route['carrito/actualizar'] = 'carrito/actualizar';
$route['carrito/vaciar'] = 'carrito/vaciar';

// Asistente IA
$route['chat/preguntar'] = 'chat/preguntar';

// Pago y checkout
$route['checkout'] = 'pago/checkout';
$route['pago/procesar'] = 'pago/procesar';
$route['pedido/gracias/(:num)'] = 'pago/gracias/$1';
$route['pedido/cancelado'] = 'pago/cancelado';

// Panel admin
$route['admin'] = 'admin/login';
$route['admin/login'] = 'admin/login';
$route['admin/login_post'] = 'admin/login_post';
$route['admin/logout'] = 'admin/logout';
$route['admin/pedidos'] = 'admin/pedidos';
$route['admin/estado/(:num)']   = 'admin/actualizar_estado/$1';
$route['admin/codigos/(:num)']    = 'admin/guardar_codigos/$1';
$route['admin/pedidos_json']       = 'admin/pedidos_json';
$route['admin/detalle/(:num)']     = 'admin/detalle_json/$1';
