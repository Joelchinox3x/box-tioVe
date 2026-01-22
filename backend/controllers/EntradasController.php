<?php
/**
 * Controlador de Entradas
 * Sistema de compra de tickets
 */
class EntradasController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Comprar entrada
     */
    public function comprar($data) {
        if (empty($data['evento_id']) || empty($data['usuario_id']) ||
            empty($data['tipo_entrada']) || empty($data['cantidad'])) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Faltan datos requeridos"
            ];
        }

        try {
            // Verificar disponibilidad del evento
            $queryEvento = "SELECT * FROM eventos WHERE id = :id AND estado = 'proximamente'";
            $stmtEvento = $this->db->prepare($queryEvento);
            $stmtEvento->bindParam(':id', $data['evento_id']);
            $stmtEvento->execute();
            $evento = $stmtEvento->fetch(PDO::FETCH_ASSOC);

            if (!$evento) {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Evento no disponible"
                ];
            }

            // Verificar capacidad
            $disponibles = $evento['capacidad_total'] - $evento['entradas_vendidas'];
            if ($disponibles < $data['cantidad']) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "No hay suficientes entradas disponibles. Quedan: $disponibles"
                ];
            }

            // Calcular precio
            $precio_unitario = $data['tipo_entrada'] === 'vip'
                ? $evento['precio_entrada_vip']
                : $evento['precio_entrada_general'];

            $precio_total = $precio_unitario * $data['cantidad'];

            $this->db->beginTransaction();

            // Crear entrada
            $queryEntrada = "INSERT INTO entradas
                (evento_id, usuario_id, tipo_entrada, precio_pagado, cantidad, estado, codigo_qr, metodo_pago)
                VALUES
                (:evento_id, :usuario_id, :tipo, :precio, :cantidad, 'pagada', :codigo_qr, :metodo_pago)";

            $codigo_qr = $this->generarCodigoQR();

            $stmtEntrada = $this->db->prepare($queryEntrada);
            $stmtEntrada->bindParam(':evento_id', $data['evento_id']);
            $stmtEntrada->bindParam(':usuario_id', $data['usuario_id']);
            $stmtEntrada->bindParam(':tipo', $data['tipo_entrada']);
            $stmtEntrada->bindParam(':precio', $precio_total);
            $stmtEntrada->bindParam(':cantidad', $data['cantidad']);
            $stmtEntrada->bindParam(':codigo_qr', $codigo_qr);
            $stmtEntrada->bindParam(':metodo_pago', $data['metodo_pago'] ?? 'efectivo');
            $stmtEntrada->execute();

            $entrada_id = $this->db->lastInsertId();

            // Actualizar contador del evento
            $queryUpdate = "UPDATE eventos
                SET entradas_vendidas = entradas_vendidas + :cantidad
                WHERE id = :evento_id";

            $stmtUpdate = $this->db->prepare($queryUpdate);
            $stmtUpdate->bindParam(':cantidad', $data['cantidad']);
            $stmtUpdate->bindParam(':evento_id', $data['evento_id']);
            $stmtUpdate->execute();

            $this->db->commit();

            return [
                "success" => true,
                "message" => "¡Compra exitosa!",
                "entrada_id" => $entrada_id,
                "codigo_qr" => $codigo_qr,
                "precio_total" => $precio_total,
                "cantidad" => $data['cantidad']
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al procesar la compra"
            ];
        }
    }

    /**
     * Generar código QR único
     */
    private function generarCodigoQR() {
        return 'EVT-' . strtoupper(bin2hex(random_bytes(8)));
    }
}
