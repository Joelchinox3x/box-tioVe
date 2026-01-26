<?php
/**
 * Controlador de Tipos de Boletos
 * Gestión de tipos de boletos para eventos (ADMIN)
 */
class TiposBoletosController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Crear nuevo tipo de boleto
     * POST /tipos-boleto/crear
     */
    public function crear($data) {
        // Validar datos requeridos
        $required = ['evento_id', 'nombre', 'precio', 'cantidad_total'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "El campo $field es requerido"
                ];
            }
        }

        try {
            $query = "INSERT INTO tipos_boleto (
                evento_id,
                nombre,
                precio,
                cantidad_total,
                cantidad_vendida,
                color_hex,
                descripcion,
                orden,
                activo
            ) VALUES (
                :evento_id,
                :nombre,
                :precio,
                :cantidad_total,
                0,
                :color_hex,
                :descripcion,
                :orden,
                1
            )";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':evento_id', $data['evento_id']);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':precio', $data['precio']);
            $stmt->bindParam(':cantidad_total', $data['cantidad_total']);
            $stmt->bindParam(':color_hex', $data['color_hex'] ?? '#FFD700');
            $stmt->bindParam(':descripcion', $data['descripcion'] ?? null);
            $stmt->bindParam(':orden', $data['orden'] ?? 0);
            $stmt->execute();

            $tipo_id = $this->db->lastInsertId();

            return [
                "success" => true,
                "message" => "Tipo de boleto creado exitosamente",
                "tipo_boleto_id" => $tipo_id
            ];

        } catch (PDOException $e) {
            error_log("Error en crear tipo boleto: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al crear tipo de boleto"
            ];
        }
    }

    /**
     * Actualizar tipo de boleto
     * PUT /tipos-boleto/editar/{id}
     */
    public function editar($id, $data) {
        try {
            $fields = [];
            $params = ['id' => $id];

            if (isset($data['nombre'])) {
                $fields[] = "nombre = :nombre";
                $params['nombre'] = $data['nombre'];
            }
            if (isset($data['precio'])) {
                $fields[] = "precio = :precio";
                $params['precio'] = $data['precio'];
            }
            if (isset($data['cantidad_total'])) {
                $fields[] = "cantidad_total = :cantidad_total";
                $params['cantidad_total'] = $data['cantidad_total'];
            }
            if (isset($data['color_hex'])) {
                $fields[] = "color_hex = :color_hex";
                $params['color_hex'] = $data['color_hex'];
            }
            if (isset($data['descripcion'])) {
                $fields[] = "descripcion = :descripcion";
                $params['descripcion'] = $data['descripcion'];
            }
            if (isset($data['orden'])) {
                $fields[] = "orden = :orden";
                $params['orden'] = $data['orden'];
            }
            if (isset($data['activo'])) {
                $fields[] = "activo = :activo";
                $params['activo'] = $data['activo'] ? 1 : 0;
            }

            if (empty($fields)) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "No hay campos para actualizar"
                ];
            }

            $query = "UPDATE tipos_boleto SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Tipo de boleto no encontrado"
                ];
            }

            return [
                "success" => true,
                "message" => "Tipo de boleto actualizado exitosamente"
            ];

        } catch (PDOException $e) {
            error_log("Error en editar tipo boleto: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al actualizar tipo de boleto"
            ];
        }
    }

    /**
     * Desactivar tipo de boleto
     * DELETE /tipos-boleto/{id}
     */
    public function desactivar($id) {
        try {
            $query = "UPDATE tipos_boleto SET activo = 0 WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Tipo de boleto no encontrado"
                ];
            }

            return [
                "success" => true,
                "message" => "Tipo de boleto desactivado exitosamente"
            ];

        } catch (PDOException $e) {
            error_log("Error en desactivar tipo boleto: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al desactivar tipo de boleto"
            ];
        }
    }

    /**
     * Obtener todos los tipos de boleto de un evento
     * GET /tipos-boleto/evento/{evento_id}
     */
    public function getTiposPorEvento($evento_id) {
        try {
            $query = "SELECT * FROM tipos_boleto
                WHERE evento_id = :evento_id
                ORDER BY orden ASC, id ASC";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':evento_id', $evento_id);
            $stmt->execute();
            $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Convertir tipos
            foreach ($tipos as &$tipo) {
                $tipo['id'] = (int)$tipo['id'];
                $tipo['evento_id'] = (int)$tipo['evento_id'];
                $tipo['precio'] = (float)$tipo['precio'];
                $tipo['cantidad_total'] = (int)$tipo['cantidad_total'];
                $tipo['cantidad_vendida'] = (int)$tipo['cantidad_vendida'];
                $tipo['orden'] = (int)$tipo['orden'];
                $tipo['activo'] = (bool)$tipo['activo'];
            }

            return [
                "success" => true,
                "tipos_boleto" => $tipos
            ];

        } catch (PDOException $e) {
            error_log("Error en getTiposPorEvento: " . $e->getMessage());
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al obtener tipos de boleto"
            ];
        }
    }
}
