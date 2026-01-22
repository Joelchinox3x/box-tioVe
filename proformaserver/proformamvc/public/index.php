<?php
// public/index.php - Punto de entrada de la aplicación

// Configurar errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Autoload manual simple para clases propias
spl_autoload_register(function ($class) {
    // Convertir namespace a ruta de archivo
    // Ejemplo: App\Controllers\HomeController -> app/Controllers/HomeController.php
    //          Core\Router -> core/Router.php

    $base_dir = __DIR__ . '/../';

    // Convertir namespace a ruta (App -> app, Core -> core)
    $parts = explode('\\', $class);
    $parts[0] = strtolower($parts[0]); // Primera parte en minúscula
    $file = $base_dir . implode('/', $parts) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Iniciar sesión
session_start();

// Cargar helpers
require __DIR__ . '/../core/helpers.php';

// Configurar zona horaria
$config = require __DIR__ . '/../config/config.php';
date_default_timezone_set($config['app']['timezone']);

// Crear instancia del router
use Core\Router;

$router = new Router();

// ============================================
// DEFINIR RUTAS
// ============================================

// Ruta raíz
$router->get('/', 'HomeController', 'index');

// Rutas de Clientes
$router->get('/clientes', 'ClienteController', 'index');
$router->get('/clientes/create', 'ClienteController', 'create');
$router->post('/clientes/store', 'ClienteController', 'store');
$router->get('/clientes/edit/{id}', 'ClienteController', 'edit');
$router->post('/clientes/update/{id}', 'ClienteController', 'update');
$router->get('/clientes/delete/{id}', 'ClienteController', 'delete');
$router->post('/clientes/delete-multiple', 'ClienteController', 'deleteMultiple');
$router->get('/clientes/search', 'ClienteController', 'search');
$router->post('/clientes/verificar-pin', 'ClienteController', 'verificarPin');
$router->post('/clientes/consultar-dni', 'ClienteController', 'consultarDni');
$router->post('/clientes/update-phone', 'ClienteController', 'updatePhone');
$router->get('/clientes/export', 'ClienteController', 'export'); // Exportar JSON
$router->post('/clientes/import', 'ClienteController', 'import'); // Importar JSON

// Rutas de Productos/Inventario
$router->get('/inventario', 'ProductoController', 'index');
$router->get('/inventario/create', 'ProductoController', 'create');
$router->post('/inventario/store', 'ProductoController', 'store');
$router->get('/inventario/edit/{id}', 'ProductoController', 'edit');
$router->post('/inventario/update/{id}', 'ProductoController', 'update');
$router->get('/inventario/delete/{id}', 'ProductoController', 'delete');
$router->get('/inventario/detalle/{id}', 'ProductoController', 'show');
$router->get('/inventario/search', 'ProductoController', 'search');
$router->post('/inventario/toggle-lock', 'ProductoController', 'toggleLock');
$router->post('/inventario/verificar-pin', 'ProductoController', 'verificarPin');
$router->post('/inventario/add-photos/{id}', 'ProductoController', 'addPhotos'); // Quick Upload
$router->get('/inventario/export/{id}', 'ProductoController', 'export'); // Exportar ZIP
$router->post('/inventario/import', 'ProductoController', 'import'); // Importar ZIP

// Rutas de Folletos
$router->post('/inventario/folleto/crear', 'ProductoController', 'crearFolleto');
$router->post('/inventario/folleto/eliminar/{id}', 'ProductoController', 'eliminarFolleto');
$router->post('/inventario/folleto/toggle/{id}', 'ProductoController', 'toggleFolleto');
$router->get('/inventario/folleto/descargar/{id}', 'ProductoController', 'descargarFolleto');

// Rutas API
// $router->get('/api/productos/skus', 'ApiController', 'getSKUs');

// Rutas de Proformas
$router->get('/proformas', 'ProformaController', 'index');
$router->get('/proformas/create', 'ProformaController', 'create');
$router->post('/proformas/store', 'ProformaController', 'store');
$router->get('/proformas/show/{id}', 'ProformaController', 'show');
$router->get('/proformas/edit/{id}', 'ProformaController', 'edit');
$router->post('/proformas/update/{id}', 'ProformaController', 'update');
$router->get('/proformas/delete/{id}', 'ProformaController', 'delete');
$router->get('/proformas/pdf/{id}', 'ProformaController', 'pdf');
$router->get('/proformas/viewPdf/{id}', 'ProformaController', 'viewPdf');
$router->get('/proformas/downloadPdf/{id}', 'ProformaController', 'downloadPdf');
$router->get('/proformas/search', 'ProformaController', 'search');
$router->get('/proformas/getProductos', 'ProformaController', 'getProductos');
$router->get('/proformas/getProductos', 'ProformaController', 'getProductos');
$router->get('/proformas/getClientes', 'ProformaController', 'getClientes');

// RUTA PÚBLICA (Acceso directo por Token)
$router->get('/p/ver/{token}', 'ProformaController', 'publicView');
$router->get('/p/dl/{token}', 'ProformaController', 'publicDownload');
$router->get('/p/producto/{token}', 'ProductoController', 'publicShow'); // Nueva Home pública de producto
$router->get('/p/catalogo', 'ProductoController', 'publicCatalog'); // Catálogo General (Modo Vitrina)

// Rutas de Chatbot (IA)
$router->post('/chat/message', 'ChatController', 'sendMessage');

// Rutas MANAGER DE TEMAS (PDF)
$router->get('/pdf-templates', 'PdfTemplateController', 'index');
$router->get('/pdf-templates/create', 'PdfTemplateController', 'create');
$router->post('/pdf-templates/store', 'PdfTemplateController', 'store');
$router->get('/pdf-templates/edit/{id}', 'PdfTemplateController', 'edit');
$router->post('/pdf-templates/update/{id}', 'PdfTemplateController', 'update');
$router->get('/pdf-templates/duplicate/{id}', 'PdfTemplateController', 'duplicate');
$router->post('/pdf-templates/duplicate/{id}', 'PdfTemplateController', 'duplicate'); // Support for Modal Form
$router->get('/pdf-templates/delete/{id}', 'PdfTemplateController', 'delete');
$router->get('/pdf-templates/image/{filename}', 'PdfTemplateController', 'serveImage'); // Serve protected images
$router->get('/pdf-templates/footer-image/{filename}', 'PdfTemplateController', 'serveFooter'); // Serve protected images
$router->post('/pdf-templates/upload-footer', 'PdfTemplateController', 'uploadFooter'); // Ajax Upload
$router->post('/pdf-templates/delete-footer', 'PdfTemplateController', 'deleteFooter'); // Ajax Delete
$router->post('/pdf-templates/upload-background', 'PdfTemplateController', 'uploadBackground'); // Ajax Upload
$router->post('/pdf-templates/delete-background', 'PdfTemplateController', 'deleteBackground'); // Ajax Delete
$router->post('/pdf-templates/rename-background', 'PdfTemplateController', 'renameBackground'); // Ajax Rename
$router->post('/pdf-templates/rename-footer', 'PdfTemplateController', 'renameFooter'); // Ajax Rename
$router->get('/pdf-templates/preview/{id}', 'PdfTemplateController', 'preview'); // Vista Previa con PDF temporal
$router->post('/pdf-templates/restore/{id}', 'PdfTemplateController', 'restore'); // Restaurar desde Snapshot o Fábrica
$router->post('/pdf-templates/save-snapshot/{id}', 'PdfTemplateController', 'saveSnapshot'); // Guardar Snapshot del tema

// Rutas de Configuración y Temas
$router->get('/settings', 'SettingsController', 'index');
$router->post('/settings/change-theme', 'SettingsController', 'changeTheme');
$router->post('/settings/change-navbar', 'SettingsController', 'changeNavbar');
$router->post('/settings/change-header', 'SettingsController', 'changeHeader');
$router->post('/settings/change-app-name', 'SettingsController', 'changeAppName');
$router->post('/settings/change-manager-info', 'SettingsController', 'changeManagerInfo');
$router->post('/settings/change-gps', 'SettingsController', 'changeGpsEnabled');
$router->post('/settings/change-igv', 'SettingsController', 'changeIgv');
$router->post('/settings/change-logo', 'SettingsController', 'changeAppLogo');
$router->post('/settings/delete-logo', 'SettingsController', 'deleteAppLogo');
$router->post('/settings/change-pin', 'SettingsController', 'changePIN');
$router->post('/settings/change-api-dni', 'SettingsController', 'changeApiDni');
$router->post('/settings/change-registration', 'SettingsController', 'changeRegistration');
$router->post('/settings/change-chatbot', 'SettingsController', 'changeChatbotEnabled');
$router->post('/settings/change-toast-style', 'SettingsController', 'changeToastStyle');

// Rutas de Leads (Clientes Pendientes)
$router->post('/api/leads/store', 'LeadController', 'store');
$router->get('/leads', 'LeadController', 'index');
$router->post('/leads/approve', 'LeadController', 'approve');
$router->post('/leads/reject', 'LeadController', 'reject');
$router->post('/leads/update-from-query', 'LeadController', 'updateFromQuery');

// Rutas de Autenticación
$router->get('/login', 'AuthController', 'showLogin');
$router->post('/login', 'AuthController', 'login');
$router->get('/register', 'AuthController', 'showRegister');
$router->post('/register', 'AuthController', 'register');
$router->get('/logout', 'AuthController', 'logout');

// ============================================
// PROTECCIÓN DE RUTAS
// ============================================
use App\Middleware\AuthMiddleware;

// Obtener la URI actual
$requestUri = $_SERVER['REQUEST_URI'];
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '/';

// Quitar el directorio base si existe
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/') {
    $path = str_replace($scriptName, '', $path);
}

// Rutas públicas (no requieren autenticación)
$publicRoutes = ['/login', '/register', '/clientes/consultar-dni', '/api/leads/store', '/pdf-templates/fix-legacy-paths', '/chat/message'];

// Permitir rutas que empiezan con /p/ (vista pública y descarga)
$isPublicRoute = in_array($path, $publicRoutes) || strpos($path, '/p/') === 0 || strpos($path, '/pdf-templates') === 0;

// Si no es una ruta pública, verificar autenticación
if (!$isPublicRoute) {
    // AuthMiddleware::check();
}

// Resolver la ruta actual
$router->resolve();