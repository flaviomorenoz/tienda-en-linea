<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Datos de la tienda
$config['tienda_nombre']    = 'Bella Rosse';
$config['tienda_slogan']    = 'Ropa y accesorios para todos';
$config['tienda_email']     = 'contacto@mitienda.com';

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
