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

            return [
                "success" => true,
                "message" => "Inscripción exitosa.",
                "peleador_id" => $peleador_id,
                "baked_url" => $baked_url,
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
            try {
                // Estado del peleador
                $queryPeleador = "SELECT id, estado_inscripcion FROM peleadores WHERE id = :peleador_id LIMIT 1";
                $stmtPeleador = $this->db->prepare($queryPeleador);
                $stmtPeleador->bindParam(':peleador_id', $peleador_id);
                $stmtPeleador->execute();
                $peleador = $stmtPeleador->fetch(PDO::FETCH_ASSOC);

                if (!$peleador) {
                    http_response_code(404);
                    return [
                        "success" => false,
                        "message" => "Peleador no encontrado"
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
                    return [
                        "success" => true,
                        "estado_peleador" => $peleador['estado_inscripcion'],
                        "evento" => null,
                        "inscripcion" => null
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

                return [
                    "success" => true,
                    "estado_peleador" => $peleador['estado_inscripcion'],
                    "evento" => $evento ? $this->convertirTipos($evento) : null,
                    "inscripcion" => $inscripcion ? $this->convertirTipos($inscripcion) : null
                ];
            } catch (Exception $e) {
                error_log("Error getInscripcionEvento: " . $e->getMessage());
                http_response_code(500);
                return [
                    "success" => false,
                    "message" => "Error al obtener estado de inscripción"
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
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                $evento_id = $data['evento_id'] ?? null;

                if (!$evento_id) {
                    http_response_code(400);
                    return ["success" => false, "message" => "evento_id es requerido"];
                }

                // Verificar que el peleador existe y no está rechazado
                $stmt = $this->db->prepare("SELECT id, estado_inscripcion FROM peleadores WHERE id = :pid LIMIT 1");
                $stmt->execute([':pid' => $peleador_id]);
                $peleador = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$peleador) {
                    http_response_code(404);
                    return ["success" => false, "message" => "Peleador no encontrado"];
                }
                if ($peleador['estado_inscripcion'] === 'rechazado') {
                    http_response_code(400);
                    return ["success" => false, "message" => "Tu perfil ha sido rechazado. Contacta al administrador."];
                }

                // Verificar que el evento existe y está activo
                $stmt = $this->db->prepare("SELECT id, precio_inscripcion_peleador FROM eventos WHERE id = :eid AND estado IN ('proximamente', 'activo') LIMIT 1");
                $stmt->execute([':eid' => $evento_id]);
                $evento = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$evento) {
                    http_response_code(400);
                    return ["success" => false, "message" => "Evento no disponible para inscripción"];
                }

                // Verificar si ya existe inscripción
                $stmt = $this->db->prepare("SELECT id FROM inscripciones_eventos WHERE peleador_id = :pid AND evento_id = :eid LIMIT 1");
                $stmt->execute([':pid' => $peleador_id, ':eid' => $evento_id]);
                if ($stmt->fetch()) {
                    return ["success" => true, "message" => "Ya estás inscrito en este evento"];
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

                return [
                    "success" => true,
                    "message" => "Inscripción creada. Ahora selecciona tu método de pago.",
                    "inscripcion_id" => (int)$inscripcion_id
                ];
            } catch (PDOException $e) {
                error_log("Error crearInscripcion: " . $e->getMessage());
                http_response_code(500);
                return ["success" => false, "message" => "Error al crear inscripción"];
            }
        }

        /**
         * Inscribir peleador al evento activo con método de pago y comprobante opcional
         */
        public function inscribirEvento($peleador_id, $data) {
            try {
                if (!isset($data['evento_id']) || !isset($data['metodo_pago']) || $data['metodo_pago'] === '') {
                    http_response_code(400);
                    return [
                        "success" => false,
                        "message" => "evento_id y metodo_pago son requeridos"
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
                    http_response_code(404);
                    return [
                        "success" => false,
                        "message" => "Peleador no encontrado"
                    ];
                }

                // ✅ MODIFICADO: Permitir inscripción incluso en estado pendiente
                // Ya no se requiere aprobación previa para pagar
                // El pago es requisito para la aprobación, no al revés
                if ($peleador['estado_inscripcion'] === 'rechazado') {
                    http_response_code(400);
                    return [
                        "success" => false,
                        "message" => "Tu perfil ha sido rechazado. Contacta al administrador."
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
                    http_response_code(400);
                    return [
                        "success" => false,
                        "message" => "Método de pago inválido o inactivo"
                    ];
                }

                $tieneComprobanteArchivo = isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK;
                $tieneComprobanteTexto = isset($data['comprobante_pago']) && trim((string)$data['comprobante_pago']) !== '';
                if ((int)$metodo['requiere_comprobante'] === 1 && !$tieneComprobanteArchivo && !$tieneComprobanteTexto) {
                    http_response_code(400);
                    return [
                        "success" => false,
                        "message" => "Este método de pago requiere comprobante"
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
                    http_response_code(404);
                    return [
                        "success" => false,
                        "message" => "Evento no encontrado o no disponible"
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
                }

                $this->db->commit();

                return [
                    "success" => true,
                    "message" => "Inscripción registrada correctamente",
                    "inscripcion_id" => (int)$inscripcionId
                ];
            } catch (PDOException $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }

                // Duplicado por unique(peleador_id, evento_id)
                if ($e->getCode() == 23000) {
                    http_response_code(400);
                    return [
                        "success" => false,
                        "message" => "Ya estás inscrito en este evento"
                    ];
                }

                error_log("Error inscribirEvento: " . $e->getMessage());
                http_response_code(500);
                return [
                    "success" => false,
                    "message" => "Error al registrar inscripción"
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
