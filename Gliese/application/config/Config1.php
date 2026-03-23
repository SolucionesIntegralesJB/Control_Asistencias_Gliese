<?php
// -- Zona Horaria
date_default_timezone_set('America/Lima');
// --
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $protocol = "https://";
} else {
    $protocol = "http://";
}
// --
$base_url =  $protocol . $_SERVER['HTTP_HOST'] .  '/GLIESE/'; //<--Direccion HTTP
// ---
define('BASE_URL', $base_url);
define('DEFAULT_CONTROLLER', 'Login');
define('DEFAULT_LAYOUT', 'layout');
// --
define('DB_HOST', 'solucionesintegralesjb.com');
define('DB_NAME', 'soluciones_gliese');
define('DB_USER', 'soluciones_gliese');
define('DB_PASS', 'Ns7l3TRaF5%!');
define('DB_PORT', 3306);