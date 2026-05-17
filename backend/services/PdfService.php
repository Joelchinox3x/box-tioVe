<?php
/**
 * PdfService - Servicio para generar PDFs
 * Orquesta la generación de diferentes tipos de documentos PDF
 */

require_once __DIR__ . '/../helpers/PdfEngine.php';
require_once __DIR__ . '/../helpers/QRGenerator.php';
require_once __DIR__ . '/../config/Config.php';

class PdfService {
    private $db;
    private const DOC_TYPE_FIGHTER_INSCRIPCION = 'fighter_inscripcion_ticket';
    private const DOC_TYPE_FIGHTER_COMPROBANTE = 'fighter_inscripcion_comprobante';
    private const SETTING_TEMPLATE_FIGHTER = 'pdf_template_fighter_inscripcion';
    private const SETTING_TEMPLATE_FIGHTER_COMPROBANTE = 'pdf_template_fighter_inscripcion_comprobante';
    private const DEFAULT_FIGHTER_TEMPLATE = 'fighter_ticket_tpl_01';
    private const DEFAULT_FIGHTER_COMPROBANTE_TEMPLATE = 'fighter_comprobante_tpl_01';
    // 1414x457 px @ 96 DPI -> 374.12 x 120.91 mm
    private const FIGHTER_COMPROBANTE_FORMAT_MM = '374.12x120.91';
    private const DOC_TYPE_BOLETO_TICKET = 'boleto_ticket';
    private const DEFAULT_BOLETO_TEMPLATE = 'boleto_ticket_tpl_01';
    private const SETTING_TEMPLATE_BOLETO = 'pdf_template_boleto_ticket';

    public function __construct($db = null) {
        $this->db = $db;
    }

    /**
     * Generar PDF de ticket de inscripción para peleador.
     * Este documento es independiente al comprobante de pago.
     */
    public function generarTicketInscripcionPeleador($payload) {
        try {
            $template = $this->resolverTemplate(
                self::DOC_TYPE_FIGHTER_INSCRIPCION,
                self::SETTING_TEMPLATE_FIGHTER,
                self::DEFAULT_FIGHTER_TEMPLATE,
                [
                    'view_path' => 'fighter_inscripcion_ticket/template_01/body',
                    'format_spec' => '190x100',
                    'orientation' => 'L',
                    'margin_top' => 6,
                    'margin_bottom' => 6,
                    'margin_left' => 6,
                    'margin_right' => 6,
                ]
            );

            $token = $this->generarToken('fighter_' . ($payload['peleador_id'] ?? uniqid('', true)));
            $qrData = Config::BASE_URL . '/verificar-ticket/' . $token;
            $qrBase64 = QRGenerator::generateBase64($qrData, 130);

            $data = [
                'ticket_code' => str_pad((string)($payload['peleador_id'] ?? 0), 6, '0', STR_PAD_LEFT),
                'fecha_emision' => date('d/m/Y H:i'),
                'peleador' => [
                    'nombre' => $payload['nombre'] ?? 'N/A',
                    'apodo' => $payload['apodo'] ?? '',
                    'dni' => $payload['dni'] ?? 'N/A',
                    'telefono' => $payload['telefono'] ?? 'N/A',
                ],
                'evento' => [
                    'nombre' => $payload['evento_nombre'] ?? 'Evento por anunciar',
                    'fecha' => $payload['evento_fecha'] ?? null,
                    'hora' => $payload['evento_hora'] ?? null,
                    'direccion' => $payload['evento_direccion'] ?? 'Por confirmar'
                ],
                'inscripcion' => [
                    'estado' => strtoupper($payload['estado_inscripcion'] ?? 'PENDIENTE'),
                    'monto' => number_format((float)($payload['monto'] ?? 0), 2)
                ],
                'qr_code' => $qrBase64,
                'token' => $token,
                'template_background' => $this->resolverAssetPath($template['background_path'] ?? null),
            ];

            $outputDir = __DIR__ . '/../files/fighter_inscripciones_pdf/';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0777, true);
            }

            $filename = 'fighter_ticket_' . ($payload['peleador_id'] ?? '0') . '_' . time() . '.pdf';
            $outputPath = $outputDir . $filename;
            $relativePath = 'files/fighter_inscripciones_pdf/' . $filename;

            $pdfEngine = new PdfEngine($template['code'], $this->construirConfigMpdf($template));
            $pdfEngine->generate($template['view_path'], $data, $outputPath);

            if (!file_exists($outputPath)) {
                throw new Exception('No se pudo generar el ticket PDF del peleador');
            }

            $fileUrl = Config::BASE_URL . '/' . $relativePath;

            if ($this->db) {
                $metadata = [
                    'template_name' => $template['name'] ?? '',
                    'evento' => $data['evento']['nombre'],
                    'estado_inscripcion' => $data['inscripcion']['estado'],
                    'generated_at' => date('c'),
                ];
                $this->guardarDocumentoPdf([
                    'document_type' => self::DOC_TYPE_FIGHTER_INSCRIPCION,
                    'template_code' => $template['code'],
                    'entity_type' => 'peleador',
                    'entity_id' => (int)($payload['peleador_id'] ?? 0),
                    'file_path' => $relativePath,
                    'file_url' => $fileUrl,
                    'verification_token' => $token,
                    'metadata_json' => json_encode($metadata),
                ]);
            }

            return [
                'success' => true,
                'filename' => $filename,
                'url' => $fileUrl,
                'file_path' => $relativePath,
                'token' => $token,
                'template_code' => $template['code'],
            ];
        } catch (Exception $e) {
            error_log('Error generarTicketInscripcionPeleador: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'No se pudo generar el ticket de inscripción',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generar comprobante PDF unificado para inscripción de peleador.
     * Se reutiliza el mismo documento lógico por inscripción y cambia de estado:
     * inscrito -> pendiente -> pagado
     */
    public function generarComprobanteInscripcionPeleador($inscripcionData) {
        $traceId = $inscripcionData['_debug_trace_id'] ?? ('pdf_' . date('Ymd_His') . '_' . substr(md5(uniqid((string)mt_rand(), true)), 0, 8));
        try {
            $this->logPdfFlow($traceId, 'PdfService.generarComprobante.start', [
                'source' => $inscripcionData['_debug_source'] ?? null,
                'inscripcion_id' => (int)($inscripcionData['id'] ?? 0),
                'peleador_id' => (int)($inscripcionData['peleador_id'] ?? 0),
                'evento_id' => (int)($inscripcionData['evento_id'] ?? 0),
                'estado_pago' => $inscripcionData['estado_pago'] ?? null,
            ]);

            $template = $this->resolverTemplate(
                self::DOC_TYPE_FIGHTER_COMPROBANTE,
                self::SETTING_TEMPLATE_FIGHTER_COMPROBANTE,
                self::DEFAULT_FIGHTER_COMPROBANTE_TEMPLATE,
                [
                    'view_path' => 'fighter_inscripcion_comprobante/template_01/body',
                    'format_spec' => self::FIGHTER_COMPROBANTE_FORMAT_MM,
                    'orientation' => 'L',
                    'margin_top' => 0,
                    'margin_bottom' => 0,
                    'margin_left' => 0,
                    'margin_right' => 0,
                ]
            );

            $this->logPdfFlow($traceId, 'PdfService.generarComprobante.template', [
                'template' => [
                    'code' => $template['code'] ?? null,
                    'view_path' => $template['view_path'] ?? null,
                    'format_spec' => $template['format_spec'] ?? null,
                    'orientation' => $template['orientation'] ?? null,
                    'background_path' => $template['background_path'] ?? null,
                ],
            ]);

            $inscripcionId = (int)($inscripcionData['id'] ?? 0);
            if ($inscripcionId <= 0) {
                throw new Exception('ID de inscripción inválido para generar comprobante');
            }

            $estadoPago = $this->normalizarEstadoPago($inscripcionData['estado_pago'] ?? 'inscrito');
            $estadoVisual = $this->mapEstadoComprobante($estadoPago);

            $token = $this->generarToken('inscripcion_' . $inscripcionId . '_' . $estadoPago);
            $qrData = Config::API_URL . '/verificar-pago/' . $token;
            $qrBase64 = QRGenerator::generateBase64($qrData, 150);

            $monto = (float)($inscripcionData['monto_pagado'] ?? $inscripcionData['precio_inscripcion_peleador'] ?? 0);
            $fechaBase = $inscripcionData['fecha_pago'] ?? $inscripcionData['fecha_inscripcion'] ?? 'now';
            $fechaEstado = date('d/m/Y H:i', strtotime($fechaBase));
            $metodoPago = trim((string)($inscripcionData['metodo_pago'] ?? ''));
            $templateBackground = $this->resolverAssetPath($template['background_path'] ?? null);

            $data = [
                'comprobante_id' => str_pad((string)$inscripcionId, 6, '0', STR_PAD_LEFT),
                'fecha_emision' => date('d/m/Y H:i'),
                'peleador' => [
                    'nombre' => $inscripcionData['peleador_nombre'] ?? 'N/A',
                    'apellidos' => $inscripcionData['peleador_apellidos'] ?? '',
                    'dni' => $inscripcionData['peleador_dni'] ?? 'N/A',
                    'apodo' => $inscripcionData['peleador_apodo'] ?? '',
                    'telefono' => $inscripcionData['peleador_telefono'] ?? 'N/A'
                ],
                'evento' => [
                    'nombre' => $inscripcionData['evento_nombre'] ?? 'N/A',
                    'fecha' => $inscripcionData['evento_fecha'] ?? date('Y-m-d'),
                    'hora' => $inscripcionData['evento_hora'] ?? '00:00',
                    'direccion' => $inscripcionData['evento_direccion'] ?? 'Por confirmar'
                ],
                'inscripcion' => [
                    'estado' => $estadoVisual['label'],
                    'estado_detalle' => $estadoVisual['description'],
                    'badge_color' => $estadoVisual['badge_color'],
                    'badge_bg' => $estadoVisual['badge_bg'],
                    'monto' => number_format($monto, 2),
                    'metodo' => $metodoPago !== '' ? ucfirst($metodoPago) : 'Por definir',
                    'fecha' => $fechaEstado,
                ],
                'manager' => $this->obtenerManagerGeneral(),
                'qr_code' => $qrBase64,
                'token' => $token,
                'template_background' => $templateBackground,
            ];

            $this->logPdfFlow($traceId, 'PdfService.generarComprobante.background_resolved', [
                'background_path_db' => $template['background_path'] ?? null,
                'template_background' => $templateBackground,
            ]);

            $pdfDir = __DIR__ . '/../files/fighter_comprobantes_pdf/';
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0777, true);
            }

            $filename = 'inscripcion_comprobante_' . $inscripcionId . '.pdf';
            $outputPath = $pdfDir . $filename;
            $relativePath = 'files/fighter_comprobantes_pdf/' . $filename;

            $pdfEngine = new PdfEngine($template['code'], $this->construirConfigMpdf($template));

            // Logo de branding como marca de agua (config por template desde BD)
            $logoPdfPath = $this->obtenerLogoPdfPath();
            if ($logoPdfPath) {
                $wmRaw = $template['watermark_config'] ?? null;
                $wmConfig = is_string($wmRaw) ? (json_decode($wmRaw, true) ?: []) : (is_array($wmRaw) ? $wmRaw : []);
                $pdfEngine->setWatermark(
                    $logoPdfPath,
                    (float)($wmConfig['x'] ?? 10),
                    (float)($wmConfig['y'] ?? 85),
                    (float)($wmConfig['w'] ?? 25),
                    (float)($wmConfig['alpha'] ?? 0.12)
                );
            }

            $pdfEngine->generate($template['view_path'], $data, $outputPath);

            if (!file_exists($outputPath)) {
                throw new Exception('No se pudo generar el comprobante PDF de inscripción');
            }

            $fileUrl = Config::BASE_URL . '/' . $relativePath;

            if ($this->db) {
                $metadata = [
                    'template_name' => $template['name'] ?? '',
                    'inscripcion_id' => $inscripcionId,
                    'peleador_id' => (int)($inscripcionData['peleador_id'] ?? 0),
                    'evento_id' => (int)($inscripcionData['evento_id'] ?? 0),
                    'estado_pago' => $estadoPago,
                    'estado_label' => $estadoVisual['label'],
                    'monto_pagado' => $monto,
                    'metodo_pago' => $metodoPago !== '' ? $metodoPago : null,
                    'qr_data' => $qrData,
                    'file_path' => $relativePath,
                    'file_url' => $fileUrl,
                    'generated_at' => date('c'),
                ];

                $docId = $this->guardarDocumentoPdf([
                    'document_type' => self::DOC_TYPE_FIGHTER_COMPROBANTE,
                    'template_code' => $template['code'],
                    'entity_type' => 'inscripcion_evento',
                    'entity_id' => $inscripcionId,
                    'file_path' => $relativePath,
                    'file_url' => $fileUrl,
                    'verification_token' => $token,
                    'metadata_json' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ], true);
                $this->logPdfFlow($traceId, 'PdfService.generarComprobante.db_saved', [
                    'pdf_document_id' => $docId,
                    'entity_id' => $inscripcionId,
                    'file_url' => $fileUrl,
                ]);
            }

            $this->logPdfFlow($traceId, 'PdfService.generarComprobante.success', [
                'inscripcion_id' => $inscripcionId,
                'estado' => $estadoVisual['label'],
                'url' => $fileUrl,
            ]);
            return [
                'success' => true,
                'filename' => $filename,
                'url' => $fileUrl,
                'token' => $token,
                'estado' => $estadoVisual['label'],
                'template_code' => $template['code'],
                'document_type' => self::DOC_TYPE_FIGHTER_COMPROBANTE,
                'debug_trace_id' => $traceId,
            ];
        } catch (Exception $e) {
            error_log('Error generarComprobanteInscripcionPeleador: ' . $e->getMessage());
            $this->logPdfFlow($traceId, 'PdfService.generarComprobante.exception', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            return [
                'success' => false,
                'message' => 'No se pudo generar el comprobante de inscripción',
                'error' => $e->getMessage(),
                'debug_trace_id' => $traceId,
            ];
        }
    }

    /**
     * Generar PDF de boleto de evento (entrada).
     * Se genera cuando el admin aprueba el pago del boleto.
     */
    public function generarBoletoPdf($boletoData) {
        try {
            $boletoId = (int)($boletoData['id'] ?? 0);
            if ($boletoId <= 0) {
                throw new Exception('ID de boleto inválido para generar PDF');
            }

            $template = $this->resolverTemplate(
                self::DOC_TYPE_BOLETO_TICKET,
                self::SETTING_TEMPLATE_BOLETO,
                self::DEFAULT_BOLETO_TEMPLATE,
                [
                    'view_path' => 'boleto_ticket/template_01/body',
                    'format_spec' => 'A5',
                    'orientation' => 'L',
                    'margin_top' => 6,
                    'margin_bottom' => 6,
                    'margin_left' => 6,
                    'margin_right' => 6,
                ]
            );

            $token = $this->generarToken('boleto_' . $boletoId);
            $qrData = Config::API_URL . '/verificar-boleto/' . $token;
            $qrBase64 = QRGenerator::generateBase64($qrData, 150);

            $data = [
                'boleto' => [
                    'id' => $boletoId,
                    'codigo_qr' => $boletoData['codigo_qr'] ?? 'SIN-CODIGO',
                    'tipo' => $boletoData['tipo_boleto'] ?? 'General',
                    'cantidad' => (int)($boletoData['cantidad'] ?? 1),
                    'precio_total' => (float)($boletoData['precio_total'] ?? 0),
                    'color_hex' => $boletoData['color_hex'] ?? '#d98721',
                ],
                'comprador' => [
                    'nombre' => $boletoData['comprador_nombres_apellidos'] ?? 'N/A',
                    'dni' => $boletoData['comprador_dni'] ?? 'N/A',
                    'telefono' => $boletoData['comprador_telefono'] ?? '',
                ],
                'evento' => [
                    'nombre' => $boletoData['evento_nombre'] ?? 'Evento',
                    'fecha' => $boletoData['evento_fecha'] ?? null,
                    'hora' => $boletoData['evento_hora'] ?? null,
                    'direccion' => $boletoData['evento_direccion'] ?? 'Por confirmar',
                ],
                'fecha_emision' => date('d/m/Y H:i'),
                'qr_code' => $qrBase64,
                'token' => $token,
                'template_background' => $this->resolverAssetPath($template['background_path'] ?? 'views/pdf_templates/fighter_inscripcion_comprobante/fondo_comprob_01.png'),
            ];

            $pdfDir = __DIR__ . '/../files/boletos_pdf/';
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0777, true);
            }

            $filename = 'boleto_ticket_' . $boletoId . '.pdf';
            $outputPath = $pdfDir . $filename;
            $relativePath = 'files/boletos_pdf/' . $filename;

            $pdfEngine = new PdfEngine($template['code'], $this->construirConfigMpdf($template));

            $logoPdfPath = $this->obtenerLogoPdfPath();
            if ($logoPdfPath) {
                $wmRaw = $template['watermark_config'] ?? null;
                $wmConfig = is_string($wmRaw) ? (json_decode($wmRaw, true) ?: []) : (is_array($wmRaw) ? $wmRaw : []);
                $pdfEngine->setWatermark(
                    $logoPdfPath,
                    (float)($wmConfig['x'] ?? 10),
                    (float)($wmConfig['y'] ?? 85),
                    (float)($wmConfig['w'] ?? 25),
                    (float)($wmConfig['alpha'] ?? 0.12)
                );
            }

            $pdfEngine->generate($template['view_path'], $data, $outputPath);

            if (!file_exists($outputPath)) {
                throw new Exception('No se pudo generar el boleto PDF');
            }

            $fileUrl = Config::BASE_URL . '/' . $relativePath;

            if ($this->db) {
                $metadata = [
                    'template_name' => $template['name'] ?? '',
                    'boleto_id' => $boletoId,
                    'evento_id' => (int)($boletoData['evento_id'] ?? 0),
                    'tipo_boleto' => $data['boleto']['tipo'],
                    'comprador' => $data['comprador']['nombre'],
                    'cantidad' => $data['boleto']['cantidad'],
                    'precio_total' => $data['boleto']['precio_total'],
                    'generated_at' => date('c'),
                ];

                $this->guardarDocumentoPdf([
                    'document_type' => self::DOC_TYPE_BOLETO_TICKET,
                    'template_code' => $template['code'],
                    'entity_type' => 'boleto_vendido',
                    'entity_id' => $boletoId,
                    'file_path' => $relativePath,
                    'file_url' => $fileUrl,
                    'verification_token' => $token,
                    'metadata_json' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ], true);
            }

            return [
                'success' => true,
                'filename' => $filename,
                'url' => $fileUrl,
                'token' => $token,
                'template_code' => $template['code'],
            ];
        } catch (Exception $e) {
            error_log('Error generarBoletoPdf: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'No se pudo generar el boleto PDF',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Compatibilidad con llamadas anteriores.
     */
    public function generarComprobantePago($inscripcionData) {
        return $this->generarComprobanteInscripcionPeleador($inscripcionData);
    }

    /**
     * Generar token único para verificación
     */
    private function generarToken($seed) {
        return hash('sha256', $seed . time() . uniqid() . 'boxtiove_secret');
    }

    private function normalizarEstadoPago($estado) {
        $estado = strtolower(trim((string)$estado));
        if (!in_array($estado, ['inscrito', 'pendiente', 'pagado'], true)) {
            return 'inscrito';
        }
        return $estado;
    }

    private function mapEstadoComprobante($estado) {
        switch ($estado) {
            case 'pagado':
                return [
                    'label' => 'PAGADO',
                    'description' => 'Pago confirmado por administración',
                    'badge_color' => '#1E8449',
                    'badge_bg' => '#EAF7EF',
                ];
            case 'pendiente':
                return [
                    'label' => 'PENDIENTE',
                    'description' => 'Pago enviado, en validación administrativa',
                    'badge_color' => '#B9770E',
                    'badge_bg' => '#FEF5E7',
                ];
            case 'inscrito':
            default:
                return [
                    'label' => 'INSCRITO',
                    'description' => 'Inscripción creada, falta registrar pago',
                    'badge_color' => '#1F618D',
                    'badge_bg' => '#EBF5FB',
                ];
        }
    }

    private function obtenerSetting($key) {
        if (!$this->db) {
            return null;
        }

        try {
            $query = "SELECT setting_value FROM system_settings WHERE setting_key = :key LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':key' => $key]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['setting_value'] : null;
        } catch (Exception $e) {
            error_log('Error obtenerSetting(' . $key . '): ' . $e->getMessage());
            return null;
        }
    }

    private function resolverTemplate($documentType, $settingKey, $defaultCode, $fallback = []) {
        $defaultFallback = [
            'code' => $defaultCode,
            'name' => 'Template por defecto',
            'view_path' => 'fighter_inscripcion_ticket/template_01/body',
            'format_spec' => 'A4',
            'orientation' => 'P',
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'background_path' => null,
        ];
        $fallback = array_merge($defaultFallback, $fallback);

        if (!$this->db) {
            return $fallback;
        }

        try {
            $selectedCode = $this->obtenerSetting($settingKey) ?: $defaultCode;

            $query = "SELECT code, name, view_path, format_spec, orientation, margin_top, margin_bottom, margin_left, margin_right, background_path, watermark_config
                      FROM pdf_templates
                      WHERE document_type = :document_type
                        AND code = :code
                        AND is_active = 1
                      LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':document_type' => $documentType,
                ':code' => $selectedCode
            ]);
            $template = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($template) {
                return $template;
            }

            $queryDefault = "SELECT code, name, view_path, format_spec, orientation, margin_top, margin_bottom, margin_left, margin_right, background_path, watermark_config
                             FROM pdf_templates
                             WHERE document_type = :document_type
                               AND is_active = 1
                             ORDER BY is_default DESC, sort_order ASC, id ASC
                             LIMIT 1";
            $stmtDefault = $this->db->prepare($queryDefault);
            $stmtDefault->execute([':document_type' => $documentType]);
            $template = $stmtDefault->fetch(PDO::FETCH_ASSOC);

            return $template ?: $fallback;
        } catch (Exception $e) {
            error_log('Error resolverTemplate: ' . $e->getMessage());
            return $fallback;
        }
    }

    private function construirConfigMpdf($template) {
        $formatSpec = $template['format_spec'] ?? 'A4';
        $orientation = strtoupper((string)($template['orientation'] ?? 'P'));
        $format = $this->parseFormatSpec($formatSpec);

        if (!in_array($orientation, ['P', 'L'], true)) {
            $orientation = 'P';
        }

        return [
            'format' => $format,
            'orientation' => $orientation,
            'margin_top' => (int)($template['margin_top'] ?? 10),
            'margin_bottom' => (int)($template['margin_bottom'] ?? 10),
            'margin_left' => (int)($template['margin_left'] ?? 10),
            'margin_right' => (int)($template['margin_right'] ?? 10),
        ];
    }

    private function parseFormatSpec($formatSpec) {
        $formatSpec = trim((string)$formatSpec);
        if ($formatSpec === '') {
            return 'A4';
        }

        if (preg_match('/^([0-9]+(?:\\.[0-9]+)?)x([0-9]+(?:\\.[0-9]+)?)$/i', $formatSpec, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }

        return strtoupper($formatSpec);
    }
    private function resolverAssetPath($path) {
        if (!$path) {
            return null;
        }

        $path = trim((string)$path);
        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\\/\\//i', $path)) {
            return $path;
        }

        $normalized = str_replace('\\', '/', $path);
        $candidates = [];

        if ($this->esRutaAbsoluta($normalized)) {
            $candidates[] = $normalized;
        }

        $candidates[] = __DIR__ . '/../' . ltrim($normalized, '/');
        $candidates[] = dirname(__DIR__) . '/' . ltrim($normalized, '/');
        $candidates[] = dirname(__DIR__) . '/public/' . ltrim($normalized, '/');

        if (strpos(ltrim($normalized, '/'), 'backend/') === 0) {
            $withoutBackendPrefix = substr(ltrim($normalized, '/'), strlen('backend/'));
            $candidates[] = __DIR__ . '/../' . $withoutBackendPrefix;
            $candidates[] = dirname(__DIR__) . '/' . $withoutBackendPrefix;
        }

        foreach ($candidates as $candidate) {
            $resolved = realpath($candidate);
            if ($resolved !== false && is_file($resolved)) {
                $resolved = str_replace('\\', '/', $resolved);
                return 'file://' . $resolved;
            }
        }

        // Si parece una ruta interna de templates y no se pudo resolver en filesystem,
        // devolvemos null para que el template use su fallback local.
        if (strpos(ltrim($normalized, '/'), 'views/') === 0 || strpos($normalized, 'pdf_templates/') !== false) {
            return null;
        }

        return rtrim(Config::BASE_URL, '/') . '/' . ltrim($normalized, '/');
    }

    private function esRutaAbsoluta($path) {
        return strpos($path, '/') === 0 || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
    }

    private function guardarDocumentoPdf($data, $upsertByEntity = false) {
        if (!$this->db) {
            return null;
        }

        try {
            if ($upsertByEntity) {
                $queryFind = "SELECT id
                              FROM pdf_documents
                              WHERE document_type = :document_type
                                AND entity_type = :entity_type
                                AND entity_id = :entity_id
                              ORDER BY id DESC
                              LIMIT 1";
                $stmtFind = $this->db->prepare($queryFind);
                $stmtFind->execute([
                    ':document_type' => $data['document_type'],
                    ':entity_type' => $data['entity_type'],
                    ':entity_id' => $data['entity_id'],
                ]);
                $existing = $stmtFind->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    $queryUpdate = "UPDATE pdf_documents
                                    SET template_code = :template_code,
                                        file_path = :file_path,
                                        file_url = :file_url,
                                        verification_token = :verification_token,
                                        metadata_json = :metadata_json,
                                        created_at = NOW()
                                    WHERE id = :id";
                    $stmtUpdate = $this->db->prepare($queryUpdate);
                    $stmtUpdate->execute([
                        ':id' => $existing['id'],
                        ':template_code' => $data['template_code'],
                        ':file_path' => $data['file_path'],
                        ':file_url' => $data['file_url'],
                        ':verification_token' => $data['verification_token'],
                        ':metadata_json' => $data['metadata_json'],
                    ]);
                    return (int)$existing['id'];
                }
            }

            $query = "INSERT INTO pdf_documents
                        (document_type, template_code, entity_type, entity_id, file_path, file_url, verification_token, metadata_json)
                      VALUES
                        (:document_type, :template_code, :entity_type, :entity_id, :file_path, :file_url, :verification_token, :metadata_json)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':document_type' => $data['document_type'],
                ':template_code' => $data['template_code'],
                ':entity_type' => $data['entity_type'],
                ':entity_id' => $data['entity_id'],
                ':file_path' => $data['file_path'],
                ':file_url' => $data['file_url'],
                ':verification_token' => $data['verification_token'],
                ':metadata_json' => $data['metadata_json'],
            ]);
            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            error_log('Error guardarDocumentoPdf: ' . $e->getMessage());
            return null;
        }
    }

    private function obtenerManagerGeneral() {
        if (!$this->db) {
            return ['nombre' => 'Por asignar', 'telefono' => ''];
        }
        try {
            $stmt = $this->db->prepare(
                "SELECT nombre_visible, telefono_whatsapp FROM managers_contacto WHERE activo = 1 AND rol = 'manager_general' ORDER BY prioridad ASC LIMIT 1"
            );
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'nombre' => $row['nombre_visible'],
                    'telefono' => $row['telefono_whatsapp'],
                ];
            }
        } catch (Exception $e) {
            error_log('Error obtenerManagerGeneral: ' . $e->getMessage());
        }
        return ['nombre' => 'Por asignar', 'telefono' => ''];
    }

    private function obtenerLogoPdfPath() {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare("SELECT nombre_archivo FROM logos WHERE tipo = 'pdf' AND activo = 1 LIMIT 1");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $path = realpath(__DIR__ . '/../files/logos/' . $row['nombre_archivo']);
                return ($path && file_exists($path)) ? $path : null;
            }
        } catch (Exception $e) {
            error_log('Error obtenerLogoPdfPath: ' . $e->getMessage());
        }
        return null;
    }

    private function logPdfFlow($traceId, $step, $context = []) {
        try {
            $payload = [
                'time' => date('Y-m-d H:i:s'),
                'trace_id' => $traceId ?: 'no-trace',
                'step' => $step,
                'context' => $context,
            ];
            $line = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($line === false) {
                $line = json_encode([
                    'time' => date('Y-m-d H:i:s'),
                    'trace_id' => $traceId ?: 'no-trace',
                    'step' => $step,
                    'context' => 'json_encode_failed',
                ]);
            }

            $logPath = __DIR__ . '/../files/debug_pdf_flow.log';
            file_put_contents($logPath, $line . PHP_EOL, FILE_APPEND);
        } catch (Exception $e) {
            error_log('PdfService::logPdfFlow error: ' . $e->getMessage());
        }
    }
}
