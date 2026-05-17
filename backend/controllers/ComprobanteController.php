<?php
/**
 * ComprobanteController - Controlador para generar comprobantes PDF
 */

require_once __DIR__ . '/../services/PdfService.php';
require_once __DIR__ . '/../config/Config.php';

class ComprobanteController {
    private $db;
    private $pdfService;
    private const DOC_TYPE_FIGHTER_COMPROBANTE = 'fighter_inscripcion_comprobante';

    public function __construct($db) {
        $this->db = $db;
        $this->pdfService = new PdfService($db);
    }

    /**
     * Generar comprobante de pago en PDF
     * POST /api/generar-comprobante
     * Requiere: inscripcion_id
     */
    public function generar() {
        try {
            // Obtener datos del body JSON
            $input = json_decode(file_get_contents('php://input'), true);
            $inscripcionId = $input['inscripcion_id'] ?? $_POST['inscripcion_id'] ?? null;

            // Debug log
            error_log('Generar comprobante - ID recibido: ' . ($inscripcionId ?? 'NULL'));

            if (!$inscripcionId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de inscripción requerido',
                    'debug' => ['input' => $input, 'post' => $_POST]
                ]);
                return;
            }

            // Obtener datos completos de la inscripción
            $inscripcion = $this->obtenerDatosInscripcion($inscripcionId);

            if (!$inscripcion) {
                error_log('Inscripción no encontrada - ID: ' . $inscripcionId);
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Inscripción no encontrada',
                    'inscripcion_id' => $inscripcionId
                ]);
                return;
            }

            // Generar comprobante unificado según estado actual:
            // inscrito -> pendiente -> pagado
            $result = $this->pdfService->generarComprobanteInscripcionPeleador($inscripcion);

            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(500);
                echo json_encode($result);
            }

        } catch (Exception $e) {
            error_log('Error en ComprobanteController::generar: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error interno al generar el comprobante'
            ]);
        }
    }

    /**
     * Ver el comprobante PDF más reciente de una inscripción.
     * GET /api/comprobantes/viewPdf/{inscripcion_id}
     * GET /api/comprobantes/view/{inscripcion_id}
     */
    public function viewPdf($inscripcionId) {
        try {
            $inscripcionId = (int)$inscripcionId;
            if ($inscripcionId <= 0) {
                $this->responderJson(400, [
                    'success' => false,
                    'message' => 'inscripcion_id inválido',
                ]);
                return;
            }

            $doc = $this->obtenerDocumentoPorInscripcion($inscripcionId);
            if (!$doc || empty($doc['file_url']) || !$this->archivoDocumentoExiste($doc)) {
                $inscripcion = $this->obtenerDatosInscripcion($inscripcionId);
                if (!$inscripcion) {
                    $this->responderJson(404, [
                        'success' => false,
                        'message' => 'Inscripción no encontrada',
                    ]);
                    return;
                }

                $result = $this->pdfService->generarComprobanteInscripcionPeleador($inscripcion);
                if (empty($result['success'])) {
                    $this->responderJson(500, [
                        'success' => false,
                        'message' => $result['message'] ?? 'No se pudo generar el comprobante',
                        'error' => $result['error'] ?? null,
                    ]);
                    return;
                }

                $doc = $this->obtenerDocumentoPorInscripcion($inscripcionId);
                if (!$doc || empty($doc['file_url'])) {
                    $doc = [
                        'file_url' => $result['url'] ?? null,
                    ];
                }
            }

            $url = $doc['file_url'] ?? null;
            if (!$url) {
                $this->responderJson(404, [
                    'success' => false,
                    'message' => 'No hay PDF disponible para esta inscripción',
                ]);
                return;
            }

            $url = $this->appendCacheBuster($url);
            $format = strtolower((string)($_GET['format'] ?? ''));
            if ($format === 'json') {
                $this->responderJson(200, [
                    'success' => true,
                    'inscripcion_id' => $inscripcionId,
                    'url' => $url,
                    'view_api_url' => '/api/comprobantes/viewPdf/' . $inscripcionId,
                    'regenerar_api_url' => '/api/comprobantes/pdf/' . $inscripcionId,
                ]);
                return;
            }

            $this->outputPdfInline($doc, $url);
        } catch (Exception $e) {
            error_log('Error en ComprobanteController::viewPdf: ' . $e->getMessage());
            $this->responderJson(500, [
                'success' => false,
                'message' => 'Error interno al abrir el comprobante',
            ]);
        }
    }

    /**
     * Regenerar comprobante PDF de una inscripción y abrirlo.
     * GET /api/comprobantes/pdf/{inscripcion_id}
     */
    public function regenerarPdf($inscripcionId) {
        try {
            $inscripcionId = (int)$inscripcionId;
            if ($inscripcionId <= 0) {
                $this->responderJson(400, [
                    'success' => false,
                    'message' => 'inscripcion_id inválido',
                ]);
                return;
            }

            $inscripcion = $this->obtenerDatosInscripcion($inscripcionId);
            if (!$inscripcion) {
                $this->responderJson(404, [
                    'success' => false,
                    'message' => 'Inscripción no encontrada',
                ]);
                return;
            }

            $result = $this->pdfService->generarComprobanteInscripcionPeleador($inscripcion);
            if (empty($result['success'])) {
                $this->responderJson(500, [
                    'success' => false,
                    'message' => $result['message'] ?? 'No se pudo regenerar el comprobante',
                    'error' => $result['error'] ?? null,
                ]);
                return;
            }

            $doc = $this->obtenerDocumentoPorInscripcion($inscripcionId);
            $url = $doc['file_url'] ?? ($result['url'] ?? null);
            if (!$url) {
                $this->responderJson(500, [
                    'success' => false,
                    'message' => 'Se regeneró, pero no hay URL disponible',
                ]);
                return;
            }

            $url = $this->appendCacheBuster($url);
            $format = strtolower((string)($_GET['format'] ?? ''));
            if ($format === 'json') {
                $this->responderJson(200, [
                    'success' => true,
                    'inscripcion_id' => $inscripcionId,
                    'url' => $url,
                    'view_api_url' => '/api/comprobantes/viewPdf/' . $inscripcionId,
                    'regenerar_api_url' => '/api/comprobantes/pdf/' . $inscripcionId,
                ]);
                return;
            }

            $this->outputPdfInline($doc, $url);
        } catch (Exception $e) {
            error_log('Error en ComprobanteController::regenerarPdf: ' . $e->getMessage());
            $this->responderJson(500, [
                'success' => false,
                'message' => 'Error interno al regenerar el comprobante',
            ]);
        }
    }

    /**
     * Obtener datos completos de la inscripción para el PDF
     * @param int $inscripcionId ID de la inscripción
     * @return array|null Datos de la inscripción
     */
    private function obtenerDatosInscripcion($inscripcionId) {
        try {
            $query = "SELECT
                        ie.id,
                        ie.peleador_id,
                        ie.estado_pago,
                        ie.monto_pagado,
                        ie.fecha_pago,
                        ie.metodo_pago,
                        ie.fecha_inscripcion,
                        p.documento_identidad as peleador_dni,
                        p.apodo as peleador_apodo,
                        u.nombre as peleador_nombre,
                        u.apellidos as peleador_apellidos,
                        u.telefono as peleador_telefono,
                        e.id as evento_id,
                        e.nombre as evento_nombre,
                        e.fecha as evento_fecha,
                        e.hora as evento_hora,
                        e.direccion as evento_direccion
                      FROM inscripciones_eventos ie
                      INNER JOIN peleadores p ON ie.peleador_id = p.id
                      INNER JOIN usuarios u ON p.usuario_id = u.id
                      INNER JOIN eventos e ON ie.evento_id = e.id
                      WHERE ie.id = :id
                      LIMIT 1";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $inscripcionId, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;

        } catch (Exception $e) {
            error_log('Error obteniendo datos de inscripción: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar comprobante por token (requiere login staff/admin)
     * GET /api/verificar-pago/{token}
     */
    public function verificar($token) {
        try {
            session_start();

            // Buscar comprobante en BD
            $comprobante = $this->buscarComprobante($token);

            // Si piden JSON (desde la app), devolver JSON
            $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
            $wantsJson = (isset($_GET['format']) && $_GET['format'] === 'json')
                        || strpos($acceptHeader, 'application/json') !== false;

            if ($wantsJson) {
                header('Content-Type: application/json; charset=UTF-8');
                // La app controla quién ve el scanner (solo staff/admin)
                if ($comprobante) {
                    echo json_encode([
                        'success' => true,
                        'valid' => true,
                        'data' => $this->formatComprobanteData($comprobante)
                    ]);
                } else {
                    echo json_encode(['success' => true, 'valid' => false, 'message' => 'Comprobante no encontrado']);
                }
                return;
            }

            // Navegador (QR escaneado) → página HTML con login
            header('Content-Type: text/html; charset=UTF-8');

            $staffLoggedIn = $this->isStaffLoggedIn();
            $staffName = $_SESSION['staff_name'] ?? '';
            $valid = false;
            $data = [];

            if ($staffLoggedIn && $comprobante) {
                $valid = true;
                $data = $this->formatComprobanteData($comprobante);
            }

            // Setting: qué ve el público al escanear QR sin ser staff
            $qrPublicView = 'ocultar_staff';
            try {
                $stmtSetting = $this->db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'qr_scan_public_view' LIMIT 1");
                $stmtSetting->execute();
                $row = $stmtSetting->fetch(PDO::FETCH_ASSOC);
                if ($row) $qrPublicView = $row['setting_value'];
            } catch (Exception $e) {}

            require __DIR__ . '/../views/verificar_pago.php';

        } catch (Exception $e) {
            error_log('Error verificando comprobante: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => false, 'message' => 'Error al verificar el comprobante']);
        }
    }

    /**
     * Login de staff para verificación QR
     * POST /api/verificar-pago-auth
     */
    public function authStaff() {
        session_start();
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $email = trim($input['email'] ?? '');
            $password = $input['password'] ?? '';

            if (!$email || !$password) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Email y contraseña requeridos']);
                return;
            }

            // Buscar usuario staff o admin
            $query = "SELECT u.id, u.nombre, u.email, u.password_hash, u.tipo_id, t.nombre as tipo_nombre
                      FROM usuarios u
                      INNER JOIN tipos_usuario t ON u.tipo_id = t.id
                      WHERE u.email = :email AND u.estado = 'activo' AND u.tipo_id IN (1, 5)
                      LIMIT 1";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Credenciales inválidas o sin permisos']);
                return;
            }

            // Guardar sesión de staff (expira en 12 horas)
            $_SESSION['staff_id'] = $usuario['id'];
            $_SESSION['staff_name'] = $usuario['nombre'];
            $_SESSION['staff_tipo'] = $usuario['tipo_nombre'];
            $_SESSION['staff_login_time'] = time();
            $_SESSION['staff_expiry'] = time() + (12 * 3600);

            echo json_encode([
                'success' => true,
                'message' => 'Bienvenido, ' . $usuario['nombre'],
                'staff' => [
                    'nombre' => $usuario['nombre'],
                    'tipo' => $usuario['tipo_nombre']
                ]
            ]);

        } catch (Exception $e) {
            error_log('Error en authStaff: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error interno']);
        }
    }

    /**
     * Cerrar sesión staff
     * POST /api/verificar-pago-logout
     */
    public function logoutStaff() {
        session_start();
        session_destroy();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => true, 'message' => 'Sesión cerrada']);
    }

    /**
     * Verificar si hay sesión de staff activa
     */
    private function isStaffLoggedIn() {
        if (!isset($_SESSION['staff_id']) || !isset($_SESSION['staff_expiry'])) {
            return false;
        }
        // Verificar expiración (12 horas)
        if (time() > $_SESSION['staff_expiry']) {
            session_destroy();
            return false;
        }
        return true;
    }

    /**
     * Buscar comprobante por token
     */
    private function buscarComprobante($token) {
        $query = "SELECT
                    pd.id,
                    pd.file_path,
                    pd.file_url,
                    pd.verification_token,
                    pd.metadata_json,
                    pd.created_at as fecha_generacion,
                    ie.id as inscripcion_id,
                    ie.estado_pago,
                    ie.monto_pagado,
                    u.nombre as peleador_nombre,
                    e.nombre as evento_nombre,
                    ie.fecha_checkin,
                    ie.staff_checkin_id
                  FROM pdf_documents pd
                  INNER JOIN inscripciones_eventos ie
                          ON pd.entity_type = 'inscripcion_evento'
                         AND pd.entity_id = ie.id
                  INNER JOIN peleadores p ON ie.peleador_id = p.id
                  INNER JOIN usuarios u ON p.usuario_id = u.id
                  INNER JOIN eventos e ON ie.evento_id = e.id
                  WHERE pd.document_type = :document_type
                    AND pd.verification_token = :token
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':document_type', self::DOC_TYPE_FIGHTER_COMPROBANTE);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Formatear datos del comprobante
     */
    private function formatComprobanteData($comprobante) {
        $metadata = [];
        if (!empty($comprobante['metadata_json'])) {
            $decoded = json_decode($comprobante['metadata_json'], true);
            if (is_array($decoded)) {
                $metadata = $decoded;
            }
        }

        $fechaGeneracion = $metadata['generated_at'] ?? $comprobante['fecha_generacion'] ?? null;

        return [
            'peleador' => $comprobante['peleador_nombre'],
            'evento' => $comprobante['evento_nombre'],
            'monto' => number_format((float)($comprobante['monto_pagado'] ?? 0), 2),
            'estado' => strtoupper($comprobante['estado_pago']),
            'fecha_generacion' => $fechaGeneracion ? date('d/m/Y H:i', strtotime($fechaGeneracion)) : null,
            'checkin' => $comprobante['fecha_checkin'] ? true : false,
            'fecha_checkin' => $comprobante['fecha_checkin'] ? date('d/m/Y H:i', strtotime($comprobante['fecha_checkin'])) : null
        ];
    }

    private function obtenerDocumentoPorInscripcion($inscripcionId) {
        $query = "SELECT id, file_path, file_url, verification_token, created_at
                  FROM pdf_documents
                  WHERE document_type = :document_type
                    AND entity_type = 'inscripcion_evento'
                    AND entity_id = :inscripcion_id
                  ORDER BY id DESC
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':document_type', self::DOC_TYPE_FIGHTER_COMPROBANTE);
        $stmt->bindValue(':inscripcion_id', (int)$inscripcionId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function archivoDocumentoExiste($doc) {
        $relativePath = trim((string)($doc['file_path'] ?? ''));
        if ($relativePath === '') {
            return false;
        }
        $absolutePath = realpath(__DIR__ . '/../' . ltrim($relativePath, '/'));
        return $absolutePath !== false && is_file($absolutePath);
    }

    private function appendCacheBuster($url) {
        $separator = strpos($url, '?') === false ? '?' : '&';
        return $url . $separator . 'v=' . time();
    }

    private function outputPdfInline($doc, $fallbackUrl = null) {
        $relativePath = trim((string)($doc['file_path'] ?? ''));
        if ($relativePath !== '') {
            $absolutePath = realpath(__DIR__ . '/../' . ltrim($relativePath, '/'));
            if ($absolutePath !== false && is_file($absolutePath)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . basename($absolutePath) . '"');
                header('Content-Length: ' . (string)filesize($absolutePath));
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                header('Expires: 0');
                readfile($absolutePath);
                exit;
            }
        }

        if (!empty($fallbackUrl)) {
            header('Location: ' . $fallbackUrl, true, 302);
            exit;
        }

        $this->responderJson(404, [
            'success' => false,
            'message' => 'No se encontró el archivo PDF generado',
        ]);
    }

    /**
     * POST /api/checkin/{token}
     * Registra la entrada (check-in) de un peleador al evento
     */
    public function checkin($token) {
        header('Content-Type: application/json; charset=UTF-8');

        try {
            $comprobante = $this->buscarComprobante($token);

            if (!$comprobante) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Comprobante no encontrado']);
                return;
            }

            $data = $this->formatComprobanteData($comprobante);

            // Ya hizo check-in previamente
            if ($comprobante['fecha_checkin']) {
                echo json_encode([
                    'success' => true,
                    'duplicate' => true,
                    'message' => 'Este peleador ya registró su entrada',
                    'data' => $data
                ]);
                return;
            }

            // Registrar check-in
            $stmt = $this->db->prepare(
                "UPDATE inscripciones_eventos SET fecha_checkin = NOW(), staff_checkin_id = :staff_id WHERE id = :id"
            );
            $staffId = $_SESSION['staff_id'] ?? null;
            $stmt->bindValue(':staff_id', $staffId, $staffId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(':id', (int)$comprobante['inscripcion_id'], PDO::PARAM_INT);
            $stmt->execute();

            // Recargar datos con fecha_checkin actualizada
            $comprobanteActualizado = $this->buscarComprobante($token);
            $dataActualizado = $this->formatComprobanteData($comprobanteActualizado);

            echo json_encode([
                'success' => true,
                'duplicate' => false,
                'message' => 'Entrada registrada correctamente',
                'data' => $dataActualizado
            ]);

        } catch (Exception $e) {
            error_log('Error en checkin: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al registrar entrada']);
        }
    }

    private function responderJson($statusCode, $payload) {
        http_response_code((int)$statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload);
    }
}
