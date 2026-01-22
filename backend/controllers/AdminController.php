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
            $query = "SELECT p.*, u.nombre, u.email, u.telefono, c.nombre as club_nombre
                FROM peleadores p
                JOIN usuarios u ON p.usuario_id = u.id
                LEFT JOIN clubs c ON p.club_id = c.id
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
                e.titulo as evento_titulo,
                e.fecha_evento,
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
}