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

// Solo cargar Database.php si no se llamó desde index.php
if (!defined('SKIP_ROUTING')) {
    require_once __DIR__ . '/../config/Database.php';
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
            // Consulta directa para diagnóstico
            $stmt = $this->db->prepare("
                SELECT
                    id,
                    evento_id,
                    nombre,
                    precio,
                    cantidad_total,
                    cantidad_vendida,
                    (cantidad_total - cantidad_vendida) as cantidad_disponible,
                    color_hex,
                    descripcion,
                    activo
                FROM tipos_boleto
                WHERE evento_id = ? AND activo = 1
                ORDER BY id ASC
            ");
            $stmt->execute([$eventoId]);
            $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($tipos) === 0) {
                $stmtCheck = $this->db->prepare("SELECT id, nombre, estado FROM eventos WHERE id = ?");
                $stmtCheck->execute([$eventoId]);
                $evento = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                
                if (!$evento) {
                    $msg = "Error: El evento con ID #$eventoId no existe en la base de datos.";
                } else {
                    $msg = "El evento '{$evento['nombre']}' existe (Estado: {$evento['estado']}), pero no tiene ningún tipo de boleto creado en la tabla 'tipos_boleto'.";
                }

                echo json_encode([
                    'success' => false,
                    'message' => $msg,
                    'data' => []
                ]);
                return;
            }

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
                'message' => "Error de base de datos: " . $e->getMessage()
            ]);
        }
    }

    /**
     * POST /boletos/comprar
     * Crear solicitud de compra de boleto
     */
    public function crearSolicitudCompra() {
        // Iniciar buffer para capturar salidas inesperadas
        ob_start();
        // Asegurar respuesta JSON
        header('Content-Type: application/json');
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

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

            // Validar teléfono
            $telefono = preg_replace('/\s+/', '', $data['telefono']);
            
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

            // Buscar el ID del método de pago basado en el código enviado
            $metodoPagoId = null;
            $metodoCodigo = $data['metodo_pago'] ?? 'yape';
            try {
                $stmtMetodo = $this->db->prepare("SELECT id FROM metodos_pago WHERE codigo = ? LIMIT 1");
                $stmtMetodo->execute([$metodoCodigo]);
                $metodoRow = $stmtMetodo->fetch(PDO::FETCH_ASSOC);
                if ($metodoRow) {
                    $metodoPagoId = $metodoRow['id'];
                }
            } catch (Exception $metodoEx) {
                error_log("Error buscando ID de método de pago: " . $metodoEx->getMessage());
            }

            // Insertar solicitud de compra con vínculo profesional
        $stmt = $this->db->prepare("
            INSERT INTO boletos_vendidos (
                evento_id, tipo_boleto_id, vendedor_id, usuario_id,
                comprador_nombres_apellidos, comprador_telefono, comprador_dni,
                cantidad, precio_total, codigo_qr,
                metodo_pago, metodo_pago_id, estado_pago, estado_boleto
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', 'activo')
        ");

        $stmt->execute([
            $data['evento_id'],
            $data['tipo_boleto_id'],
            $data['vendedor_id'] ?? null,
            $data['usuario_id'] ?? null, // Nuevo campo
            $data['nombres_apellidos'],
            $telefono,
            $data['dni'],
            $data['cantidad'],
            $precioTotal,
            $codigoQR,
            $metodoCodigo,
            $metodoPagoId
        ]);

            $boletoId = $this->db->lastInsertId();

            // Incrementar cantidad vendida
            $stmt = $this->db->prepare("
                UPDATE tipos_boleto
                SET cantidad_vendida = cantidad_vendida + ?
                WHERE id = ?
            ");
            $stmt->execute([$data['cantidad'], $data['tipo_boleto_id']]);

            // Generar PDF inmediatamente
            $pdfUrl = null;
            try {
                require_once __DIR__ . '/../services/PdfService.php';
                $pdfService = new PdfService($this->db);
                
                // Obtener datos completos para el PDF
                $stmtBoleto = $this->db->prepare("
                    SELECT 
                        bv.id, bv.evento_id, bv.tipo_boleto_id,
                        bv.comprador_nombres_apellidos, bv.comprador_telefono,
                        bv.comprador_dni, bv.cantidad, bv.precio_total,
                        bv.codigo_qr, bv.metodo_pago,
                        tb.nombre as tipo_boleto, tb.color_hex,
                        e.nombre as evento_nombre, e.fecha as evento_fecha,
                        e.hora as evento_hora, e.direccion as evento_direccion
                    FROM boletos_vendidos bv
                    JOIN tipos_boleto tb ON bv.tipo_boleto_id = tb.id
                    JOIN eventos e ON bv.evento_id = e.id
                    WHERE bv.id = ?
                ");
                $stmtBoleto->execute([$boletoId]);
                $boletoData = $stmtBoleto->fetch(PDO::FETCH_ASSOC);

                if ($boletoData) {
                    $pdfResult = $pdfService->generarBoletoPdf($boletoData);
                    if ($pdfResult['success']) {
                        $pdfUrl = $pdfResult['url'];
                    }
                }
            } catch (Exception $pdfEx) {
                $errorMsg = "Error generando PDF inicial: " . $pdfEx->getMessage();
                error_log($errorMsg);
                file_put_contents(__DIR__ . '/../files/debug_pdf_error.txt', $errorMsg . "\n", FILE_APPEND);
            }

            $response = [
                'success' => true,
                'message' => 'Solicitud de compra creada. Procede a realizar el pago.',
                'data' => [
                    'boleto_id' => $boletoId,
                    'codigo_qr' => $codigoQR,
                    'precio_total' => $precioTotal,
                    'pdf_url' => $pdfUrl,
                    'mensaje_pago' => "Yapea S/$precioTotal al número 934-567-890 y sube tu comprobante"
                ]
            ];

            // Limpiar buffer
            ob_clean();
            echo json_encode($response);
            exit;

        } catch (Throwable $e) {
            http_response_code(400);
            error_log("Error en crearSolicitudCompra: " . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
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

            $pdfResult = null;
            if ($accion === 'aprobar') {
                // Obtener datos completos del boleto para generar PDF
                $stmtBoleto = $this->db->prepare("
                    SELECT
                        bv.id, bv.evento_id, bv.tipo_boleto_id,
                        bv.comprador_nombres_apellidos, bv.comprador_telefono,
                        bv.comprador_dni, bv.cantidad, bv.precio_total,
                        bv.codigo_qr, bv.metodo_pago,
                        tb.nombre as tipo_boleto, tb.color_hex,
                        e.nombre as evento_nombre, e.fecha as evento_fecha,
                        e.hora as evento_hora, e.direccion as evento_direccion
                    FROM boletos_vendidos bv
                    JOIN tipos_boleto tb ON bv.tipo_boleto_id = tb.id
                    JOIN eventos e ON bv.evento_id = e.id
                    WHERE bv.id = ?
                ");
                $stmtBoleto->execute([$boletoId]);
                $boletoData = $stmtBoleto->fetch(PDO::FETCH_ASSOC);

                if ($boletoData) {
                    require_once __DIR__ . '/../services/PdfService.php';
                    $pdfService = new PdfService($this->db);
                    $pdfResult = $pdfService->generarBoletoPdf($boletoData);
                }

                $mensaje = "Pago aprobado.";
                if ($pdfResult && $pdfResult['success']) {
                    $mensaje .= " PDF del boleto generado.";
                }
            } else {
                $mensaje = "Pago rechazado.";
            }

            $response = [
                'success' => true,
                'message' => $mensaje
            ];
            if ($pdfResult && $pdfResult['success']) {
                $response['pdf_url'] = $pdfResult['url'];
            }
            echo json_encode($response);

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
    public function getMisBoletos($usuarioId) {
        header('Content-Type: application/json');
        
        try {
            if (!$usuarioId) {
                throw new Exception("ID de usuario requerido");
            }

            // Obtener boletos con datos del evento y tipo, y URL del PDF si existe
            $query = "
                SELECT 
                    bv.id, bv.codigo_qr, bv.cantidad, bv.precio_total, 
                    bv.estado_pago, bv.fecha_compra,
                    e.nombre as evento, e.fecha as evento_fecha, e.hora as evento_hora,
                    tb.nombre as tipo_boleto, tb.color_hex,
                    (SELECT file_url FROM pdf_documents 
                     WHERE entity_type = 'boleto_vendido' 
                       AND document_type = 'boleto_ticket' 
                       AND entity_id = bv.id 
                     ORDER BY id DESC LIMIT 1) as pdf_url
                FROM boletos_vendidos bv
                JOIN eventos e ON bv.evento_id = e.id
                JOIN tipos_boleto tb ON bv.tipo_boleto_id = tb.id
                WHERE bv.usuario_id = ?
                ORDER BY bv.fecha_compra DESC
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$usuarioId]);
            $boletos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatear datos
            foreach ($boletos as &$boleto) {
                // Si no hay PDF generado pero está verificado, usamos el endpoint para intentar generarlo/recuperarlo
                // Pero idealmente debería tenerlo. Si es NULL, no tendrá link.
                
                // Formatear fecha
                $boleto['fecha_fmt'] = date('d/m/Y', strtotime($boleto['evento_fecha']));
                $boleto['hora_fmt'] = date('H:i', strtotime($boleto['evento_hora']));
            }

            echo json_encode([
                'success' => true,
                'boletos' => $boletos
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

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
     * GET /boletos/:id/pdf
     * Obtener URL del PDF generado para un boleto
     */
    public function obtenerPdfBoleto($boletoId) {
        try {
            $stmt = $this->db->prepare("
                SELECT file_url, verification_token
                FROM pdf_documents
                WHERE document_type = 'boleto_ticket'
                  AND entity_type = 'boleto_vendido'
                  AND entity_id = ?
                ORDER BY id DESC
                LIMIT 1
            ");
            $stmt->execute([$boletoId]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$doc) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'PDF no encontrado para este boleto'
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'pdf_url' => $doc['file_url'],
                'token' => $doc['verification_token']
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * GET /verificar-boleto/:token
     * Renderizar página HTML de verificación de boleto (QR scan)
     */
    public function verificarBoleto($token) {
        try {
            if (empty($token)) {
                throw new Exception('Token requerido');
            }

            // Buscar documento PDF por token
            $stmt = $this->db->prepare("
                SELECT pd.entity_id, pd.file_url
                FROM pdf_documents pd
                WHERE pd.verification_token = ?
                  AND pd.document_type = 'boleto_ticket'
                LIMIT 1
            ");
            $stmt->execute([$token]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$doc) {
                // Renderizar vista con error
                $valid = false;
                $data = null;
                $errorMsg = 'Boleto no encontrado o token inválido';
                header('Content-Type: text/html; charset=UTF-8');
                require __DIR__ . '/../views/verificar_boleto.php';
                return;
            }

            // Obtener datos completos del boleto
            $stmtBoleto = $this->db->prepare("
                SELECT
                    bv.id, bv.comprador_nombres_apellidos, bv.comprador_dni,
                    bv.comprador_telefono, bv.cantidad, bv.precio_total,
                    bv.codigo_qr, bv.estado_pago, bv.estado_boleto,
                    bv.fecha_compra, bv.fecha_validacion, bv.fecha_uso,
                    tb.nombre as tipo_boleto, tb.color_hex,
                    e.nombre as evento_nombre, e.fecha as evento_fecha,
                    e.hora as evento_hora, e.direccion as evento_direccion
                FROM boletos_vendidos bv
                JOIN tipos_boleto tb ON bv.tipo_boleto_id = tb.id
                JOIN eventos e ON bv.evento_id = e.id
                WHERE bv.id = ?
            ");
            $stmtBoleto->execute([$doc['entity_id']]);
            $boleto = $stmtBoleto->fetch(PDO::FETCH_ASSOC);

            if (!$boleto) {
                $valid = false;
                $data = null;
                $errorMsg = 'Datos del boleto no encontrados';
                header('Content-Type: text/html; charset=UTF-8');
                require __DIR__ . '/../views/verificar_boleto.php';
                return;
            }

            $valid = true;
            $data = $boleto;
            $errorMsg = null;

            // Staff session check
            session_start();
            $staffLoggedIn = !empty($_SESSION['staff_logged_in']);
            $staffName = $_SESSION['staff_name'] ?? '';

            header('Content-Type: text/html; charset=UTF-8');
            require __DIR__ . '/../views/verificar_boleto.php';

        } catch (Exception $e) {
            header('Content-Type: text/html; charset=UTF-8');
            $valid = false;
            $data = null;
            $errorMsg = $e->getMessage();
            require __DIR__ . '/../views/verificar_boleto.php';
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


?>
