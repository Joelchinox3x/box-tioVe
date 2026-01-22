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
                YEAR(CURDATE()) - YEAR(p.fecha_nacimiento) - (DATE_FORMAT(CURDATE(), '%m%d') < DATE_FORMAT(p.fecha_nacimiento, '%m%d')) as edad,
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
     * Inscribir nuevo peleador
     */
    public function inscribir($data) {
        
        // 🛠️ DEBUGGING: Ver qué llega realmente
        error_log("--- INTENTO DE INSCRIPCIÓN ---");
        // Si data es muy grande, no lo imprimas todo, pero por ahora sirve
        error_log("DATOS RECIBIDOS (POST/JSON): " . print_r($data, true));
        error_log("ARCHIVOS RECIBIDOS (_FILES): " . print_r($_FILES, true));
        
        // Validar datos requeridos
        $required = ['nombre', 'email', 'password', 'apodo', 'fecha_nacimiento',
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
                (usuario_id, apodo, fecha_nacimiento, peso_actual, altura, genero, club_id,
                 estilo, documento_identidad, experiencia_anos, foto_perfil)
                VALUES
                (:usuario_id, :apodo, :fecha_nacimiento, :peso_actual, :altura, :genero,
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
            $stmtPeleador->bindParam(':fecha_nacimiento', $data['fecha_nacimiento']);
            $stmtPeleador->bindParam(':peso_actual', $data['peso_actual']);
            $stmtPeleador->bindParam(':altura', $altura);
            $stmtPeleador->bindParam(':genero', $data['genero']);
            $stmtPeleador->bindParam(':club_id', $data['club_id']);
            $stmtPeleador->bindParam(':estilo', $estilo);
            $stmtPeleador->bindParam(':documento_identidad', $data['documento_identidad']);
            $stmtPeleador->bindParam(':experiencia_anos', $experiencia_anos);
            $stmtPeleador->bindParam(':foto_perfil', $foto_perfil);
            $stmtPeleador->execute();

            $this->db->commit();

            return [
                "success" => true,
                "message" => "Inscripción exitosa.",
                "peleador_id" => $this->db->lastInsertId(),
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