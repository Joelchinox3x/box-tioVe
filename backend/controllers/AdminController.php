<?php
/**
 * Controlador de Administración
 * Gestiona funcionalidades exclusivas del administrador
 */
class AdminController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Verificar si el usuario es admin
     */
    private function verificarAdmin($usuario_id) {
        $query = "SELECT tipo_id FROM usuarios WHERE id = :usuario_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        return $usuario && $usuario['tipo_id'] == 1;
    }

    /**
     * Obtener lista de peleadores pendientes de aprobación
     */
    public function getPeleadoresPendientes() {
        try {
            $query = "SELECT p.*, u.nombre, u.apellidos, u.email, u.telefono, c.nombre as club_nombre,
                        fc.baked_url as card_url, fc.composition_json
                FROM peleadores p
                JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN clubs c ON p.club_id = c.id
                LEFT JOIN fighter_cards fc ON p.id = fc.peleador_id AND fc.is_primary = 1
                WHERE p.estado_inscripcion = 'pendiente'
                ORDER BY p.fecha_inscripcion DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $peleadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                "success" => true,
                "count" => count($peleadores),
                "peleadores" => $peleadores
            ];

        } catch (PDOException $e) {
            error_log("Error obteniendo peleadores pendientes: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al obtener peleadores pendientes"
            ];
        }
    }

    /**
     * Obtener TODOS los peleadores con filtro opcional por estado
     */
    public function getPeleadores($filtro = 'todos') {
        try {
            $query = "SELECT p.*, u.nombre, u.apellidos, u.email, u.telefono, c.nombre as club_nombre,
                        COALESCE(
                            p.edad,
                            CASE
                                WHEN p.fecha_nacimiento IS NULL THEN NULL
                                ELSE YEAR(CURDATE()) - YEAR(p.fecha_nacimiento) - (DATE_FORMAT(CURDATE(), '%m%d') < DATE_FORMAT(p.fecha_nacimiento, '%m%d'))
                            END
                        ) as edad,
                        fc.baked_url as card_url,
                        fc.composition_json
                FROM peleadores p
                JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN clubs c ON p.club_id = c.id
                LEFT JOIN fighter_cards fc ON p.id = fc.peleador_id AND fc.is_primary = 1";

            // Agregar filtro por estado si no es 'todos'
            if ($filtro !== 'todos') {
                $query .= " WHERE p.estado_inscripcion = :filtro";
            }

            $query .= " ORDER BY p.fecha_inscripcion DESC";

            $stmt = $this->db->prepare($query);

            if ($filtro !== 'todos') {
                $stmt->bindParam(':filtro', $filtro);
            }

            $stmt->execute();
            $peleadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                "success" => true,
                "count" => count($peleadores),
                "filtro" => $filtro,
                "peleadores" => $peleadores
            ];

        } catch (PDOException $e) {
            error_log("Error obteniendo peleadores: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al obtener peleadores"
            ];
        }
    }

    /**
     * Aprobar o rechazar peleador
     */
    public function cambiarEstadoPeleador($peleador_id, $data) {
        // Log para debugging
        error_log("===== CAMBIAR ESTADO PELEADOR =====");
        error_log("Peleador ID: " . $peleador_id);
        error_log("Data recibida: " . print_r($data, true));

        // Validar datos
        if (!isset($data['estado']) || !in_array($data['estado'], ['aprobado', 'rechazado'])) {
            error_log("ERROR: Estado inválido - " . ($data['estado'] ?? 'NULL'));
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Estado inválido. Debe ser 'aprobado' o 'rechazado'"
            ];
        }

        try {
            $this->db->beginTransaction();

            $query = "UPDATE peleadores
                SET estado_inscripcion = :estado,
                    notas_admin = :notas
                WHERE id = :peleador_id";

            $stmt = $this->db->prepare($query);
            $notas = $data['notas'] ?? '';
            $stmt->bindParam(':estado', $data['estado']);
            $stmt->bindParam(':notas', $notas);
            $stmt->bindParam(':peleador_id', $peleador_id);
            $stmt->execute();

            $rowCount = $stmt->rowCount();
            error_log("Filas afectadas: " . $rowCount);

            if ($rowCount === 0) {
                error_log("ADVERTENCIA: No se actualizó ningún registro. Peleador ID: " . $peleador_id);
                $this->db->rollBack();
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Peleador no encontrado"
                ];
            }

            $this->db->commit();
            error_log("✅ Peleador actualizado exitosamente");

            return [
                "success" => true,
                "message" => "Peleador " . $data['estado'] . " exitosamente"
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error cambiando estado de peleador: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al procesar la solicitud"
            ];
        }
    }

    /**
     * Crear nuevo club
     */
    public function crearClub($data) {
        // Validar datos requeridos
        if (!isset($data['nombre']) || trim($data['nombre']) === '') {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "El nombre del club es requerido"
            ];
        }

        try {
            $this->db->beginTransaction();

            $query = "INSERT INTO clubs
                (nombre, direccion, telefono, email, descripcion, activo)
                VALUES
                (:nombre, :direccion, :telefono, :email, :descripcion, 1)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':direccion', $data['direccion']);
            $stmt->bindParam(':telefono', $data['telefono']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->execute();

            $club_id = $this->db->lastInsertId();

            $this->db->commit();

            return [
                "success" => true,
                "message" => "Club creado exitosamente",
                "club_id" => $club_id
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();

            if ($e->getCode() == 23000) {
                http_response_code(409);
                return [
                    "success" => false,
                    "message" => "Ya existe un club con ese nombre"
                ];
            }

            error_log("Error creando club: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al crear el club"
            ];
        }
    }

    /**
     * Buscar usuario por DNI
     */
    public function buscarUsuarioPorDNI($dni) {
        try {
            // Buscar en la tabla peleadores primero (tienen DNI)
            $query = "SELECT u.*, p.documento_identidad as dni, t.nombre as tipo_nombre, c.nombre as club_nombre
                FROM usuarios u
                LEFT JOIN peleadores p ON u.id = p.usuario_id
                JOIN tipos_usuario t ON u.tipo_id = t.id
                LEFT JOIN clubs c ON u.club_id = c.id
                WHERE p.documento_identidad = :dni";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':dni', $dni);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "No se encontró usuario con ese DNI"
                ];
            }

            // Remover password
            unset($usuario['password_hash']);

            return [
                "success" => true,
                "usuario" => $usuario
            ];

        } catch (PDOException $e) {
            error_log("Error buscando usuario: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al buscar usuario"
            ];
        }
    }

    /**
     * Asignar dueño a un club
     */
    public function asignarDuenioClub($data) {
        // Validar datos
        if (!isset($data['usuario_id']) || !isset($data['club_id'])) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "usuario_id y club_id son requeridos"
            ];
        }

        try {
            $this->db->beginTransaction();

            // Actualizar usuario: cambiar tipo_id a 4 (manager_club) y asignar club_id
            $query = "UPDATE usuarios
                SET tipo_id = 4, club_id = :club_id
                WHERE id = :usuario_id";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':club_id', $data['club_id']);
            $stmt->bindParam(':usuario_id', $data['usuario_id']);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $this->db->rollBack();
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Usuario no encontrado"
                ];
            }

            $this->db->commit();

            return [
                "success" => true,
                "message" => "Dueño asignado exitosamente al club"
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error asignando dueño: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al asignar dueño"
            ];
        }
    }

    /**
     * Obtener todos los clubs
     */
    public function getAllClubs() {
        try {
            $query = "SELECT c.*,
                COUNT(DISTINCT CASE WHEN u.tipo_id = 4 THEN u.id END) as total_managers,
                COUNT(DISTINCT CASE WHEN u.tipo_id = 2 THEN u.id END) as total_peleadores
                FROM clubs c
                LEFT JOIN usuarios u ON c.id = u.club_id
                WHERE c.activo = 1
                GROUP BY c.id
                ORDER BY c.nombre";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                "success" => true,
                "count" => count($clubs),
                "clubs" => $clubs
            ];

        } catch (PDOException $e) {
            error_log("Error obteniendo clubs: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al obtener clubs"
            ];
        }
    }

    /**
     * Obtener estadísticas del dashboard admin
     */
    public function getEstadisticas() {
        try {
            $stats = [];

            // Total de peleadores pendientes
            $query = "SELECT COUNT(*) as total FROM peleadores WHERE estado_inscripcion = 'pendiente'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['peleadores_pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total de peleadores aprobados
            $query = "SELECT COUNT(*) as total FROM peleadores WHERE estado_inscripcion = 'aprobado'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['peleadores_aprobados'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total de clubs activos
            $query = "SELECT COUNT(*) as total FROM clubs WHERE activo = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['clubs_activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total de usuarios
            $query = "SELECT COUNT(*) as total FROM usuarios WHERE estado = 'activo'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['usuarios_activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            return [
                "success" => true,
                "estadisticas" => $stats
            ];

        } catch (PDOException $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al obtener estadísticas"
            ];
        }
    }

    /**
     * ========================================
     * GESTIÓN DE INSCRIPCIONES Y PAGOS
     * ========================================
     */

    /**
     * Obtener todas las inscripciones con filtros opcionales
     */
    public function getInscripciones($filters = []) {
        try {
            $query = "SELECT
                i.id,
                i.peleador_id,
                i.evento_id,
                i.estado_pago,
                i.monto_pagado,
                i.fecha_inscripcion,
                i.fecha_pago,
                i.metodo_pago,
                i.comprobante_pago,
                i.notas_admin,
                -- Datos del peleador
                u.nombre as peleador_nombre,
                u.email as peleador_email,
                u.telefono as peleador_telefono,
                p.apodo as peleador_apodo,
                p.documento_identidad as peleador_dni,
                -- Datos del evento
                e.nombre as evento_nombre,
                e.fecha as evento_fecha,
                e.hora as evento_hora,
                e.precio_inscripcion_peleador as precio_evento,
                -- Datos del club
                c.nombre as club_nombre
            FROM inscripciones_eventos i
            INNER JOIN peleadores p ON i.peleador_id = p.id
            INNER JOIN usuarios u ON p.usuario_id = u.id
            INNER JOIN eventos e ON i.evento_id = e.id
            LEFT JOIN clubs c ON p.club_id = c.id
            WHERE 1=1";

            // Aplicar filtros
            if (isset($filters['estado_pago'])) {
                $query .= " AND i.estado_pago = :estado_pago";
            }
            if (isset($filters['evento_id'])) {
                $query .= " AND i.evento_id = :evento_id";
            }

            $query .= " ORDER BY i.fecha_inscripcion DESC";

            $stmt = $this->db->prepare($query);

            // Bind de parámetros
            if (isset($filters['estado_pago'])) {
                $stmt->bindParam(':estado_pago', $filters['estado_pago']);
            }
            if (isset($filters['evento_id'])) {
                $stmt->bindParam(':evento_id', $filters['evento_id']);
            }

            $stmt->execute();
            $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                "success" => true,
                "count" => count($inscripciones),
                "inscripciones" => $inscripciones
            ];

        } catch (PDOException $e) {
            error_log("Error obteniendo inscripciones: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al obtener inscripciones"
            ];
        }
    }

    /**
     * Confirmar pago de una inscripción
     */
    public function confirmarPago($inscripcion_id, $data) {
        try {
            $this->db->beginTransaction();

            // Validar datos
            if (!isset($data['monto_pagado']) || $data['monto_pagado'] <= 0) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "El monto pagado es requerido y debe ser mayor a 0"
                ];
            }

            // Actualizar inscripción
            $query = "UPDATE inscripciones_eventos
                SET estado_pago = 'pagado',
                    monto_pagado = :monto_pagado,
                    fecha_pago = NOW(),
                    metodo_pago = :metodo_pago,
                    comprobante_pago = :comprobante_pago,
                    notas_admin = :notas_admin
                WHERE id = :inscripcion_id";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':inscripcion_id', $inscripcion_id);
            $stmt->bindParam(':monto_pagado', $data['monto_pagado']);
            $stmt->bindParam(':metodo_pago', $data['metodo_pago']);
            $stmt->bindParam(':comprobante_pago', $data['comprobante_pago']);
            $stmt->bindParam(':notas_admin', $data['notas_admin']);
            $stmt->execute();

            $this->db->commit();

            return [
                "success" => true,
                "message" => "Pago confirmado exitosamente"
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error confirmando pago: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al confirmar pago"
            ];
        }
    }

    /**
     * Obtener inscripciones pendientes de pago
     */
    public function getInscripcionesPendientes() {
        return $this->getInscripciones(['estado_pago' => 'pendiente']);
    }

    /**
     * Crear nueva inscripción (cuando un peleador se inscribe a un evento)
     */
    public function crearInscripcion($data) {
        try {
            // Validar datos requeridos
            if (!isset($data['peleador_id']) || !isset($data['evento_id'])) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "Peleador y evento son requeridos"
                ];
            }

            // Verificar que el peleador esté aprobado
            $query = "SELECT estado_inscripcion FROM peleadores WHERE id = :peleador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':peleador_id', $data['peleador_id']);
            $stmt->execute();
            $peleador = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$peleador || $peleador['estado_inscripcion'] !== 'aprobado') {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "El peleador debe estar aprobado para inscribirse"
                ];
            }

            // Obtener precio del evento
            $query = "SELECT precio_inscripcion_peleador FROM eventos WHERE id = :evento_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':evento_id', $data['evento_id']);
            $stmt->execute();
            $evento = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$evento) {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Evento no encontrado"
                ];
            }

            $this->db->beginTransaction();

            // Crear inscripción
            $query = "INSERT INTO inscripciones_eventos
                (peleador_id, evento_id, monto_pagado, estado_pago)
                VALUES (:peleador_id, :evento_id, :monto_pagado, 'pendiente')";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':peleador_id', $data['peleador_id']);
            $stmt->bindParam(':evento_id', $data['evento_id']);
            $stmt->bindParam(':monto_pagado', $evento['precio_inscripcion_peleador']);
            $stmt->execute();

            $inscripcion_id = $this->db->lastInsertId();

            $this->db->commit();

            return [
                "success" => true,
                "message" => "Inscripción creada exitosamente",
                "inscripcion_id" => $inscripcion_id,
                "monto_a_pagar" => $evento['precio_inscripcion_peleador']
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();

            // Error de duplicado (ya inscrito)
            if ($e->getCode() == 23000) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "El peleador ya está inscrito en este evento"
                ];
            }

            error_log("Error creando inscripción: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al crear inscripción"
            ];
        }
    }

    /**
     * Actualizar precio de inscripción de un evento
     */
    public function actualizarPrecioEvento($evento_id, $data) {
        try {
            if (!isset($data['precio_inscripcion_peleador'])) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "El precio de inscripción es requerido"
                ];
            }

            $query = "UPDATE eventos
                SET precio_inscripcion_peleador = :precio
                WHERE id = :evento_id";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':precio', $data['precio_inscripcion_peleador']);
            $stmt->bindParam(':evento_id', $evento_id);
            $stmt->execute();

            return [
                "success" => true,
                "message" => "Precio actualizado exitosamente"
            ];

        } catch (PDOException $e) {
            error_log("Error actualizando precio: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al actualizar precio"
            ];
        }
    }

    /**
     * ========================================
     * MÉTODOS DE PAGO
     * ========================================
     */

    public function getMetodosPago($filters = []) {
        try {
            $query = "SELECT
                id,
                codigo,
                nombre,
                requiere_comprobante,
                activo,
                orden,
                qr_imagen_url,
                telefono_receptor,
                nombre_receptor,
                fecha_creacion,
                fecha_actualizacion
            FROM metodos_pago
            WHERE 1=1";

            if (isset($filters['activo'])) {
                $query .= " AND activo = :activo";
            }

            $query .= " ORDER BY orden ASC, nombre ASC";

            $stmt = $this->db->prepare($query);
            if (isset($filters['activo'])) {
                $stmt->bindParam(':activo', $filters['activo']);
            }
            $stmt->execute();

            $metodos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                "success" => true,
                "count" => count($metodos),
                "metodos" => $metodos
            ];
        } catch (PDOException $e) {
            error_log("Error obteniendo métodos de pago: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al obtener métodos de pago"
            ];
        }
    }

    public function crearMetodoPago($data) {
        try {
            if (empty($data['codigo']) || empty($data['nombre'])) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "Código y nombre son requeridos"
                ];
            }

            $codigo = strtolower(trim($data['codigo']));
            $nombre = trim($data['nombre']);

            $query = "INSERT INTO metodos_pago
                (codigo, nombre, requiere_comprobante, activo, orden, qr_imagen_url, telefono_receptor, nombre_receptor)
                VALUES (:codigo, :nombre, :requiere_comprobante, :activo, :orden, :qr_imagen_url, :telefono_receptor, :nombre_receptor)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':requiere_comprobante', $data['requiere_comprobante']);
            $stmt->bindParam(':activo', $data['activo']);
            $stmt->bindParam(':orden', $data['orden']);
            $stmt->bindParam(':qr_imagen_url', $data['qr_imagen_url']);
            $stmt->bindParam(':telefono_receptor', $data['telefono_receptor']);
            $stmt->bindParam(':nombre_receptor', $data['nombre_receptor']);
            $stmt->execute();

            return [
                "success" => true,
                "message" => "Método de pago creado",
                "metodo_id" => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            error_log("Error creando método de pago: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al crear método de pago"
            ];
        }
    }

    public function actualizarMetodoPago($metodo_id, $data) {
        try {
            $fields = [];
            $params = [':id' => $metodo_id];

            $allowed = [
                'codigo',
                'nombre',
                'requiere_comprobante',
                'activo',
                'orden',
                'qr_imagen_url',
                'telefono_receptor',
                'nombre_receptor'
            ];

            foreach ($allowed as $key) {
                if (array_key_exists($key, $data)) {
                    $fields[] = "$key = :$key";
                    $params[":$key"] = $key === 'codigo'
                        ? strtolower(trim($data[$key]))
                        : $data[$key];
                }
            }

            if (count($fields) === 0) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "No hay campos para actualizar"
                ];
            }

            $query = "UPDATE metodos_pago SET " . implode(", ", $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            return [
                "success" => true,
                "message" => "Método de pago actualizado"
            ];
        } catch (PDOException $e) {
            error_log("Error actualizando método de pago: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al actualizar método de pago"
            ];
        }
    }

    /**
     * Subir imagen QR para método de pago
     */
    public function uploadQRImage($files) {
        // Log para debug
        error_log("uploadQRImage - FILES recibidos: " . print_r($files, true));
        error_log("uploadQRImage - POST recibidos: " . print_r($_POST, true));

        if (!isset($files['qr_image'])) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "No se recibió el archivo QR",
                "debug" => [
                    "files_keys" => array_keys($files),
                    "post_keys" => array_keys($_POST)
                ]
            ];
        }

        $file = $files['qr_image'];

        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Tipo de archivo no permitido. Solo JPG, PNG o WEBP"
            ];
        }

        // Validar tamaño (máx 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "El archivo es demasiado grande. Máximo 5MB"
            ];
        }

        // Crear directorio si no existe
        $uploadDir = __DIR__ . '/../files/qr_codes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($extension)) {
            $mimes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];
            $extension = $mimes[$file['type']] ?? 'jpg';
        }

        $nonce = substr(md5(time() . $file['name']), 0, 12);
        $newFilename = "qr_" . $nonce . "." . $extension;
        $targetPath = $uploadDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Guardar path relativo (consistente con foto_perfil, baked_url, etc.)
            $relativePath = "files/qr_codes/" . $newFilename;

            return [
                "success" => true,
                "message" => "Imagen QR subida correctamente",
                "url" => $relativePath,
                "filename" => $newFilename
            ];
        } else {
            error_log("Fallo move_uploaded_file para QR. Tmp: " . $file['tmp_name'] . " Dest: " . $targetPath);
            http_response_code(500);
            return [
                "success" => false,
                "message" => "No se pudo guardar el archivo. Verifica permisos"
            ];
        }
    }

    /**
     * Editar datos de un peleador (admin)
     */
    public function editPeleador($peleador_id, $data) {
        error_log("===== EDITAR PELEADOR ID: $peleador_id =====");
        error_log("Data recibida: " . print_r($data, true));

        try {
            $this->db->beginTransaction();

            // Verificar que el peleador existe y obtener usuario_id
            $query = "SELECT p.usuario_id FROM peleadores p WHERE p.id = :peleador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':peleador_id', $peleador_id);
            $stmt->execute();
            $peleador = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$peleador) {
                $this->db->rollBack();
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Peleador no encontrado"
                ];
            }

            $usuario_id = $peleador['usuario_id'];

            // Actualizar tabla usuarios (nombre, email, telefono)
            $userFields = [];
            $userParams = [];
            if (isset($data['nombre'])) {
                $userFields[] = "nombre = :nombre";
                $userParams[':nombre'] = $data['nombre'];
            }
            if (isset($data['email'])) {
                $userFields[] = "email = :email";
                $userParams[':email'] = $data['email'];
            }
            if (isset($data['telefono'])) {
                $userFields[] = "telefono = :telefono";
                $userParams[':telefono'] = $data['telefono'];
            }

            if (!empty($userFields)) {
                $query = "UPDATE usuarios SET " . implode(', ', $userFields) . " WHERE id = :usuario_id";
                $stmt = $this->db->prepare($query);
                foreach ($userParams as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                $stmt->bindValue(':usuario_id', $usuario_id);
                $stmt->execute();
                error_log("✅ Usuario actualizado: " . implode(', ', $userFields));
            }

            // Actualizar tabla peleadores
            $pelFields = [];
            $pelParams = [];

            $allowedFields = [
                'apodo', 'peso_actual', 'altura', 'genero', 'estilo',
                'categoria', 'experiencia_anos', 'victorias', 'derrotas',
                'empates', 'club_id', 'documento_identidad'
            ];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $pelFields[] = "$field = :$field";
                    $pelParams[":$field"] = $data[$field] === '' ? null : $data[$field];
                }
            }

            if (!empty($pelFields)) {
                $query = "UPDATE peleadores SET " . implode(', ', $pelFields) . " WHERE id = :peleador_id";
                $stmt = $this->db->prepare($query);
                foreach ($pelParams as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                $stmt->bindValue(':peleador_id', $peleador_id);
                $stmt->execute();
                error_log("✅ Peleador actualizado: " . implode(', ', $pelFields));
            }

            $this->db->commit();
            error_log("✅ COMMIT - Edición completada");

            return [
                "success" => true,
                "message" => "Peleador actualizado exitosamente"
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("❌ Error editando peleador: " . $e->getMessage());

            if ($e->getCode() == 23000) {
                http_response_code(409);
                return [
                    "success" => false,
                    "message" => "Ya existe un registro con ese email o DNI"
                ];
            }

            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al editar peleador"
            ];
        }
    }

    /**
     * Eliminar peleador y todos sus datos relacionados
     */
    public function deletePeleador($peleador_id) {
        error_log("===== INICIO ELIMINACIÓN PELEADOR ID: $peleador_id =====");

        try {
            $this->db->beginTransaction();
            error_log("✅ Transacción iniciada");

            // Obtener información del peleador antes de eliminar
            $query = "SELECT p.*, p.usuario_id
                      FROM peleadores p
                      WHERE p.id = :peleador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':peleador_id', $peleador_id);
            $stmt->execute();
            $peleador = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("✅ Peleador encontrado: " . ($peleador ? "SÍ (ID: {$peleador['id']}, Usuario: {$peleador['usuario_id']})" : "NO"));

            if (!$peleador) {
                $this->db->rollBack();
                error_log("❌ Peleador no encontrado - Rollback");
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Peleador no encontrado"
                ];
            }

            // Eliminar archivos físicos del peleador (fotos, tarjetas, etc.)
            $peleadorDir = __DIR__ . "/../files/peleadores/$peleador_id";
            error_log("📁 Directorio a eliminar: $peleadorDir");
            if (is_dir($peleadorDir)) {
                error_log("✅ Directorio existe, eliminando...");
                $this->deleteDirectory($peleadorDir);
                error_log("✅ Directorio eliminado");
            } else {
                error_log("ℹ️ Directorio no existe, continuando...");
            }

            // Eliminar registros relacionados en fighter_cards
            error_log("🗑️ Eliminando registros de fighter_cards...");
            $query = "DELETE FROM fighter_cards WHERE peleador_id = :peleador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':peleador_id', $peleador_id);
            $stmt->execute();
            $deletedCards = $stmt->rowCount();
            error_log("✅ Eliminados $deletedCards registros de fighter_cards");

            // Eliminar inscripciones a eventos
            error_log("🗑️ Eliminando inscripciones a eventos...");
            $query = "DELETE FROM inscripciones_eventos WHERE peleador_id = :peleador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':peleador_id', $peleador_id);
            $stmt->execute();
            $deletedInscripciones = $stmt->rowCount();
            error_log("✅ Eliminadas $deletedInscripciones inscripciones");

            // Eliminar el peleador
            error_log("🗑️ Eliminando registro del peleador...");
            $query = "DELETE FROM peleadores WHERE id = :peleador_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':peleador_id', $peleador_id);
            $stmt->execute();
            error_log("✅ Peleador eliminado de la BD");

            // NO ELIMINAR EL USUARIO - solo eliminar el peleador
            error_log("ℹ️ Usuario conservado (ID: {$peleador['usuario_id']})");

            $this->db->commit();
            error_log("✅ COMMIT - Eliminación completada exitosamente");
            error_log("===== FIN ELIMINACIÓN PELEADOR =====");

            return [
                "success" => true,
                "message" => "Peleador eliminado exitosamente"
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("❌ ERROR PDO: " . $e->getMessage());
            error_log("❌ Código de error: " . $e->getCode());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            error_log("===== FIN ELIMINACIÓN PELEADOR (CON ERROR) =====");
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al eliminar peleador: " . $e->getMessage()
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("❌ ERROR GENERAL: " . $e->getMessage());
            error_log("❌ Stack trace: " . $e->getTraceAsString());
            error_log("===== FIN ELIMINACIÓN PELEADOR (CON ERROR) =====");
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al eliminar peleador: " . $e->getMessage()
            ];
        }
    }

    /**
     * Función auxiliar para eliminar directorio recursivamente
     */
    private function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "$dir/$file";
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
