<?php
/**
 * API Principal - BoxEvent App
 * Punto de entrada para todas las peticiones
 */

// Headers CORS
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// DEBUG ALL REQUESTS
file_put_contents(__DIR__ . '/../files/debug_requests.txt', "[" . date('Y-m-d H:i:s') . "] " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir archivos necesarios
require_once __DIR__ . '/../config/Database.php';

// Inicializar base de datos
$database = new Database();
$db = $database->getConnection();

// Obtener método y ruta
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remover /api del path
$requestUri = str_replace('/api', '', $requestUri);
$requestUri = trim($requestUri, '/');

// Separar ruta en partes
$uriParts = explode('/', $requestUri);
$resource = $uriParts[0] ?? '';
$id = $uriParts[1] ?? null;
$action = $uriParts[2] ?? null;

// ==========================================
// RUTAS DE LA API
// ==========================================

try {
    switch ($resource) {

        // ========== EVENTOS ==========
        case 'eventos':
            require_once __DIR__ . '/../controllers/EventosController.php';
            $controller = new EventosController($db);

            if ($method === 'GET' && !$id) {
                // GET /api/eventos - Obtener evento principal
                echo json_encode($controller->getEventoPrincipal());
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint no encontrado"]);
            }
            break;

        // ========== PELEADORES ==========
        case 'diagnostic':
            require_once __DIR__ . '/../check_db.php';
            exit; // check_db.php already prints output
            break;
            
        case 'peleadores':
            require_once __DIR__ . '/../controllers/PeleadoresController.php';
            $controller = new PeleadoresController($db);

            if ($method === 'GET' && !$id) {
                // GET /api/peleadores - Listar todos
                $filtro = $_GET['filtro'] ?? 'todos'; // todos, populares, club
                $club = $_GET['club'] ?? null;
                echo json_encode($controller->listar($filtro, $club));

            } elseif ($method === 'GET' && $id === 'usuario' && $action) {
                // GET /api/peleadores/usuario/{id} - Obtener por ID de Usuario
                echo json_encode($controller->obtenerPorUsuarioId($action));

            } elseif ($method === 'GET' && $id === 'manager-contacto' && !$action) {
                // GET /api/peleadores/manager-contacto?rol=manager_cobros
                $rol = $_GET['rol'] ?? 'manager_peleadores';
                echo json_encode($controller->getManagerContacto($rol));

            } elseif ($method === 'GET' && $id && $action === 'inscripcion-evento') {
                // GET /api/peleadores/{id}/inscripcion-evento - Estado de inscripción al evento
                echo json_encode($controller->getInscripcionEvento($id));

            } elseif ($method === 'POST' && $id && $action === 'asignar-manager') {
                // POST /api/peleadores/{id}/asignar-manager - Registrar asignacion de manager
                echo json_encode($controller->registrarAsignacion($id));

            } elseif ($method === 'POST' && $id && $action === 'crear-inscripcion') {
                // POST /api/peleadores/{id}/crear-inscripcion - Crear inscripción al evento (sin pago)
                echo json_encode($controller->crearInscripcion($id));

            } elseif ($method === 'POST' && $id && $action === 'inscribir-evento') {
                // POST /api/peleadores/{id}/inscribir-evento - Enviar pago de inscripción
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                if (!empty($_FILES) || strpos($contentType, 'multipart/form-data') !== false) {
                    $data = $_POST;
                } else {
                    $data = json_decode(file_get_contents("php://input"), true);
                }
                echo json_encode($controller->inscribirEvento($id, $data));

            } elseif ($method === 'GET' && $id && $action === 'verificar-dni') {
                // GET /api/peleadores/{dni}/verificar-dni - Verificar si DNI existe
                echo json_encode($controller->verificarDNI($id));

            } elseif ($method === 'GET' && $id) {
                // GET /api/peleadores/{id} - Detalle de un peleador
                echo json_encode($controller->obtenerPorId($id));

            } elseif ($method === 'POST' && !$id) {
                // POST /api/peleadores
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                
                // AGREGADO: !empty($_FILES) || ...
                if (!empty($_FILES) || strpos($contentType, 'multipart/form-data') !== false) {
                    $data = $_POST;
                } else {
                    $data = json_decode(file_get_contents("php://input"), true);
                }

                echo json_encode($controller->inscribir($data));

            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint no encontrado"]);
            }
            break;

        // ========== PELEADORES APROBADOS ==========
        case 'peleadores-aprobados':
            require_once __DIR__ . '/../controllers/PeleadoresController.php';
            $controller = new PeleadoresController($db);

            if ($method === 'GET') {
                // GET /api/peleadores-aprobados - Listar solo peleadores aprobados
                echo json_encode($controller->listarAprobados());
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint no encontrado"]);
            }
            break;

        // ========== FIGHTER CARDS (JSON + BAKED) ==========
        case 'fighter-cards':
            require_once __DIR__ . '/../controllers/FighterCardsController.php';
            $controller = new FighterCardsController($db);

            if ($method === 'POST') {
                // POST /api/fighter-cards
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                if (!empty($_FILES) || strpos($contentType, 'multipart/form-data') !== false) {
                    $data = $_POST;
                } else {
                    $data = json_decode(file_get_contents("php://input"), true);
                }
                echo json_encode($controller->guardar($data));

            } elseif ($method === 'GET' && $id) {
                // GET /api/fighter-cards/{peleador_id}
                echo json_encode($controller->listarPorPeleador($id));

            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint card no encontrado"]);
            }
            break;

        // ========== PELEAS ==========
        case 'peleas':
            require_once __DIR__ . '/../controllers/PeleasController.php';
            $controller = new PeleasController($db);

            if ($method === 'GET' && !$id) {
                // GET /api/peleas - Obtener cartelera
                echo json_encode($controller->obtenerCartelera());

            } elseif ($method === 'POST' && $id && $action === 'votar') {
                // POST /api/peleas/{id}/votar - Votar por un peleador
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->votar($id, $data));

            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint no encontrado"]);
            }
            break;

        // ========== PROMOCIONES ==========
        case 'promociones':
            require_once __DIR__ . '/../controllers/PromocionesController.php';
            $controller = new PromocionesController($db);

            if ($method === 'POST') {
                // POST /api/promociones - Registrar una promoción
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->registrar($data));

            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint no encontrado"]);
            }
            break;

        // ========== RANKING ==========
        case 'ranking':
            require_once __DIR__ . '/../controllers/PeleadoresController.php';
            $controller = new PeleadoresController($db);

            if ($method === 'GET') {
                // GET /api/ranking - Ranking de popularidad
                echo json_encode($controller->ranking());
            }
            break;

        // ========== CLUBS ==========
        case 'clubs':
            require_once __DIR__ . '/../controllers/ClubsController.php';
            $controller = new ClubsController($db);

            if ($method === 'GET' && !$id) {
                // GET /api/clubs - Listar todos los clubs
                echo json_encode($controller->listar());

            } elseif ($method === 'GET' && $id && !$action) {
                // GET /api/clubs/{id} - Obtener un club
                echo json_encode($controller->obtenerPorId($id));

            } elseif ($method === 'GET' && $id && $action === 'peleadores') {
                // GET /api/clubs/{id}/peleadores - Peleadores del club
                echo json_encode($controller->obtenerPeleadores($id));

            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint no encontrado"]);
            }
            break;

        // ========== USUARIOS ==========
        case 'usuarios':
            require_once __DIR__ . '/../controllers/UsuariosController.php';
            $controller = new UsuariosController($db);

            if ($method === 'POST' && $id === 'registro') {
                // POST /api/usuarios/registro - Registrar espectador
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

                // Si viene multipart/form-data (con foto), usar $_POST
                if (!empty($_FILES) || strpos($contentType, 'multipart/form-data') !== false) {
                    $data = $_POST;
                } else {
                    $data = json_decode(file_get_contents("php://input"), true);
                }

                echo json_encode($controller->registrarEspectador($data));

            } elseif ($method === 'GET' && $id && $id !== 'verificar-email' && $id !== 'login' && !$action) {
                // GET /api/usuarios/{id} - Obtener perfil por ID
                echo json_encode($controller->getById($id));

            } elseif ($method === 'POST' && $id === 'login') {
                // POST /api/usuarios/login - Iniciar sesión
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->login($data));

            } elseif ($method === 'PUT' && $id && $action === 'perfil') {
                // PUT /api/usuarios/{id}/perfil - Actualizar perfil
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

                // Si viene multipart/form-data (con foto), usar $_POST
                if (!empty($_FILES) || strpos($contentType, 'multipart/form-data') !== false) {
                    $data = $_POST;
                } else {
                    $data = json_decode(file_get_contents("php://input"), true);
                }

                echo json_encode($controller->updateProfile($id, $data));

            } elseif ($method === 'POST' && $id === 'update-password') {
                // POST /api/usuarios/update-password - Actualizar contraseña
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->actualizarPassword($data));

            } elseif ($method === 'GET' && $id === 'verificar-email') {
                // GET /api/usuarios/verificar-email?email=xxx
                $email = $_GET['email'] ?? '';
                echo json_encode($controller->verificarEmail($email));

            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint no encontrado"]);
            }
            break;

        // ========== ENTRADAS ==========
        case 'entradas':
            require_once __DIR__ . '/../controllers/EntradasController.php';
            $controller = new EntradasController($db);

            if ($method === 'POST' && !$id) {
                // POST /api/entradas - Comprar entrada
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->comprar($data));

            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint no encontrado"]);
            }
            break;

        // ========== CARD TEMPLATES (FONDOS Y BORDES) ==========
        case 'card-templates':
            require_once __DIR__ . '/../controllers/CardTemplatesController.php';
            $controller = new CardTemplatesController();

            if ($method === 'GET' && ($id === 'backgrounds' || $id === 'borders' || $id === 'stickers')) {
                // GET /api/card-templates/backgrounds (o borders o stickers)
                echo json_encode($controller->listar($id));
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint no encontrado"]);
            }
            break;

        // ========== CONFIGURACIÓN (APP) ==========
        case 'settings':
            require_once __DIR__ . '/../controllers/SettingsController.php';
            $controller = new SettingsController($db);

            if ($method === 'GET' && !$id) {
                // GET /api/settings - Listar todos los settings
                echo json_encode($controller->getAllSettings());

            } elseif ($method === 'GET' && $id) {
                // GET /api/settings/{key} - Obtener un setting
                echo json_encode($controller->getSetting($id));

            } elseif ($method === 'PUT' && $id) {
                // PUT /api/settings/{key} - Actualizar un setting
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->updateSetting($id, $data));

            } else {
                http_response_code(400);
                echo json_encode(["error" => "Falta la clave del setting"]);
            }
            break;

        // ========== SUBIDA TEMPORAL (APP -> WEB) ==========
        case 'temp-upload':
            require_once __DIR__ . '/../controllers/TempUploadController.php';
            $controller = new TempUploadController();

            if ($method === 'POST') {
                echo json_encode($controller->upload($_FILES));
            } else {
                http_response_code(405);
                echo json_encode(["error" => "Método no permitido"]);
            }
            break;

        // ========== HERRAMIENTA WEB (APP -> WEB) ==========
        case 'bg-remover':
            // Servir el HTML directamente
            $htmlFile = __DIR__ . '/bg_remover.html';
            if (file_exists($htmlFile)) {
                header('Content-Type: text/html');
                readfile($htmlFile);
            } else {
                http_response_code(404);
                echo "Herramienta no encontrada en " . $htmlFile;
            }
            exit;
            break;

        // ========== SERVIR CONTENIDO (APP -> WEB) ==========
        case 'temp-content':
            // Recibir nombre de archivo por query
            $fileName = $_GET['file'] ?? '';
            $filePath = __DIR__ . '/uploads/temp/' . basename($fileName);
            
            if (!empty($fileName) && file_exists($filePath)) {
                $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                $mimeTypes = [
                    'png' => 'image/png',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'wasm' => 'application/wasm',
                    'json' => 'application/json',
                    'js' => 'application/javascript',
                    'mjs' => 'application/javascript'
                ];
                $contentType = $mimeTypes[strtolower($ext)] ?? 'application/octet-stream';
                
                header('Content-Type: ' . $contentType);
                header('Access-Control-Allow-Origin: *');
                readfile($filePath);
            } else {
                http_response_code(404);
                echo "Archivo no encontrado";
            }
            exit;
            break;

        // ========== BANNERS (DINÁMICO) ==========
        case 'banners':
            require_once __DIR__ . '/../controllers/BannersController.php';
            $controller = new BannersController($db);

            if ($method === 'GET') {
                // GET /api/banners - Listar (admin=todos, public=solo activos)
                $onlyActive = !isset($_GET['admin']); // Si no dice admin, es publico
                echo json_encode($controller->listar($onlyActive));

            } elseif ($method === 'POST') {
                // POST /api/banners - Subir nuevo
                echo json_encode($controller->subir($_FILES));

            } elseif ($method === 'PUT' && $id) {
                // PUT /api/banners/{id} - Activar/Desactivar
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->actualizar($id, $data));

            } elseif ($method === 'DELETE' && $id) {
                // DELETE /api/banners/{id}
                echo json_encode($controller->eliminar($id));

            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint banner no encontrado"]);
            }
            break;

        // ========== ADMIN ==========
        case 'admin':
            require_once __DIR__ . '/../controllers/AdminController.php';
            $controller = new AdminController($db);

            // RUTAS: /api/admin/branding/...
            if ($id === 'branding') {
                require_once __DIR__ . '/../controllers/BrandingController.php';
                $brandingController = new BrandingController($db);

                if ($action === 'logos' && $method === 'GET') {
                    // GET /api/admin/branding/logos?tipo=card
                    $tipo = $_GET['tipo'] ?? null;
                    echo json_encode($brandingController->getAllLogos($tipo));
                } elseif ($action === 'active' && $method === 'GET') {
                    // GET /api/admin/branding/active
                    echo json_encode($brandingController->getActiveLogos());
                } elseif ($action === 'upload' && $method === 'POST') {
                    // POST /api/admin/branding/upload
                    echo json_encode($brandingController->uploadLogo($_FILES, $_POST));
                } elseif ($action === 'set-active' && $method === 'POST') {
                    // POST /api/admin/branding/set-active
                    $data = json_decode(file_get_contents("php://input"), true);
                    $logoId = $data['id'] ?? null;
                    echo json_encode($brandingController->setActiveLogo($logoId));
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "Sub-recurso de branding no encontrado"]);
                }
                break;
            }

            if ($method === 'GET' && $id === 'estadisticas') {
                // GET /api/admin/estadisticas - Estadísticas del dashboard
                echo json_encode($controller->getEstadisticas());

            } elseif ($method === 'GET' && $id === 'peleadores-pendientes') {
                // GET /api/admin/peleadores-pendientes - Lista de peleadores pendientes
                echo json_encode($controller->getPeleadoresPendientes());

            } elseif ($method === 'GET' && $id === 'peleadores') {
                // GET /api/admin/peleadores?filtro=todos|pendiente|aprobado|rechazado
                $filtro = $_GET['filtro'] ?? 'todos';
                echo json_encode($controller->getPeleadores($filtro));

            } elseif ($method === 'PUT' && $id === 'peleadores' && $action) {
                // PUT /api/admin/peleadores/{id} - Aprobar/rechazar peleador
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->cambiarEstadoPeleador($action, $data));

            } elseif ($method === 'PATCH' && $id === 'peleadores' && $action) {
                // PATCH /api/admin/peleadores/{id} - Editar datos del peleador
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->editPeleador($action, $data));

            } elseif ($method === 'DELETE' && $id === 'peleadores' && $action) {
                // DELETE /api/admin/peleadores/{id} - Eliminar peleador
                echo json_encode($controller->deletePeleador($action));

            } elseif ($method === 'GET' && $id === 'clubs') {
                // GET /api/admin/clubs - Todos los clubs
                echo json_encode($controller->getAllClubs());

            } elseif ($method === 'POST' && $id === 'clubs') {
                // POST /api/admin/clubs - Crear nuevo club
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->crearClub($data));

            } elseif ($method === 'GET' && $id === 'buscar-usuario' && isset($_GET['dni'])) {
                // GET /api/admin/buscar-usuario?dni=XXX - Buscar usuario por DNI
                echo json_encode($controller->buscarUsuarioPorDNI($_GET['dni']));

            } elseif ($method === 'POST' && $id === 'asignar-duenio') {
                // POST /api/admin/asignar-duenio - Asignar dueño a club
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->asignarDuenioClub($data));

            } elseif ($method === 'GET' && $id === 'inscripciones') {
                // GET /api/admin/inscripciones?estado_pago=pendiente&evento_id=1
                $filters = [];
                if (isset($_GET['estado_pago'])) {
                    $filters['estado_pago'] = $_GET['estado_pago'];
                }
                if (isset($_GET['evento_id'])) {
                    $filters['evento_id'] = $_GET['evento_id'];
                }
                echo json_encode($controller->getInscripciones($filters));

            } elseif ($method === 'GET' && $id === 'inscripciones-pendientes') {
                // GET /api/admin/inscripciones-pendientes - Inscripciones sin pagar
                echo json_encode($controller->getInscripcionesPendientes());

            } elseif ($method === 'POST' && $id === 'inscripciones') {
                // POST /api/admin/inscripciones - Crear nueva inscripción
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->crearInscripcion($data));

            } elseif ($method === 'PUT' && $id === 'inscripciones' && $action) {
                // PUT /api/admin/inscripciones/{id} - Confirmar pago
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->confirmarPago($action, $data));

            } elseif ($method === 'PUT' && $id === 'eventos' && $action === 'precio') {
                // PUT /api/admin/eventos/{id}/precio - Actualizar precio inscripción
                $data = json_decode(file_get_contents("php://input"), true);
                $evento_id = isset($_GET['evento_id']) ? $_GET['evento_id'] : null;
                echo json_encode($controller->actualizarPrecioEvento($evento_id, $data));

            } elseif ($method === 'GET' && $id === 'metodos-pago') {
                // GET /api/admin/metodos-pago?activo=1
                $filters = [];
                if (isset($_GET['activo'])) {
                    $filters['activo'] = $_GET['activo'];
                }
                echo json_encode($controller->getMetodosPago($filters));

            } elseif ($method === 'POST' && $id === 'metodos-pago' && $action === 'upload-qr') {
                // POST /api/admin/metodos-pago/upload-qr
                echo json_encode($controller->uploadQRImage($_FILES));

            } elseif ($method === 'POST' && $id === 'metodos-pago' && !$action) {
                // POST /api/admin/metodos-pago
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->crearMetodoPago($data));

            } elseif ($method === 'PUT' && $id === 'metodos-pago' && $action) {
                // PUT /api/admin/metodos-pago/{id}
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->actualizarMetodoPago($action, $data));

            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint admin no encontrado"]);
            }
            break;

        // ========== BOLETOS ==========
        case 'boletos':
            // Prevenir ejecución del ruteo interno del controlador
            define('SKIP_ROUTING', true);
            require_once __DIR__ . '/../controllers/BoletosController.php';
            $controller = new BoletosController($db);

            if ($method === 'GET' && $id === 'tipos-boleto' && $action) {
                // GET /api/boletos/tipos-boleto/{eventoId}
                $controller->getTiposBoleto($action);

            } elseif ($method === 'POST' && $id === 'comprar') {
                // POST /api/boletos/comprar
                $controller->crearSolicitudCompra();

            } elseif ($method === 'GET' && $id === 'pendientes') {
                // GET /api/boletos/pendientes
                $controller->getPagosPendientes();

            } elseif ($method === 'PUT' && $id && $action === 'validar') {
                // PUT /api/boletos/{id}/validar
                $controller->validarPago($id);

            } elseif ($method === 'POST' && $id === 'validar-qr') {
                // POST /api/boletos/validar-qr
                $controller->validarQR();

            } elseif ($method === 'POST' && $id && $action === 'comprobante') {
                // POST /api/boletos/{id}/comprobante
                $controller->subirComprobante($id);

            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint boletos no encontrado: $method $id/$action"]);
            }
            break;

        // ========== TIPOS DE BOLETO (ADMIN) ==========
        case 'tipos-boleto':
            require_once __DIR__ . '/../controllers/TiposBoletosController.php';
            $controller = new TiposBoletosController($db);

            if ($method === 'POST' && $id === 'crear') {
                // POST /api/tipos-boleto/crear
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->crear($data));

            } elseif ($method === 'PUT' && $id === 'editar' && $action) {
                // PUT /api/tipos-boleto/editar/{id}
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->editar($action, $data));

            } elseif ($method === 'PUT' && $id === 'activar' && $action) {
                // PUT /api/tipos-boleto/activar/{id}
                echo json_encode($controller->activar($action));

            } elseif ($method === 'DELETE' && $id) {
                // DELETE /api/tipos-boleto/{id}
                echo json_encode($controller->desactivar($id));

            } elseif ($method === 'GET' && $id === 'evento' && $action) {
                // GET /api/tipos-boleto/evento/{evento_id}
                echo json_encode($controller->getTiposPorEvento($action));

            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint tipos-boleto no encontrado"]);
            }
            break;

        // ========== VERIFICAR MIGRACIÓN (temporal) ==========
        case 'verify-migration':
            if ($method === 'GET') {
                $stmt = $db->query("DESCRIBE peleadores");
                $peleadores_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $stmt = $db->query("DESCRIBE usuarios");
                $usuarios_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Verificar últimos registros
                $stmt = $db->query("SELECT id, nombre, apellidos, email, fecha_registro FROM usuarios ORDER BY id DESC LIMIT 5");
                $ultimos_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $stmt = $db->query("SELECT id, usuario_id, apodo, genero, documento_identidad, fecha_inscripcion FROM peleadores ORDER BY id DESC LIMIT 5");
                $ultimos_peleadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $genero_existe = false;
                $apellidos_existe = false;

                foreach ($peleadores_columns as $col) {
                    if ($col['Field'] === 'genero') {
                        $genero_existe = $col;
                    }
                }

                foreach ($usuarios_columns as $col) {
                    if ($col['Field'] === 'apellidos') {
                        $apellidos_existe = $col;
                    }
                }

                echo json_encode([
                    'success' => true,
                    'genero_column' => $genero_existe ?: 'NO EXISTE',
                    'apellidos_column' => $apellidos_existe ?: 'NO EXISTE',
                    'peleadores_columns' => array_column($peleadores_columns, 'Field'),
                    'usuarios_columns' => array_column($usuarios_columns, 'Field'),
                    'ultimos_usuarios' => $ultimos_usuarios,
                    'ultimos_peleadores' => $ultimos_peleadores
                ], JSON_PRETTY_PRINT);
            }
            break;

        // ========== TEST PROOF (HTML) ==========
        case 'test-proof':
            $file = __DIR__ . '/proof.html';
            if (file_exists($file)) {
                header('Content-Type: text/html');
                readfile($file);
                exit;
            } else {
                echo "Archivo proof.html no encontrado en " . $file;
            }
            break;

        // ========== ANUNCIOS ==========
        case 'anuncios':
            require_once __DIR__ . '/../controllers/AnunciosController.php';
            $controller = new AnunciosController($db);

            if ($method === 'GET' && !$id) {
                // GET /api/anuncios - Listar (publico=1 para activos, sin param para admin)
                $onlyActive = isset($_GET['publico']);
                $eventoId = $_GET['evento_id'] ?? null;
                $limit = $_GET['limit'] ?? null;
                echo json_encode($controller->listar($onlyActive, $eventoId, $limit));

            } elseif ($method === 'GET' && $id) {
                // GET /api/anuncios/{id}
                echo json_encode($controller->obtenerPorId($id));

            } elseif ($method === 'POST' && !$id) {
                // POST /api/anuncios - Crear anuncio
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                if (!empty($_FILES) || strpos($contentType, 'multipart/form-data') !== false) {
                    $data = $_POST;
                } else {
                    $data = json_decode(file_get_contents("php://input"), true);
                }
                echo json_encode($controller->crear($data, $_FILES));

            } elseif ($method === 'PUT' && $id) {
                // PUT /api/anuncios/{id} - Actualizar
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->actualizar($id, $data));

            } elseif ($method === 'DELETE' && $id) {
                // DELETE /api/anuncios/{id}
                echo json_encode($controller->eliminar($id));

            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint anuncios no encontrado"]);
            }
            break;

        // ========== TELEGRAM WEBHOOK ==========
        case 'telegram-webhook':
            require_once __DIR__ . '/../controllers/TelegramWebhookController.php';
            $controller = new TelegramWebhookController($db);

            if ($method === 'POST') {
                $payload = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->handleWebhook($payload));
            } else {
                http_response_code(405);
                echo json_encode(["error" => "Metodo no permitido"]);
            }
            break;

        // ========== DEFAULT ==========
        default:
            http_response_code(404);
            echo json_encode([
                "error" => "Recurso no encontrado",
                "resource" => $resource
            ]);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error interno del servidor",
        "message" => $e->getMessage()
    ]);
}
