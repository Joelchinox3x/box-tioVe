<?php
/**
 * Controlador de Boletos
 * Maneja todas las operaciones relacionadas con la venta de boletos
 */

require_once __DIR__ . '/../config/Config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo cargar database.php si no se llamó desde index.php
if (!defined('SKIP_ROUTING')) {
    require_once __DIR__ . '/../config/database.php';
}

class BoletosController {
    private $db;

    public function __construct($dbConnection = null) {
        // Usar conexión pasada o crear nueva
        $this->db = $dbConnection ?? getDB();
    }

    /**
     * GET /tipos-boleto/:eventoId
     * Obtener tipos de boleto disponibles para un evento
     */
    public function getTiposBoleto($eventoId) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    id,
                    evento_id,
                    tipo_nombre as nombre,
                    precio,
                    cantidad_total,
                    cantidad_vendida,
                    cantidad_disponible,
                    color_hex,
                    descripcion,
                    activo
                FROM vista_boletos_disponibles
                WHERE evento_id = ? AND activo = 1
                ORDER BY id ASC
            ");
            $stmt->execute([$eventoId]);
            $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convertir tipos de datos
            foreach ($tipos as &$tipo) {
                $tipo['id'] = (int)$tipo['id'];
                $tipo['evento_id'] = (int)$tipo['evento_id'];
                $tipo['precio'] = (float)$tipo['precio'];
                $tipo['cantidad_total'] = (int)$tipo['cantidad_total'];
                $tipo['cantidad_vendida'] = (int)$tipo['cantidad_vendida'];
                $tipo['cantidad_disponible'] = (int)$tipo['cantidad_disponible'];
                $tipo['activo'] = (bool)$tipo['activo'];
            }

            echo json_encode([
                'success' => true,
                'data' => $tipos
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * POST /boletos/comprar
     * Crear solicitud de compra de boleto
     */
    public function crearSolicitudCompra() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            // Validar datos requeridos
            $required = ['evento_id', 'tipo_boleto_id', 'nombres_apellidos', 'telefono', 'dni', 'cantidad'];
            foreach ($required as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Campo requerido: $field");
                }
            }

            // Validar DNI (8 dígitos)
            if (!preg_match('/^\d{8}$/', $data['dni'])) {
                throw new Exception("El DNI debe tener 8 dígitos numéricos");
            }

            // Validar teléfono (9 dígitos empezando con 9)
            $telefono = preg_replace('/\s+/', '', $data['telefono']);
            if (!preg_match('/^9\d{8}$/', $telefono)) {
                throw new Exception("El teléfono debe empezar con 9 y tener 9 dígitos");
            }

            // Verificar disponibilidad
            $stmt = $this->db->prepare("
                SELECT cantidad_total, cantidad_vendida, precio
                FROM tipos_boleto
                WHERE id = ? AND evento_id = ? AND activo = 1
            ");
            $stmt->execute([$data['tipo_boleto_id'], $data['evento_id']]);
            $tipoBoleto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tipoBoleto) {
                throw new Exception("Tipo de boleto no encontrado o inactivo");
            }

            $disponibles = $tipoBoleto['cantidad_total'] - $tipoBoleto['cantidad_vendida'];
            if ($disponibles < $data['cantidad']) {
                throw new Exception("Solo quedan $disponibles boletos disponibles");
            }

            // Calcular precio total
            $precioTotal = $tipoBoleto['precio'] * $data['cantidad'];

            // Generar código QR único
            $codigoQR = $this->generarCodigoQR($data['evento_id']);

            // Insertar solicitud de compra
            $stmt = $this->db->prepare("
                INSERT INTO boletos_vendidos (
                    evento_id, tipo_boleto_id, vendedor_id,
                    comprador_nombres_apellidos, comprador_telefono, comprador_dni,
                    cantidad, precio_total, codigo_qr,
                    metodo_pago, estado_pago, estado_boleto
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', 'activo')
            ");

            $stmt->execute([
                $data['evento_id'],
                $data['tipo_boleto_id'],
                $data['vendedor_id'] ?? null,
                $data['nombres_apellidos'],
                $telefono,
                $data['dni'],
                $data['cantidad'],
                $precioTotal,
                $codigoQR,
                $data['metodo_pago'] ?? 'yape'
            ]);

            $boletoId = $this->db->lastInsertId();

            // Incrementar cantidad vendida
            $stmt = $this->db->prepare("
                UPDATE tipos_boleto
                SET cantidad_vendida = cantidad_vendida + ?
                WHERE id = ?
            ");
            $stmt->execute([$data['cantidad'], $data['tipo_boleto_id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Solicitud de compra creada. Procede a realizar el pago.',
                'data' => [
                    'boleto_id' => $boletoId,
                    'codigo_qr' => $codigoQR,
                    'precio_total' => $precioTotal,
                    'mensaje_pago' => "Yapea S/$precioTotal al número 934-567-890 y sube tu comprobante"
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * POST /boletos/:id/comprobante
     * Subir comprobante de pago
     */
    public function subirComprobante($boletoId) {
        try {
            // Verificar que el boleto existe
            $stmt = $this->db->prepare("SELECT id FROM boletos_vendidos WHERE id = ?");
            $stmt->execute([$boletoId]);
            if (!$stmt->fetch()) {
                throw new Exception("Boleto no encontrado");
            }

            // Aquí normalmente subirías la imagen a un servidor/S3
            // Por ahora simularemos con una URL
            if (isset($_FILES['comprobante'])) {
                // Lógica de subida de archivo
                $uploadDir = __DIR__ . '/../../uploads/comprobantes/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileName = $boletoId . '_' . time() . '_' . basename($_FILES['comprobante']['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $targetPath)) {
                    $comprobanteUrl = Config::getFileUrl($fileName, 'comprobantes');
                } else {
                    throw new Exception("Error al subir el comprobante");
                }
            } else {
                throw new Exception("No se recibió ningún archivo");
            }

            // Actualizar boleto con URL del comprobante
            $stmt = $this->db->prepare("
                UPDATE boletos_vendidos
                SET comprobante_pago = ?
                WHERE id = ?
            ");
            $stmt->execute([$comprobanteUrl, $boletoId]);

            echo json_encode([
                'success' => true,
                'message' => 'Comprobante subido. En breve validaremos tu pago.',
                'comprobante_url' => $comprobanteUrl
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /boletos/pendientes
     * Obtener boletos con pago pendiente (para admin)
     */
    public function getPagosPendientes() {
        try {
            $stmt = $this->db->query("
                SELECT
                    bv.id,
                    bv.comprador_nombres_apellidos,
                    bv.comprador_telefono,
                    bv.comprador_dni,
                    bv.cantidad,
                    bv.precio_total,
                    bv.comprobante_pago,
                    bv.fecha_compra,
                    tb.nombre as tipo_boleto,
                    e.nombre as evento_nombre
                FROM boletos_vendidos bv
                JOIN tipos_boleto tb ON bv.tipo_boleto_id = tb.id
                JOIN eventos e ON bv.evento_id = e.id
                WHERE bv.estado_pago = 'pendiente'
                ORDER BY bv.fecha_compra ASC
            ");

            $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $pagos
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * PUT /boletos/:id/validar
     * Aprobar o rechazar un pago (admin)
     */
    public function validarPago($boletoId) {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $accion = $data['accion'] ?? ''; // 'aprobar' o 'rechazar'

            if (!in_array($accion, ['aprobar', 'rechazar'])) {
                throw new Exception("Acción inválida");
            }

            $nuevoEstado = $accion === 'aprobar' ? 'verificado' : 'rechazado';

            $stmt = $this->db->prepare("
                UPDATE boletos_vendidos
                SET estado_pago = ?,
                    fecha_validacion = CURRENT_TIMESTAMP,
                    observaciones = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $nuevoEstado,
                $data['observaciones'] ?? null,
                $boletoId
            ]);

            if ($accion === 'aprobar') {
                // TODO: Aquí se generaría el PDF del boleto y se enviaría por WhatsApp
                $mensaje = "Pago aprobado. El boleto será generado y enviado.";
            } else {
                $mensaje = "Pago rechazado.";
            }

            echo json_encode([
                'success' => true,
                'message' => $mensaje
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * POST /boletos/validar-qr
     * Validar QR en la entrada del evento
     */
    public function validarQR() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $codigoQR = $data['codigo_qr'] ?? '';

            if (empty($codigoQR)) {
                throw new Exception("Código QR requerido");
            }

            // Buscar boleto
            $stmt = $this->db->prepare("
                SELECT
                    bv.*,
                    tb.nombre as tipo_boleto,
                    e.nombre as evento_nombre,
                    e.fecha as evento_fecha
                FROM boletos_vendidos bv
                JOIN tipos_boleto tb ON bv.tipo_boleto_id = tb.id
                JOIN eventos e ON bv.evento_id = e.id
                WHERE bv.codigo_qr = ?
            ");
            $stmt->execute([$codigoQR]);
            $boleto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$boleto) {
                throw new Exception("BOLETO INVÁLIDO");
            }

            if ($boleto['estado_pago'] !== 'verificado') {
                throw new Exception("PAGO NO VERIFICADO");
            }

            if ($boleto['estado_boleto'] === 'usado') {
                throw new Exception("BOLETO YA UTILIZADO el " . $boleto['fecha_uso']);
            }

            if ($boleto['estado_boleto'] === 'cancelado') {
                throw new Exception("BOLETO CANCELADO");
            }

            // Marcar como usado
            $stmt = $this->db->prepare("
                UPDATE boletos_vendidos
                SET estado_boleto = 'usado',
                    fecha_uso = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$boleto['id']]);

            echo json_encode([
                'success' => true,
                'valido' => true,
                'message' => '✅ BOLETO VÁLIDO - BIENVENIDO',
                'data' => [
                    'comprador' => $boleto['comprador_nombres_apellidos'],
                    'dni' => $boleto['comprador_dni'],
                    'tipo_boleto' => $boleto['tipo_boleto'],
                    'evento' => $boleto['evento_nombre']
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'valido' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generar código QR único
     */
    private function generarCodigoQR($eventoId) {
        // Obtener siglas del evento
        $stmt = $this->db->prepare("SELECT nombre FROM eventos WHERE id = ?");
        $stmt->execute([$eventoId]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);

        // Generar siglas (primeras letras de cada palabra)
        $palabras = explode(' ', $evento['nombre']);
        $siglas = '';
        foreach ($palabras as $palabra) {
            if (strlen($palabra) > 2) { // Solo palabras significativas
                $siglas .= strtoupper(substr($palabra, 0, 1));
            }
        }

        $año = date('Y');

        // Obtener último ID de boleto para este evento
        $stmt = $this->db->prepare("
            SELECT MAX(id) as ultimo_id FROM boletos_vendidos WHERE evento_id = ?
        ");
        $stmt->execute([$eventoId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $numero = ($result['ultimo_id'] ?? 0) + 1;

        // Formato: BOX-[SIGLAS]-[AÑO]-[NUMERO]
        return sprintf("BOX-%s-%s-%06d", $siglas, $año, $numero);
    }
}

// Ruteo interno solo si no es llamado desde index.php
if (!defined('SKIP_ROUTING')) {
    $controller = new BoletosController();
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_GET['path'] ?? '';

    // Parsear path
    $segments = explode('/', trim($path, '/'));

    switch ($method) {
        case 'GET':
            if ($segments[0] === 'tipos-boleto' && isset($segments[1])) {
                $controller->getTiposBoleto($segments[1]);
            } elseif ($segments[0] === 'pendientes') {
                $controller->getPagosPendientes();
            }
            break;

        case 'POST':
            if ($segments[0] === 'comprar') {
                $controller->crearSolicitudCompra();
            } elseif ($segments[0] === 'validar-qr') {
                $controller->validarQR();
            } elseif (isset($segments[0]) && $segments[1] === 'comprobante') {
                $controller->subirComprobante($segments[0]);
            }
            break;

        case 'PUT':
            if (isset($segments[0]) && $segments[1] === 'validar') {
                $controller->validarPago($segments[0]);
            }
            break;
    }
}
?>
