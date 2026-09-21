<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Configuración de la librería Email (CI_Email).
 *
 * IMPORTANTE: al cargar la librería SIN parámetros  ->  $this->load->library('email');
 * CodeIgniter incluye automáticamente este archivo y pasa el arreglo $config
 * al constructor (ver system/core/Loader.php -> _ci_init_library()).
 *
 * Envío con Gmail: requiere una "Contraseña de aplicación" (cuenta con
 * verificación en 2 pasos). Google la muestra con espacios
 * (xxxx xxxx xxxx xxxx), pero aquí se usa el valor real de 16 caracteres SIN espacios.
 *
 * Puertos válidos:
 *   - 587 + smtp_crypto = 'tls'  (STARTTLS, el usado aquí)
 *   - 465 + smtp_crypto = 'ssl'  (SSL directo)
 */

$config['protocol']     = 'smtp';
$config['smtp_host']    = 'smtp.gmail.com';
$config['smtp_port']    = 587;
$config['smtp_crypto']  = 'tls';
$config['smtp_user']    = 'flaviomorenoz@gmail.com';
$config['smtp_pass']    = 'ekwklfohupwhiail';   // App password SIN espacios
$config['smtp_timeout'] = 15;                   // default de CI3: 5 segundos

$config['mailtype']     = 'html';
$config['charset']      = 'utf-8';
$config['newline']      = "\r\n";
$config['crlf']         = "\r\n";
$config['wordwrap']     = TRUE;
