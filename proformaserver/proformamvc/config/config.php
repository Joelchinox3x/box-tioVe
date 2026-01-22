<?php
// config/config.php - Configuración de la aplicación

return [
    // Base de datos
    'db' => [
        'host' => 'mi_mysql',
        'dbname' => 'proformamvc',
        'user' => 'server_admin',
        'password' => 'Cocacola123',
        'charset' => 'utf8mb4'
    ],
    
    // Configuración de la app
    'app' => [
        'name' => 'Tradimacova App',
        'url' => 'http://localhost/proformamvc',
        'timezone' => 'America/Lima',
        'locale' => 'es'
    ],
    
    // Seguridad
    'security' => [
        'admin_pin' => '123',  // PIN para desbloquear clientes protegidos
        'session_lifetime' => 7200  // 2 horas
    ],
    
    // Rutas de archivos
    'paths' => [
        'uploads' => __DIR__ . '/../public/uploads/',
        'pdfs' => __DIR__ . '/../public/pdfs/',
        'assets' => __DIR__ . '/../public/assets/'
    ],
    
    // WooCommerce (si lo usas)
    'woocommerce' => [
        'store_url' => 'https://tusitio.com',
        'consumer_key' => '',
        'consumer_secret' => ''
    ],
    
    // Configuración de PDF
    'pdf' => [
        'default_template' => 'orange',
        'logo_path' => __DIR__ . '/../public/assets/img/logo.png',
        'color_brand' => '#f37021'
    ],
    
    // Email (para futuras funcionalidades)
    'email' => [
        'from_address' => 'noreply@tradimacova.com',
        'from_name' => 'Tradimacova'
    ],

    // API Perú - Consulta DNI/RUC
    'apiperu' => [
        'token' => '01f863c7784de4e94368e129dd3d8b03b7dade81065d536c385066995fcdd876'
    ],

    // Decolecta - Alternativa para DNI/RUC
    'decolecta' => [
        'token' => ''  // Token de Decolecta
    ]
];