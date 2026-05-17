    <?php
    /**
     * Controlador de Peleadores
     * Gestiona inscripciones, listados y perfil
     */
    class PeleadoresController {
        private $db;

        public function __construct($db) {
            $this->db = $db;
        }

        /**
         * Listar peleadores con filtros
         */
        public function listar($filtro = 'todos', $club = null) {
            $query = "SELECT
                p.id, p.apodo, p.foto_perfil, p.estilo, p.genero,
                p.victorias, p.derrotas, p.empates, p.total_promociones,
                p.peso_actual, p.altura, p.experiencia_anos,
                u.nombre, u.apellidos,
                c.nombre as club_nombre
                FROM peleadores p
                JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN clubs c ON p.club_id = c.id
                WHERE p.estado_inscripcion = 'aprobado'";

            // Filtrar por club
            if ($club) {
                $query .= " AND p.club_id = :club_id";
            }

            // Ordenar según filtro
            if ($filtro === 'populares') {
                $query .= " ORDER BY p.total_promociones DESC";
            } elseif ($filtro === 'alfabetico') {
                $query .= " ORDER BY u.nombre ASC";
            } else {
                $query .= " ORDER BY p.fecha_inscripcion DESC";
            }

            $stmt = $this->db->prepare($query);

            if ($club) {
                $stmt->bindParam(':club_id', $club);
            }

            $stmt->execute();
            $peleadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convertir tipos
            $peleadores = array_map([$this, 'convertirTipos'], $peleadores);

            return [
                "success" => true,
                "total" => count($peleadores),
                "peleadores" => $peleadores
            ];
        }

        /**
         * Listar solo peleadores aprobados con toda la info para la vista pública
         */
        public function listarAprobados() {
            $query = "SELECT
                p.id,
                p.apodo,
                p.foto_perfil,
                p.estilo,
                p.genero,
                p.victorias,
                p.derrotas,
                p.empates,
                p.total_promociones,
                p.peso_actual as peso,
                p.altura,
                p.experiencia_anos,
                p.fecha_nacimiento,
                p.documento_identidad as dni,
                p.estado_inscripcion,
                COALESCE(
                    p.edad,
                    CASE
                        WHEN p.fecha_nacimiento IS NULL THEN NULL
                        ELSE YEAR(CURDATE()) - YEAR(p.fecha_nacimiento) - (DATE_FORMAT(CURDATE(), '%m%d') < DATE_FORMAT(p.fecha_nacimiento, '%m%d'))
                    END
                ) as edad,
                CASE
                    WHEN p.peso_actual <= 50 THEN 'Mosca'
                    WHEN p.peso_actual <= 57 THEN 'Pluma'
                    WHEN p.peso_actual <= 61 THEN 'Ligero'
                    WHEN p.peso_actual <= 67 THEN 'Welter'
                    WHEN p.peso_actual <= 73 THEN 'Mediano'
                    WHEN p.peso_actual <= 79 THEN 'Mediopesado'
                    ELSE 'Pesado'
                END as categoria,
                u.nombre,
                u.apellidos as apellido,
                u.email,
                u.telefono,
                c.nombre as club_nombre
                FROM peleadores p
                JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN clubs c ON p.club_id = c.id
                WHERE p.estado_inscripcion = 'aprobado'
                ORDER BY p.total_promociones DESC, u.nombre ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $peleadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convertir tipos
            $peleadores = array_map([$this, 'convertirTipos'], $peleadores);

            return [
                "success" => true,
                "total" => count($peleadores),
                "peleadores" => $peleadores
            ];
        }

        /**
         * Obtener detalle de un peleador
         */
        public function obtenerPorId($id) {
            $query = "SELECT
                p.*, u.nombre, u.apellidos, u.email, u.telefono, u.fecha_registro
                FROM peleadores p
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.id = :id";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $peleador = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$peleador) {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Peleador no encontrado"
                ];
            }

            // Obtener historial de peleas
            $queryPeleas = "SELECT * FROM peleas
                WHERE (peleador_1_id = :id OR peleador_2_id = :id)
                AND resultado != 'pendiente'
                ORDER BY fecha_creacion DESC
                LIMIT 10";

            $stmtPeleas = $this->db->prepare($queryPeleas);
            $stmtPeleas->bindParam(':id', $id);
            $stmtPeleas->execute();
            $historial = $stmtPeleas->fetchAll(PDO::FETCH_ASSOC);

            // Convertir tipos
            $peleador = $this->convertirTipos($peleador);
            $historial = array_map([$this, 'convertirTipos'], $historial);

            return [
                "success" => true,
                "peleador" => $peleador,
                "historial_peleas" => $historial
            ];
        }

        /**
         * Obtener peleador por usuario_id (incluye ficha aunque esté pendiente)
         */
        public function obtenerPorUsuarioId($usuario_id) {
            try {
                $query = "SELECT
                    p.*,
                    u.nombre,
                    u.apellidos,
                    u.email,
                    u.telefono,
                    c.nombre as club_nombre,
                    fc.baked_url,
                    fc.composition_json
                FROM peleadores p
                JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN clubs c ON p.club_id = c.id
                LEFT JOIN fighter_cards fc ON fc.peleador_id = p.id AND fc.is_primary = 1
                WHERE p.usuario_id = :usuario_id
                ORDER BY p.fecha_inscripcion DESC
                LIMIT 1";

                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':usuario_id', $usuario_id);
                $stmt->execute();
                $peleador = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$peleador) {
                    return ["success" => false, "message" => "El usuario no es un peleador"];
                }

                $peleador = $this->convertirTipos($peleador);

                return [
                    "success" => true,
                    "peleador" => $peleador
                ];
            } catch (Exception $e) {
                error_log("Error obtenerPorUsuarioId: " . $e->getMessage());
                return ["success" => false, "message" => "Error al obtener peleador"];
            }
        }

        /**
     * Inscribir nuevo peleador
     */
    public function inscribir($data) {
        
        // 🛠️ DEBUGGING: Ver qué llega realmente
        error_log("--- INTENTO DE INSCRIPCIÓN ---");
        // Si data es muy grande, no lo imprimas todo, pero por ahora sirve
        error_log("DATOS RECIBIDOS (POST/JSON): " . print_r($data, true));
        error_log("ARCHIVOS RECIBIDOS (_FILES): " . print_r($_FILES, true));
        
        // Validar datos requeridos
        $required = ['nombre', 'email', 'password', 'apodo', 'edad',
                     'peso_actual', 'documento_identidad', 'club_id', 'genero'];

        foreach ($required as $field) {
            // Nota: isset devuelve false si es null, así que esto cubre ambos casos
            if (!isset($data[$field]) || $data[$field] === '') {
                http_response_code(400);
                return ["success" => false, "message" => "El campo $field es requerido"];
            }
        }

        // Validar género
        if (!in_array($data['genero'], ['masculino', 'femenino'])) {
            http_response_code(400);
            return ["success" => false, "message" => "El género debe ser 'masculino' o 'femenino'"];
        }

        try {
            $this->db->beginTransaction();

            // 1. Crear usuario
            $queryUsuario = "INSERT INTO usuarios (nombre, apellidos, email, password_hash, telefono, tipo_id, club_id)
                VALUES (:nombre, :apellidos, :email, :password, :telefono, 2, :club_id)";

            $stmtUsuario = $this->db->prepare($queryUsuario);
            $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);
            
            // Manejo seguro de nulos
            $telefono = !empty($data['telefono']) ? $data['telefono'] : null;
            $apellidos = !empty($data['apellidos']) ? $data['apellidos'] : null;
            $club_id = !empty($data['club_id']) ? $data['club_id'] : null;

            $stmtUsuario->bindParam(':nombre', $data['nombre']);
            $stmtUsuario->bindParam(':apellidos', $apellidos);
            $stmtUsuario->bindParam(':email', $data['email']);
            $stmtUsuario->bindParam(':password', $password_hash);
            $stmtUsuario->bindParam(':telefono', $telefono);
            $stmtUsuario->bindParam(':club_id', $club_id);
            $stmtUsuario->execute();

            $usuario_id = $this->db->lastInsertId();

            // 2. Crear perfil de peleador
            $queryPeleador = "INSERT INTO peleadores
                (usuario_id, apodo, fecha_nacimiento, edad, peso_actual, categoria, altura, genero, club_id,
                 estilo, documento_identidad, experiencia_anos, foto_perfil)
                VALUES
                (:usuario_id, :apodo, :fecha_nacimiento, :edad, :peso_actual, :categoria, :altura, :genero,
                 :club_id, :estilo, :documento_identidad, :experiencia_anos, :foto_perfil)";

            $stmtPeleador = $this->db->prepare($queryPeleador);

            $altura = $data['altura'] ?? null;
            $estilo = $data['estilo'] ?? 'fajador';
            $experiencia_anos = $data['experiencia_anos'] ?? 0;
            
            // Inicializar foto como null
            $foto_perfil = null;

            // --- LÓGICA DE FOTO CORREGIDA ---
            
            // 1. Revisar si hay un archivo real subido (prioridad)
            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                
                $uploadDir = __DIR__ . '/../files/peleadores';
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileTmp = $_FILES['foto_perfil']['tmp_name'];
                $fileName = basename($_FILES['foto_perfil']['name']);
                $imageInfo = getimagesize($fileTmp);
                
                if ($imageInfo !== false) {
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    if (empty($ext)) $ext = 'jpg';
                    
                    $newFileName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $destination = $uploadDir . '/' . $newFileName;

                    if (move_uploaded_file($fileTmp, $destination)) {
                        $foto_perfil = 'files/peleadores/' . $newFileName;
                        error_log("✅ FOTO SUBIDA OK: $foto_perfil");
                    } else {
                        error_log("❌ ERROR MOVIENDO ARCHIVO a $destination");
                    }
                } else {
                    error_log("❌ ERROR: El archivo no es una imagen válida");
                }
            } 
            // 2. Si no hay archivo en $_FILES, revisar si hay basura en $data (limpieza)
            else {
                // Aquí estaba el error de sintaxis. Corregido:
                error_log("⚠️ NO se detectó archivo en _FILES['foto_perfil']");
                
                // Si viene texto basura como [object Object], lo ignoramos
                if (isset($data['foto_perfil']) && $data['foto_perfil'] !== '[object Object]') {
                     // Solo si fuera una URL válida o path string lo usaríamos, si no, null
                     // $foto_perfil = $data['foto_perfil']; 
                }
            }

            $stmtPeleador->bindParam(':usuario_id', $usuario_id);
            $stmtPeleador->bindParam(':apodo', $data['apodo']);
            $fecha_nacimiento = !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null;
            $edad = isset($data['edad']) && $data['edad'] !== '' ? (int)$data['edad'] : null;
            $stmtPeleador->bindParam(':fecha_nacimiento', $fecha_nacimiento);
            $stmtPeleador->bindParam(':edad', $edad);
            $stmtPeleador->bindParam(':peso_actual', $data['peso_actual']);
            $categoria = $this->calcularCategoria((float)$data['peso_actual']);
            $stmtPeleador->bindParam(':categoria', $categoria);
            $stmtPeleador->bindParam(':altura', $altura);
            $stmtPeleador->bindParam(':genero', $data['genero']);
            $stmtPeleador->bindParam(':club_id', $data['club_id']);
            $stmtPeleador->bindParam(':estilo', $estilo);
            $stmtPeleador->bindParam(':documento_identidad', $data['documento_identidad']);
            $stmtPeleador->bindParam(':experiencia_anos', $experiencia_anos);
            $stmtPeleador->bindParam(':foto_perfil', $foto_perfil);
            $stmtPeleador->execute();

            $peleador_id = $this->db->lastInsertId();

            // --- GUARDAR TARJETA (baked + composition) ---
            $baked_url = null;
            $raw_composition = $data['composition_json'] ?? null;
            $composition_data = null;
            if ($raw_composition) {
                $composition_data = json_decode($raw_composition, true);
            }

            // 1) Imagen baked (si llega)
            if (isset($_FILES['baked_image']) && $_FILES['baked_image']['error'] === UPLOAD_ERR_OK) {
                $peleadorCardDir = __DIR__ . "/../files/peleadores/$peleador_id";
                if (!is_dir($peleadorCardDir)) mkdir($peleadorCardDir, 0777, true);

                $fileTmp = $_FILES['baked_image']['tmp_name'];
                $fileName = 'card_' . time() . '.png';
                $destination = $peleadorCardDir . '/' . $fileName;

                if (move_uploaded_file($fileTmp, $destination)) {
                    $baked_url = "files/peleadores/$peleador_id/" . $fileName;
                    error_log("✅ Card guardada en: $baked_url");
                }
            }

            // 2) Archivos de capas (si llegan) + reemplazo en JSON
            $peleadorDir = __DIR__ . "/../files/peleadores/$peleador_id";
            $layerDir = "$peleadorDir/layers";
            if (!is_dir($layerDir)) mkdir($layerDir, 0777, true);

            foreach ($_FILES as $key => $file) {
                if (strpos($key, 'layer_file_') === 0 && $file['error'] === UPLOAD_ERR_OK) {
                    $layerId = str_replace('layer_file_', '', $key);
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'png';
                    $newName = 'layer_' . $layerId . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($file['tmp_name'], $layerDir . '/' . $newName)) {
                        $newUrl = "files/peleadores/$peleador_id/layers/" . $newName;
                        if ($composition_data && isset($composition_data['layers'])) {
                            foreach ($composition_data['layers'] as &$layer) {
                                if ((string)$layer['id'] === (string)$layerId) {
                                    $layer['uri'] = $newUrl;
                                }
                            }
                        }
                    }
                }
            }

            $composition_json = $composition_data ? json_encode($composition_data) : $raw_composition;

            // 3) Insertar fighter_cards si hay baked o composition
            if ($baked_url || $composition_json) {
                $queryCard = "INSERT INTO fighter_cards (peleador_id, is_primary, baked_url, composition_json)
                              VALUES (:pid, 1, :baked, :json)";
                $stmtCard = $this->db->prepare($queryCard);
                $stmtCard->bindParam(':pid', $peleador_id);
                $stmtCard->bindParam(':baked', $baked_url);
                $stmtCard->bindParam(':json', $composition_json);
                $stmtCard->execute();
                error_log("✅ REGISTRO EN fighter_cards CREADO (baked_url: " . ($baked_url ? $baked_url : 'NULL') . ")");
            } else {
                error_log("⚠️ No se creó registro en fighter_cards (baked_url y composition_json están vacíos)");
            }

            $this->db->commit();

            $ticketPdf = null;
            try {
                require_once __DIR__ . '/../services/PdfService.php';
                $pdfService = new PdfService($this->db);
                $eventoTicket = $this->obtenerEventoParaTicket();

                $ticketPdf = $pdfService->generarTicketInscripcionPeleador([
                    'peleador_id' => (int)$peleador_id,
                    'usuario_id' => (int)$usuario_id,
                    'nombre' => trim(($data['nombre'] ?? '') . ' ' . ($apellidos ?? '')),
                    'apodo' => $data['apodo'] ?? '',
                    'dni' => $data['documento_identidad'] ?? '',
                    'telefono' => $telefono ?? '',
                    'estado_inscripcion' => 'PENDIENTE',
                    'monto' => $eventoTicket['precio_inscripcion_peleador'] ?? 0,
                    'evento_nombre' => $eventoTicket['nombre'] ?? 'Evento por anunciar',
                    'evento_fecha' => $eventoTicket['fecha'] ?? null,
                    'evento_hora' => $eventoTicket['hora'] ?? null,
                    'evento_direccion' => $eventoTicket['direccion'] ?? 'Por confirmar',
                ]);
            } catch (Exception $e) {
                error_log("Error generando ticket PDF de peleador: " . $e->getMessage());
            }

            return [
                "success" => true,
                "message" => "Inscripción exitosa.",
                "peleador_id" => $peleador_id,
                "baked_url" => $baked_url,
                "ticket_pdf_url" => ($ticketPdf && !empty($ticketPdf['success'])) ? ($ticketPdf['url'] ?? null) : null,
                "ticket_pdf_template" => ($ticketPdf && !empty($ticketPdf['success'])) ? ($ticketPdf['template_code'] ?? null) : null,
                "debug_info" => [
                    "files_received" => $_FILES,
                    "foto_final" => $foto_perfil
                ]
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("PDO Error: " . $e->getMessage());
            
            if ($e->getCode() == 23000) {
                http_response_code(409);
                return ["success" => false, "message" => "El email o documento ya está registrado"];
            }
            http_response_code(500);
            return ["success" => false, "message" => "Error interno", "error" => $e->getMessage()];
        }
    }

        /**
         * Obtener estado de inscripción del peleador en el evento activo
         */
        public function getInscripcionEvento($peleador_id) {
            $traceId = $this->newPdfTraceId('get');
            $this->logPdfFlow($traceId, 'getInscripcionEvento.start', [
                'peleador_id' => (int)$peleador_id,
            ]);
            try {
                // Estado del peleador
                $queryPeleador = "SELECT id, estado_inscripcion FROM peleadores WHERE id = :peleador_id LIMIT 1";
                $stmtPeleador = $this->db->prepare($queryPeleador);
                $stmtPeleador->bindParam(':peleador_id', $peleador_id);
                $stmtPeleador->execute();
                $peleador = $stmtPeleador->fetch(PDO::FETCH_ASSOC);

                if (!$peleador) {
                    $this->logPdfFlow($traceId, 'getInscripcionEvento.peleador_not_found', [
                        'peleador_id' => (int)$peleador_id,
                    ]);
                    http_response_code(404);
                    return [
                        "success" => false,
                        "message" => "Peleador no encontrado",
                        "debug_trace_id" => $traceId
                    ];
                }

                // Evento disponible para inscripción (próximo o activo)
                $queryEvento = "SELECT id, nombre, fecha, hora, direccion, precio_inscripcion_peleador, estado
                                FROM eventos
                                WHERE estado IN ('proximamente', 'activo')
                                ORDER BY
                                    CASE WHEN estado = 'proximamente' THEN 0 ELSE 1 END,
                                    fecha ASC
                                LIMIT 1";
                $stmtEvento = $this->db->prepare($queryEvento);
                $stmtEvento->execute();
                $evento = $stmtEvento->fetch(PDO::FETCH_ASSOC);

                if (!$evento) {
                    $this->logPdfFlow($traceId, 'getInscripcionEvento.no_evento', [
                        'peleador_id' => (int)$peleador_id,
                    ]);
                    return [
                        "success" => true,
                        "estado_peleador" => $peleador['estado_inscripcion'],
                        "evento" => null,
                        "inscripcion" => null,
                        "debug_trace_id" => $traceId
                    ];
                }

                // Inscripción del peleador para el evento activo
                $queryInscripcion = "SELECT id, estado_pago, monto_pagado, fecha_inscripcion, fecha_pago,
                                            metodo_pago, comprobante_pago, notas_admin
                                     FROM inscripciones_eventos
                                     WHERE peleador_id = :peleador_id AND evento_id = :evento_id
                                     LIMIT 1";
                $stmtInscripcion = $this->db->prepare($queryInscripcion);
                $stmtInscripcion->bindParam(':peleador_id', $peleador_id);
                $stmtInscripcion->bindParam(':evento_id', $evento['id']);
                $stmtInscripcion->execute();
                $inscripcion = $stmtInscripcion->fetch(PDO::FETCH_ASSOC);

                $ticketPdf = $this->obtenerDocumentoPdf('fighter_inscripcion_ticket', 'peleador', (int)$peleador_id);
                if ($inscripcion) {
                    $comprobantePdf = $this->asegurarComprobanteInscripcionPdf((int)$inscripcion['id'], $traceId, 'getInscripcionEvento.read');
                    if ($comprobantePdf) {
                        $inscripcion['comprobante_pdf_url'] = $comprobantePdf['file_url'];
                        $inscripcion['comprobante_pdf_token'] = $comprobantePdf['verification_token'];
                    }
                    $inscripcion['comprobante_pdf_view_api_url'] = '/api/comprobantes/viewPdf/' . (int)$inscripcion['id'];
                    $inscripcion['comprobante_pdf_regenerar_api_url'] = '/api/comprobantes/pdf/' . (int)$inscripcion['id'];
                }

                $this->logPdfFlow($traceId, 'getInscripcionEvento.result', [
                    'estado_peleador' => $peleador['estado_inscripcion'],
                    'evento_id' => (int)($evento['id'] ?? 0),
                    'inscripcion_id' => $inscripcion ? (int)$inscripcion['id'] : null,
                    'ticket_pdf' => $ticketPdf,
                    'comprobante_pdf_url' => $inscripcion['comprobante_pdf_url'] ?? null,
                ]);

                return [
                    "success" => true,
                    "estado_peleador" => $peleador['estado_inscripcion'],
                    "evento" => $evento ? $this->convertirTipos($evento) : null,
                    "inscripcion" => $inscripcion ? $this->convertirTipos($inscripcion) : null,
                    "ticket_pdf_url" => $ticketPdf['file_url'] ?? null,
                    "ticket_pdf_token" => $ticketPdf['verification_token'] ?? null,
                    "debug_trace_id" => $traceId
                ];
            } catch (Exception $e) {
                error_log("Error getInscripcionEvento: " . $e->getMessage());
                $this->logPdfFlow($traceId, 'getInscripcionEvento.exception', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                ]);
                http_response_code(500);
                return [
                    "success" => false,
                    "message" => "Error al obtener estado de inscripción",
                    "debug_trace_id" => $traceId
                ];
            }
        }

        /**
         * Regenerar manualmente el comprobante PDF de la inscripción del peleador.
         * Útil para iterar diseño sin cambiar estado de pago.
         */
        public function regenerarComprobante($peleador_id, $data = []) {
            $traceId = $this->newPdfTraceId('regen');
            $this->logPdfFlow($traceId, 'regenerarComprobante.start', [
                'peleador_id' => (int)$peleador_id,
                'payload' => $data,
            ]);

            try {
                $peleadorId = (int)$peleador_id;
                $inscripcionId = (int)($data['inscripcion_id'] ?? 0);
                $eventoId = (int)($data['evento_id'] ?? 0);

                if ($peleadorId <= 0) {
                    http_response_code(400);
                    return [
                        "success" => false,
                        "message" => "peleador_id inválido",
                        "debug_trace_id" => $traceId
                    ];
                }

                $stmtPeleador = $this->db->prepare("SELECT id FROM peleadores WHERE id = :id LIMIT 1");
                $stmtPeleador->execute([':id' => $peleadorId]);
                if (!$stmtPeleador->fetch(PDO::FETCH_ASSOC)) {
                    http_response_code(404);
                    return [
                        "success" => false,
                        "message" => "Peleador no encontrado",
                        "debug_trace_id" => $traceId
                    ];
                }

                $inscripcion = null;
                if ($inscripcionId > 0) {
                    $stmtIns = $this->db->prepare("SELECT id, evento_id
                                                   FROM inscripciones_eventos
                                                   WHERE id = :id AND peleador_id = :peleador_id
                                                   LIMIT 1");
                    $stmtIns->execute([
                        ':id' => $inscripcionId,
                        ':peleador_id' => $peleadorId,
                    ]);
                    $inscripcion = $stmtIns->fetch(PDO::FETCH_ASSOC) ?: null;
                } elseif ($eventoId > 0) {
                    $stmtIns = $this->db->prepare("SELECT id, evento_id
                                                   FROM inscripciones_eventos
                                                   WHERE peleador_id = :peleador_id AND evento_id = :evento_id
                                                   ORDER BY id DESC
                                                   LIMIT 1");
                    $stmtIns->execute([
                        ':peleador_id' => $peleadorId,
                        ':evento_id' => $eventoId,
                    ]);
                    $inscripcion = $stmtIns->fetch(PDO::FETCH_ASSOC) ?: null;
                } else {
                    $stmtIns = $this->db->prepare("SELECT ie.id, ie.evento_id
                                                   FROM inscripciones_eventos ie
                                                   INNER JOIN eventos e ON ie.evento_id = e.id
                                                   WHERE ie.peleador_id = :peleador_id
                                                     AND e.estado IN ('proximamente', 'activo')
                                                   ORDER BY
                                                     CASE WHEN e.estado = 'proximamente' THEN 0 ELSE 1 END,
                                                     e.fecha ASC,
                                                     ie.id DESC
                                                   LIMIT 1");
                    $stmtIns->execute([':peleador_id' => $peleadorId]);
                    $inscripcion = $stmtIns->fetch(PDO::FETCH_ASSOC) ?: null;

                    // Fallback: última inscripción histórica del peleador.
                    if (!$inscripcion) {
                        $stmtUltima = $this->db->prepare("SELECT id, evento_id
                                                          FROM inscripciones_eventos
                                                          WHERE peleador_id = :peleador_id
                                                          ORDER BY id DESC
                                                          LIMIT 1");
                        $stmtUltima->execute([':peleador_id' => $peleadorId]);
                        $inscripcion = $stmtUltima->fetch(PDO::FETCH_ASSOC) ?: null;
                    }
                }

                if (!$inscripcion) {
                    http_response_code(404);
                    $this->logPdfFlow($traceId, 'regenerarComprobante.no_inscripcion', [
                        'peleador_id' => $peleadorId,
                        'inscripcion_id' => $inscripcionId,
                        'evento_id' => $eventoId,
                    ]);
                    return [
                        "success" => false,
                        "message" => "No se encontró inscripción para regenerar comprobante",
                        "debug_trace_id" => $traceId
                    ];
                }

                $inscripcionTargetId = (int)$inscripcion['id'];
                $inscripcionPdfData = $this->obtenerDatosInscripcionParaPdf($inscripcionTargetId);
                if (!$inscripcionPdfData) {
                    http_response_code(500);
                    $this->logPdfFlow($traceId, 'regenerarComprobante.no_pdf_data', [
                        'inscripcion_id' => $inscripcionTargetId,
                    ]);
                    return [
                        "success" => false,
                        "message" => "No se pudieron obtener datos para el comprobante",
                        "debug_trace_id" => $traceId
                    ];
                }

                require_once __DIR__ . '/../services/PdfService.php';
                $pdfService = new PdfService($this->db);
                $inscripcionPdfData['_debug_trace_id'] = $traceId;
                $inscripcionPdfData['_debug_source'] = 'regenerarComprobante.manual';
                $resultado = $pdfService->generarComprobanteInscripcionPeleador($inscripcionPdfData);

                $this->logPdfFlow($traceId, 'regenerarComprobante.generation_result', [
                    'inscripcion_id' => $inscripcionTargetId,
                    'resultado' => $resultado,
                ]);

                if (empty($resultado['success'])) {
                    http_response_code(500);
                    return [
                        "success" => false,
                        "message" => $resultado['message'] ?? 'No se pudo regenerar el comprobante',
                        "error" => $resultado['error'] ?? null,
                        "debug_trace_id" => $traceId
                    ];
                }

                $doc = $this->obtenerDocumentoPdf('fighter_inscripcion_comprobante', 'inscripcion_evento', $inscripcionTargetId);
                return [
                    "success" => true,
                    "message" => "Comprobante regenerado correctamente",
                    "inscripcion_id" => $inscripcionTargetId,
                    "comprobante_pdf_url" => $doc['file_url'] ?? ($resultado['url'] ?? null),
                    "comprobante_pdf_token" => $doc['verification_token'] ?? ($resultado['token'] ?? null),
                    "comprobante_pdf_view_api_url" => '/api/comprobantes/viewPdf/' . $inscripcionTargetId,
                    "comprobante_pdf_regenerar_api_url" => '/api/comprobantes/pdf/' . $inscripcionTargetId,
                    "debug_trace_id" => $traceId
                ];
            } catch (Exception $e) {
                error_log("Error regenerarComprobante: " . $e->getMessage());
                $this->logPdfFlow($traceId, 'regenerarComprobante.exception', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                ]);
                http_response_code(500);
                return [
                    "success" => false,
                    "message" => "Error al regenerar comprobante",
                    "debug_trace_id" => $traceId
                ];
            }
        }

        /**
         * Obtener manager activo para contacto por WhatsApp
         * @param string $rol - rol del manager (manager_peleadores, manager_cobros, manager_general)
         */
        public function getManagerContacto($rol = 'manager_peleadores') {
            try {
                $rolesValidos = ['manager_peleadores', 'manager_cobros', 'manager_general'];
                if (!in_array($rol, $rolesValidos)) {
                    $rol = 'manager_peleadores';
                }

                $query = "SELECT
                            id,
                            nombre_visible,
                            telefono_whatsapp,
                            mensaje_base,
                            rol
                          FROM managers_contacto
                          WHERE activo = 1
                            AND recibe_nuevos = 1
                            AND rol = :rol
                          ORDER BY prioridad ASC, total_asignaciones ASC, ultima_asignacion_at ASC, id ASC
                          LIMIT 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':rol' => $rol]);
                $manager = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$manager) {
                    return [
                        "success" => true,
                        "manager" => null
                    ];
                }

                return [
                    "success" => true,
                    "manager" => $manager
                ];
            } catch (PDOException $e) {
                // Tabla inexistente o error de migración: no romper la pantalla
                error_log("Error getManagerContacto: " . $e->getMessage());
                return [
                    "success" => false,
                    "message" => "No se pudo obtener el manager de contacto"
                ];
            }
        }

        /**
         * Registrar asignacion de manager a peleador
         */
        public function registrarAsignacion($peleadorId) {
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $managerId = $data['manager_id'] ?? null;
                $motivo = $data['motivo'] ?? 'registro';
                $canal = $data['canal'] ?? 'whatsapp';

                if (!$managerId || !$peleadorId) {
                    return ["success" => false, "message" => "Faltan datos"];
                }

                // Verificar si ya existe asignacion abierta para este peleador+manager+motivo
                $stmt = $this->db->prepare(
                    "SELECT id FROM manager_asignaciones
                     WHERE peleador_id = :pid AND manager_id = :mid AND motivo = :motivo AND estado IN ('asignado','contactado','en_proceso')
                     LIMIT 1"
                );
                $stmt->execute([':pid' => $peleadorId, ':mid' => $managerId, ':motivo' => $motivo]);
                if ($stmt->fetch()) {
                    return ["success" => true, "message" => "Ya existe asignacion activa"];
                }

                // Insertar asignacion
                $stmt = $this->db->prepare(
                    "INSERT INTO manager_asignaciones (peleador_id, manager_id, motivo, canal, estado)
                     VALUES (:pid, :mid, :motivo, :canal, 'asignado')"
                );
                $stmt->execute([
                    ':pid' => $peleadorId,
                    ':mid' => $managerId,
                    ':motivo' => $motivo,
                    ':canal' => $canal,
                ]);

                // Actualizar conteo del manager
                $stmt = $this->db->prepare(
                    "UPDATE managers_contacto SET total_asignaciones = total_asignaciones + 1, ultima_asignacion_at = NOW() WHERE id = :mid"
                );
                $stmt->execute([':mid' => $managerId]);

                return ["success" => true, "asignacion_id" => $this->db->lastInsertId()];
            } catch (PDOException $e) {
                error_log("Error registrarAsignacion: " . $e->getMessage());
                return ["success" => false, "message" => "Error al registrar asignacion"];
            }
        }

        /**
         * Crear inscripción al evento (sin pago aún)
         * El peleador decide inscribirse y luego elige método de pago
         */
        public function crearInscripcion($peleador_id) {
            $rawBody = file_get_contents('php://input');
            $traceId = $this->newPdfTraceId('crear');
            $this->logPdfFlow($traceId, 'crearInscripcion.start', [
                'peleador_id' => (int)$peleador_id,
                'raw_body' => $rawBody,
                'files_keys' => array_keys($_FILES ?? []),
            ]);
            try {
                $data = json_decode($rawBody, true);
                $evento_id = $data['evento_id'] ?? null;
                $this->logPdfFlow($traceId, 'crearInscripcion.body_decoded', [
                    'data' => $data,
                    'evento_id' => $evento_id,
                ]);

                if (!$evento_id) {
                    $this->logPdfFlow($traceId, 'crearInscripcion.validation_error', [
                        'reason' => 'evento_id es requerido',
                    ]);
                    http_response_code(400);
                    return ["success" => false, "message" => "evento_id es requerido", "debug_trace_id" => $traceId];
                }

                // Verificar que el peleador existe y no está rechazado
                $stmt = $this->db->prepare("SELECT id, estado_inscripcion FROM peleadores WHERE id = :pid LIMIT 1");
                $stmt->execute([':pid' => $peleador_id]);
                $peleador = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$peleador) {
                    $this->logPdfFlow($traceId, 'crearInscripcion.peleador_not_found', ['peleador_id' => (int)$peleador_id]);
                    http_response_code(404);
                    return ["success" => false, "message" => "Peleador no encontrado", "debug_trace_id" => $traceId];
                }
                if ($peleador['estado_inscripcion'] === 'rechazado') {
                    $this->logPdfFlow($traceId, 'crearInscripcion.peleador_rechazado', ['peleador_id' => (int)$peleador_id]);
                    http_response_code(400);
                    return ["success" => false, "message" => "Tu perfil ha sido rechazado. Contacta al administrador.", "debug_trace_id" => $traceId];
                }

                // Verificar que el evento existe y está activo
                $stmt = $this->db->prepare("SELECT id, precio_inscripcion_peleador FROM eventos WHERE id = :eid AND estado IN ('proximamente', 'activo') LIMIT 1");
                $stmt->execute([':eid' => $evento_id]);
                $evento = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$evento) {
                    $this->logPdfFlow($traceId, 'crearInscripcion.evento_not_available', ['evento_id' => (int)$evento_id]);
                    http_response_code(400);
                    return ["success" => false, "message" => "Evento no disponible para inscripción", "debug_trace_id" => $traceId];
                }

                // Verificar si ya existe inscripción
                $stmt = $this->db->prepare("SELECT id FROM inscripciones_eventos WHERE peleador_id = :pid AND evento_id = :eid LIMIT 1");
                $stmt->execute([':pid' => $peleador_id, ':eid' => $evento_id]);
                $inscripcionExistente = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($inscripcionExistente) {
                    $inscripcionIdExistente = (int)$inscripcionExistente['id'];
                    $this->logPdfFlow($traceId, 'crearInscripcion.already_exists', [
                        'inscripcion_id' => $inscripcionIdExistente,
                    ]);
                    $comprobanteExistente = $this->asegurarComprobanteInscripcionPdf($inscripcionIdExistente, $traceId, 'crearInscripcion.exists');
                    return [
                        "success" => true,
                        "message" => "Ya estás inscrito en este evento",
                        "inscripcion_id" => $inscripcionIdExistente,
                        "comprobante_pdf_url" => $comprobanteExistente['file_url'] ?? null,
                        "debug_trace_id" => $traceId
                    ];
                }

                // Crear inscripción con estado inscrito, sin método de pago
                $stmt = $this->db->prepare(
                    "INSERT INTO inscripciones_eventos (peleador_id, evento_id, estado_pago, monto_pagado)
                     VALUES (:pid, :eid, 'inscrito', :monto)"
                );
                $stmt->execute([
                    ':pid' => $peleador_id,
                    ':eid' => $evento_id,
                    ':monto' => $evento['precio_inscripcion_peleador']
                ]);

                $inscripcion_id = $this->db->lastInsertId();
                error_log("✅ INSCRIPCIÓN CREADA (ID: $inscripcion_id) peleador $peleador_id → evento $evento_id");
                $this->logPdfFlow($traceId, 'crearInscripcion.inserted', [
                    'inscripcion_id' => (int)$inscripcion_id,
                    'evento_id' => (int)$evento_id,
                ]);

                $comprobantePdf = $this->asegurarComprobanteInscripcionPdf((int)$inscripcion_id, $traceId, 'crearInscripcion.new');
                $this->logPdfFlow($traceId, 'crearInscripcion.pdf_result', [
                    'inscripcion_id' => (int)$inscripcion_id,
                    'pdf' => $comprobantePdf,
                ]);

                return [
                    "success" => true,
                    "message" => "Inscripción creada. Ahora selecciona tu método de pago.",
                    "inscripcion_id" => (int)$inscripcion_id,
                    "comprobante_pdf_url" => $comprobantePdf['file_url'] ?? null,
                    "debug_trace_id" => $traceId
                ];
            } catch (PDOException $e) {
                error_log("Error crearInscripcion: " . $e->getMessage());
                $this->logPdfFlow($traceId, 'crearInscripcion.exception', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'line' => $e->getLine(),
                ]);
                http_response_code(500);
                return ["success" => false, "message" => "Error al crear inscripción", "debug_trace_id" => $traceId];
            }
        }

        /**
         * Inscribir peleador al evento activo con método de pago y comprobante opcional
         */
        public function inscribirEvento($peleador_id, $data) {
            $traceId = $this->newPdfTraceId('inscribir');
            $this->logPdfFlow($traceId, 'inscribirEvento.start', [
                'peleador_id' => (int)$peleador_id,
                'data' => $data,
                'files' => $_FILES ?? [],
            ]);
            try {
                if (!isset($data['evento_id']) || !isset($data['metodo_pago']) || $data['metodo_pago'] === '') {
                    $this->logPdfFlow($traceId, 'inscribirEvento.validation_error', [
                        'reason' => 'evento_id y metodo_pago son requeridos',
                    ]);
                    http_response_code(400);
                    return [
                        "success" => false,
                        "message" => "evento_id y metodo_pago son requeridos",
                        "debug_trace_id" => $traceId
                    ];
                }
                $metodoPago = strtolower(trim($data['metodo_pago']));

                // Verificar estado del peleador
                $queryPeleador = "SELECT id, estado_inscripcion FROM peleadores WHERE id = :peleador_id LIMIT 1";
                $stmtPeleador = $this->db->prepare($queryPeleador);
                $stmtPeleador->bindParam(':peleador_id', $peleador_id);
                $stmtPeleador->execute();
                $peleador = $stmtPeleador->fetch(PDO::FETCH_ASSOC);

                if (!$peleador) {
                    $this->logPdfFlow($traceId, 'inscribirEvento.peleador_not_found', ['peleador_id' => (int)$peleador_id]);
                    http_response_code(404);
                    return [
                        "success" => false,
                        "message" => "Peleador no encontrado",
                        "debug_trace_id" => $traceId
                    ];
                }

                // ✅ MODIFICADO: Permitir inscripción incluso en estado pendiente
                // Ya no se requiere aprobación previa para pagar
                // El pago es requisito para la aprobación, no al revés
                if ($peleador['estado_inscripcion'] === 'rechazado') {
                    $this->logPdfFlow($traceId, 'inscribirEvento.peleador_rechazado', ['peleador_id' => (int)$peleador_id]);
                    http_response_code(400);
                    return [
                        "success" => false,
                        "message" => "Tu perfil ha sido rechazado. Contacta al administrador.",
                        "debug_trace_id" => $traceId
                    ];
                }

                // Validar método de pago activo y requisitos
                $queryMetodo = "SELECT codigo, requiere_comprobante, activo
                                FROM metodos_pago
                                WHERE codigo = :codigo
                                LIMIT 1";
                $stmtMetodo = $this->db->prepare($queryMetodo);
                $stmtMetodo->bindParam(':codigo', $metodoPago);
                $stmtMetodo->execute();
                $metodo = $stmtMetodo->fetch(PDO::FETCH_ASSOC);

                if (!$metodo || (int)$metodo['activo'] !== 1) {
                    $this->logPdfFlow($traceId, 'inscribirEvento.metodo_invalido', [
                        'metodo_pago' => $metodoPago,
                        'metodo_db' => $metodo,
                    ]);
                    http_response_code(400);
                    return [
                        "success" => false,
                        "message" => "Método de pago inválido o inactivo",
                        "debug_trace_id" => $traceId
                    ];
                }

                $tieneComprobanteArchivo = isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK;
                $tieneComprobanteTexto = isset($data['comprobante_pago']) && trim((string)$data['comprobante_pago']) !== '';
                if ((int)$metodo['requiere_comprobante'] === 1 && !$tieneComprobanteArchivo && !$tieneComprobanteTexto) {
                    $this->logPdfFlow($traceId, 'inscribirEvento.comprobante_required_missing', [
                        'metodo_pago' => $metodoPago,
                        'requiere_comprobante' => (int)$metodo['requiere_comprobante'],
                    ]);
                    http_response_code(400);
                    return [
                        "success" => false,
                        "message" => "Este método de pago requiere comprobante",
                        "debug_trace_id" => $traceId
                    ];
                }

                // Verificar evento válido para inscripción
                $queryEvento = "SELECT id, precio_inscripcion_peleador
                                FROM eventos
                                WHERE id = :evento_id
                                  AND estado IN ('proximamente', 'activo')
                                LIMIT 1";
                $stmtEvento = $this->db->prepare($queryEvento);
                $stmtEvento->bindParam(':evento_id', $data['evento_id']);
                $stmtEvento->execute();
                $evento = $stmtEvento->fetch(PDO::FETCH_ASSOC);

                if (!$evento) {
                    $this->logPdfFlow($traceId, 'inscribirEvento.evento_not_available', [
                        'evento_id' => (int)$data['evento_id'],
                    ]);
                    http_response_code(404);
                    return [
                        "success" => false,
                        "message" => "Evento no encontrado o no disponible",
                        "debug_trace_id" => $traceId
                    ];
                }

                // Comprobante opcional
                $comprobantePath = null;
                if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../files/comprobantes';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $ext = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
                    if (empty($ext)) $ext = 'jpg';
                    $filename = 'inscripcion_' . $peleador_id . '_' . time() . '.' . $ext;
                    $destination = $uploadDir . '/' . $filename;

                    if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $destination)) {
                        $comprobantePath = 'files/comprobantes/' . $filename;
                        $this->logPdfFlow($traceId, 'inscribirEvento.comprobante_uploaded', [
                            'comprobante_path' => $comprobantePath,
                        ]);
                    } else {
                        $this->logPdfFlow($traceId, 'inscribirEvento.comprobante_upload_failed', [
                            'destination' => $destination,
                            'file' => $_FILES['comprobante'] ?? null,
                        ]);
                    }
                }

                $this->db->beginTransaction();

                // Verificar si ya existe inscripción (creada automáticamente en el registro)
                $queryExiste = "SELECT id FROM inscripciones_eventos
                                WHERE peleador_id = :peleador_id AND evento_id = :evento_id
                                LIMIT 1";
                $stmtExiste = $this->db->prepare($queryExiste);
                $stmtExiste->bindParam(':peleador_id', $peleador_id);
                $stmtExiste->bindParam(':evento_id', $data['evento_id']);
                $stmtExiste->execute();
                $inscripcionExistente = $stmtExiste->fetch(PDO::FETCH_ASSOC);

                if ($inscripcionExistente) {
                    // ACTUALIZAR inscripción existente con método de pago y comprobante → estado pendiente
                    $queryUpdate = "UPDATE inscripciones_eventos
                                    SET estado_pago = 'pendiente',
                                        metodo_pago = :metodo_pago,
                                        comprobante_pago = :comprobante_pago,
                                        monto_pagado = :monto_pagado,
                                        fecha_pago = NOW()
                                    WHERE id = :inscripcion_id";
                    $stmtUpdate = $this->db->prepare($queryUpdate);
                    $stmtUpdate->bindParam(':metodo_pago', $metodoPago);
                    $stmtUpdate->bindParam(':comprobante_pago', $comprobantePath);
                    $stmtUpdate->bindParam(':monto_pagado', $evento['precio_inscripcion_peleador']);
                    $stmtUpdate->bindParam(':inscripcion_id', $inscripcionExistente['id']);
                    $stmtUpdate->execute();
                    $inscripcionId = $inscripcionExistente['id'];
                    error_log("✅ INSCRIPCIÓN ACTUALIZADA (ID: $inscripcionId) con método $metodoPago");
                    $this->logPdfFlow($traceId, 'inscribirEvento.updated', [
                        'inscripcion_id' => (int)$inscripcionId,
                        'metodo_pago' => $metodoPago,
                    ]);
                } else {
                    // INSERTAR nueva inscripción (caso de fallback)
                    $queryInsert = "INSERT INTO inscripciones_eventos
                                    (peleador_id, evento_id, estado_pago, monto_pagado, metodo_pago, comprobante_pago, fecha_pago)
                                    VALUES
                                    (:peleador_id, :evento_id, 'pendiente', :monto_pagado, :metodo_pago, :comprobante_pago, NOW())";
                    $stmtInsert = $this->db->prepare($queryInsert);
                    $stmtInsert->bindParam(':peleador_id', $peleador_id);
                    $stmtInsert->bindParam(':evento_id', $data['evento_id']);
                    $stmtInsert->bindParam(':monto_pagado', $evento['precio_inscripcion_peleador']);
                    $stmtInsert->bindParam(':metodo_pago', $metodoPago);
                    $stmtInsert->bindParam(':comprobante_pago', $comprobantePath);
                    $stmtInsert->execute();
                    $inscripcionId = $this->db->lastInsertId();
                    error_log("✅ INSCRIPCIÓN NUEVA CREADA (ID: $inscripcionId)");
                    $this->logPdfFlow($traceId, 'inscribirEvento.inserted', [
                        'inscripcion_id' => (int)$inscripcionId,
                        'metodo_pago' => $metodoPago,
                    ]);
                }

                $this->db->commit();

                $comprobantePdf = $this->asegurarComprobanteInscripcionPdf((int)$inscripcionId, $traceId, 'inscribirEvento.save', true);
                $this->logPdfFlow($traceId, 'inscribirEvento.pdf_result', [
                    'inscripcion_id' => (int)$inscripcionId,
                    'pdf' => $comprobantePdf,
                ]);

                return [
                    "success" => true,
                    "message" => "Inscripción registrada correctamente",
                    "inscripcion_id" => (int)$inscripcionId,
                    "comprobante_pdf_url" => $comprobantePdf['file_url'] ?? null,
                    "debug_trace_id" => $traceId
                ];
            } catch (PDOException $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }

                // Duplicado por unique(peleador_id, evento_id)
                if ($e->getCode() == 23000) {
                    $this->logPdfFlow($traceId, 'inscribirEvento.duplicate', [
                        'error' => $e->getMessage(),
                        'code' => $e->getCode(),
                    ]);
                    http_response_code(400);
                    return [
                        "success" => false,
                        "message" => "Ya estás inscrito en este evento",
                        "debug_trace_id" => $traceId
                    ];
                }

                error_log("Error inscribirEvento: " . $e->getMessage());
                $this->logPdfFlow($traceId, 'inscribirEvento.exception', [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'line' => $e->getLine(),
                ]);
                http_response_code(500);
                return [
                    "success" => false,
                    "message" => "Error al registrar inscripción",
                    "debug_trace_id" => $traceId
                ];
            }
        }

        /**
         * Ranking de popularidad
         */
        public function ranking() {
            $query = "SELECT
                p.id, p.apodo, p.foto_perfil, p.total_promociones, p.genero,
                p.victorias, p.derrotas,
                u.nombre, u.apellidos,
                c.nombre as club_nombre
                FROM peleadores p
                JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN clubs c ON p.club_id = c.id
                WHERE p.estado_inscripcion = 'aprobado'
                ORDER BY p.total_promociones DESC
                LIMIT 20";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convertir tipos
            $ranking = array_map([$this, 'convertirTipos'], $ranking);

            return [
                "success" => true,
                "ranking" => $ranking
            ];
        }

        /**
         * Verificar si un DNI ya está registrado
         */
        public function verificarDNI($dni) {
            $query = "SELECT COUNT(*) as count FROM peleadores WHERE documento_identidad = :dni";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':dni', $dni);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                "success" => true,
                "disponible" => $result['count'] == 0,
                "mensaje" => $result['count'] == 0
                    ? "DNI disponible"
                    : "Este DNI ya está registrado"
            ];
        }

        private function newPdfTraceId($prefix = 'pdf') {
            return $prefix . '_' . date('Ymd_His') . '_' . substr(md5(uniqid((string)mt_rand(), true)), 0, 8);
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
                error_log('logPdfFlow error: ' . $e->getMessage());
            }
        }

        /**
         * Convertir tipos de datos para JSON
         */
        private function calcularCategoria($peso) {
            if ($peso <= 50) return 'Mosca';
            if ($peso <= 57) return 'Pluma';
            if ($peso <= 61) return 'Ligero';
            if ($peso <= 67) return 'Welter';
            if ($peso <= 73) return 'Mediano';
            if ($peso <= 79) return 'Mediopesado';
            return 'Pesado';
        }

        private function obtenerEventoParaTicket() {
            try {
                $query = "SELECT id, nombre, fecha, hora, direccion, precio_inscripcion_peleador
                          FROM eventos
                          WHERE estado IN ('proximamente', 'activo')
                          ORDER BY
                            CASE WHEN estado = 'proximamente' THEN 0 ELSE 1 END,
                            fecha ASC
                          LIMIT 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute();
                $evento = $stmt->fetch(PDO::FETCH_ASSOC);

                return $evento ?: null;
            } catch (Exception $e) {
                error_log("Error obtenerEventoParaTicket: " . $e->getMessage());
                return null;
            }
        }

        private function obtenerDocumentoPdf($documentType, $entityType, $entityId) {
            try {
                $query = "SELECT id, file_path, file_url, verification_token
                          FROM pdf_documents
                          WHERE document_type = :document_type
                            AND entity_type = :entity_type
                            AND entity_id = :entity_id
                          ORDER BY id DESC
                          LIMIT 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    ':document_type' => $documentType,
                    ':entity_type' => $entityType,
                    ':entity_id' => $entityId,
                ]);
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (Exception $e) {
                error_log("Error obtenerDocumentoPdf: " . $e->getMessage());
                return null;
            }
        }

        private function asegurarComprobanteInscripcionPdf($inscripcionId, $traceId = null, $source = null, $forceRegenerate = false) {
            $inscripcionId = (int)$inscripcionId;
            if ($inscripcionId <= 0) {
                $this->logPdfFlow($traceId, 'asegurarComprobante.invalid_id', [
                    'source' => $source,
                    'inscripcion_id' => $inscripcionId,
                ]);
                return null;
            }

            try {
                $this->logPdfFlow($traceId, 'asegurarComprobante.start', [
                    'source' => $source,
                    'inscripcion_id' => $inscripcionId,
                ]);
                $documento = $this->obtenerDocumentoPdf(
                    'fighter_inscripcion_comprobante',
                    'inscripcion_evento',
                    $inscripcionId
                );
                $this->logPdfFlow($traceId, 'asegurarComprobante.documento_actual', [
                    'source' => $source,
                    'documento' => $documento,
                ]);

                // Si ya existe documento registrado y el archivo está disponible, reutilizar (salvo force).
                if (!$forceRegenerate && $documento && !empty($documento['file_path'])) {
                    $absolutePath = __DIR__ . '/../' . ltrim($documento['file_path'], '/');
                    if (file_exists($absolutePath)) {
                        $this->logPdfFlow($traceId, 'asegurarComprobante.reuse_existing', [
                            'source' => $source,
                            'absolute_path' => $absolutePath,
                        ]);
                        return $documento;
                    }
                    $this->logPdfFlow($traceId, 'asegurarComprobante.missing_file', [
                        'source' => $source,
                        'absolute_path' => $absolutePath,
                    ]);
                }

                require_once __DIR__ . '/../services/PdfService.php';
                $pdfService = new PdfService($this->db);
                $inscripcionPdfData = $this->obtenerDatosInscripcionParaPdf($inscripcionId);
                $this->logPdfFlow($traceId, 'asegurarComprobante.data_for_pdf', [
                    'source' => $source,
                    'inscripcion_data' => $inscripcionPdfData,
                ]);

                if (!$inscripcionPdfData) {
                    $this->logPdfFlow($traceId, 'asegurarComprobante.no_data', [
                        'source' => $source,
                        'inscripcion_id' => $inscripcionId,
                    ]);
                    return $documento;
                }

                $inscripcionPdfData['_debug_trace_id'] = $traceId;
                $inscripcionPdfData['_debug_source'] = $source;
                $resultado = $pdfService->generarComprobanteInscripcionPeleador($inscripcionPdfData);
                $this->logPdfFlow($traceId, 'asegurarComprobante.generation_result', [
                    'source' => $source,
                    'resultado' => $resultado,
                ]);
                if (!empty($resultado['success'])) {
                    $documentoNuevo = $this->obtenerDocumentoPdf(
                        'fighter_inscripcion_comprobante',
                        'inscripcion_evento',
                        $inscripcionId
                    );
                    $this->logPdfFlow($traceId, 'asegurarComprobante.documento_nuevo', [
                        'source' => $source,
                        'documento' => $documentoNuevo,
                    ]);

                    if ($documentoNuevo) {
                        return $documentoNuevo;
                    }

                    return [
                        'file_path' => $resultado['file_path'] ?? null,
                        'file_url' => $resultado['url'] ?? null,
                        'verification_token' => $resultado['token'] ?? null,
                    ];
                }

                if (!empty($resultado['error'])) {
                    error_log('asegurarComprobanteInscripcionPdf: ' . $resultado['error']);
                    $this->logPdfFlow($traceId, 'asegurarComprobante.error_result', [
                        'source' => $source,
                        'error' => $resultado['error'],
                    ]);
                }
            } catch (Exception $e) {
                error_log('Error asegurarComprobanteInscripcionPdf: ' . $e->getMessage());
                $this->logPdfFlow($traceId, 'asegurarComprobante.exception', [
                    'source' => $source,
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                ]);
            }

            return null;
        }

        private function obtenerDatosInscripcionParaPdf($inscripcionId) {
            try {
                $query = "SELECT
                            ie.id,
                            ie.peleador_id,
                            ie.evento_id,
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
                            e.nombre as evento_nombre,
                            e.fecha as evento_fecha,
                            e.hora as evento_hora,
                            e.direccion as evento_direccion,
                            e.precio_inscripcion_peleador
                          FROM inscripciones_eventos ie
                          INNER JOIN peleadores p ON ie.peleador_id = p.id
                          INNER JOIN usuarios u ON p.usuario_id = u.id
                          INNER JOIN eventos e ON ie.evento_id = e.id
                          WHERE ie.id = :id
                          LIMIT 1";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $inscripcionId, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            } catch (Exception $e) {
                error_log("Error obtenerDatosInscripcionParaPdf: " . $e->getMessage());
                return null;
            }
        }

        private function convertirTipos($data) {
            if (!is_array($data)) return $data;

            foreach ($data as $key => $value) {
                // Convertir números enteros
                if (in_array($key, ['id', 'usuario_id', 'victorias', 'derrotas', 'empates',
                    'total_promociones', 'experiencia_anos', 'peleador_1_id', 'peleador_2_id',
                    'evento_id', 'votos_peleador_1', 'votos_peleador_2', 'numero_rounds', 'orden_pelea', 'edad'])) {
                    $data[$key] = (int)$value;
                }
                // Convertir decimales
                if (in_array($key, ['peso_actual', 'altura', 'peso'])) {
                    $data[$key] = (float)$value;
                }
                // Convertir booleanos
                if (in_array($key, ['es_pelea_estelar', 'entradas_agotadas'])) {
                    $data[$key] = (bool)$value;
                }
            }

            return $data;
        }
    }
