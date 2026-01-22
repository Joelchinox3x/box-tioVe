<?php
/**
 * API Principal - BoxEvent App
 * Punto de entrada para todas las peticiones
 */

// Headers CORS
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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
        case 'peleadores':
            require_once __DIR__ . '/../controllers/PeleadoresController.php';
            $controller = new PeleadoresController($db);

            if ($method === 'GET' && !$id) {
                // GET /api/peleadores - Listar todos
                $filtro = $_GET['filtro'] ?? 'todos'; // todos, populares, club
                $club = $_GET['club'] ?? null;
                echo json_encode($controller->listar($filtro, $club));

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

        // ========== ADMIN ==========
        case 'admin':
            require_once __DIR__ . '/../controllers/AdminController.php';
            $controller = new AdminController($db);

            if ($method === 'GET' && $id === 'estadisticas') {
                // GET /api/admin/estadisticas - Estadísticas del dashboard
                echo json_encode($controller->getEstadisticas());

            } elseif ($method === 'GET' && $id === 'peleadores-pendientes') {
                // GET /api/admin/peleadores-pendientes - Lista de peleadores pendientes
                echo json_encode($controller->getPeleadoresPendientes());

            } elseif ($method === 'PUT' && $id === 'peleadores' && $action) {
                // PUT /api/admin/peleadores/{id} - Aprobar/rechazar peleador
                $data = json_decode(file_get_contents("php://input"), true);
                echo json_encode($controller->cambiarEstadoPeleador($action, $data));

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

            } else {
                http_response_code(404);
                echo json_encode(["error" => "Endpoint admin no encontrado"]);
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
