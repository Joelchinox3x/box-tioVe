<?php
// config.php
// Put this file outside webroot if you can. For simplicity it's in the folder.

$db_host = 'mi_mysql';
$db_name = 'proforma-app';
$db_user = 'server_admin';
$db_pass = 'Cocacola123';

// WooCommerce API (no pongas las keys aquí en público)
// Puedes ponerlas en este archivo antes de subir
$wc_consumer_key = 'ck_af7c07fdf0af4b4c698adc38c987d0f7593c63a7';
$wc_consumer_secret = 'cs_bd45466fe0443f65fcc99f2e4ff515f32bd89b9e';
$wc_store_url = 'https://tradimacova.com'; // sin slash final

// Definimos la constante de la ruta raíz del proyecto
define('PROJECT_ROOT', __DIR__);



// PIN DE SEGURIDAD (Para borrar o desbloquear clientes)
$admin_pin = '123'; // <--- CAMBIA ESTO POR TU CLAVE
$delete_password = "123"; // Cambia "123" por tu contraseña

try {
  $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);
} catch (Exception $e) {
  die('DB connection failed: ' . $e->getMessage());
}
