<?php

class ClubsController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Obtener todos los clubs activos
     * GET /api/clubs
     */
    public function listar() {
        try {
            $query = "SELECT id, nombre, direccion, telefono, email, logo, descripcion
                      FROM clubs
                      WHERE activo = true
                      ORDER BY nombre ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute();

            $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                "success" => true,
                "total" => count($clubs),
                "clubs" => $clubs
            ];

        } catch (PDOException $e) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al obtener clubs"
            ];
        }
    }

    /**
     * Obtener un club por ID
     * GET /api/clubs/:id
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM clubs WHERE id = :id AND activo = true";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $club = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$club) {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Club no encontrado"
                ];
            }

            return [
                "success" => true,
                "club" => $club
            ];

        } catch (PDOException $e) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al obtener club"
            ];
        }
    }

    /**
     * Obtener peleadores de un club
     * GET /api/clubs/:id/peleadores
     */
    public function obtenerPeleadores($id) {
        try {
            $query = "SELECT p.*, u.nombre, u.email
                      FROM peleadores p
                      JOIN usuarios u ON p.usuario_id = u.id
                      WHERE p.club_id = :club_id
                      AND p.estado_inscripcion = 'aprobado'
                      ORDER BY p.total_promociones DESC";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':club_id', $id);
            $stmt->execute();

            $peleadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                "success" => true,
                "total" => count($peleadores),
                "peleadores" => $peleadores
            ];

        } catch (PDOException $e) {
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al obtener peleadores del club"
            ];
        }
    }
}
