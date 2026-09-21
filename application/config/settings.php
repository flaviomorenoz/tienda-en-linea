<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Datos de la tienda
$config['tienda_nombre']    = 'Bella Rosse';
$config['tienda_slogan']    = 'Ropa y accesorios para todos';

// Correo que RECIBE los avisos de pedidos nuevos (ver Pago::_enviar_correo_pedido)
$config['tienda_email']     = 'flaviomorenoz@gmail.com';
// Cuenta SMTP que figura como remitente (debe coincidir con smtp_user de config/email.php)
$config['email_remitente']  = 'flaviomorenoz@gmail.com';

// Datos del proveedor para el Libro de Reclamaciones (Ley N° 29571)
// Completar con los datos reales del negocio registrado en SUNAT.
$config['tienda_ruc']             = '';
$config['tienda_razon_social']    = 'Bella Rosse';
$config['tienda_domicilio_fiscal']= '';

// WhatsApp - reemplazar con el número real (con código de país, sin +)
// Ejemplo Perú: 51987654321
$config['whatsapp_numero']  = '+51991629237';
$config['whatsapp_mensaje'] = 'Hola, me interesa el siguiente producto: ';

// Moneda
$config['moneda_simbolo']   = 'S/';
$config['moneda_codigo']    = 'PEN';

// Paginación de productos
$config['productos_por_pagina'] = 12;
