<?php
/**
 * Controlador de Promociones
 * Sistema para compartir/promocionar peleadores
 */
class PromocionesController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Registrar una promoción (share)
     */
    public function registrar($data) {
        if (empty($data['peleador_id']) || empty($data['usuario_id']) || empty($data['plataforma'])) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Faltan datos requeridos"
            ];
        }

        try {
            $this->db->beginTransaction();

            // Registrar promoción
            $query = "INSERT INTO promociones (peleador_id, usuario_promotor_id, plataforma, link_compartido)
                VALUES (:peleador_id, :usuario_id, :plataforma, :link)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':peleador_id', $data['peleador_id']);
            $stmt->bindParam(':usuario_id', $data['usuario_id']);
            $stmt->bindParam(':plataforma', $data['plataforma']);
            $stmt->bindParam(':link', $data['link'] ?? null);
            $stmt->execute();

            // Actualizar contador del peleador
            $queryUpdate = "UPDATE peleadores
                SET total_promociones = total_promociones + 1
                WHERE id = :peleador_id";

            $stmtUpdate = $this->db->prepare($queryUpdate);
            $stmtUpdate->bindParam(':peleador_id', $data['peleador_id']);
            $stmtUpdate->execute();

            // Dar puntos al usuario promotor
            $queryPuntos = "UPDATE usuarios
                SET puntos_promocion = puntos_promocion + 10
                WHERE id = :usuario_id";

            $stmtPuntos = $this->db->prepare($queryPuntos);
            $stmtPuntos->bindParam(':usuario_id', $data['usuario_id']);
            $stmtPuntos->execute();

            $this->db->commit();

            return [
                "success" => true,
                "message" => "¡Promoción registrada! +10 puntos",
                "link_compartir" => $this->generarLinkCompartir($data['peleador_id'], $data['plataforma'])
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al registrar promoción"
            ];
        }
    }

    /**
     * Generar link para compartir
     */
    private function generarLinkCompartir($peleador_id, $plataforma) {
        $base_url = "http://34.44.67.166"; // Cambiar en producción
        $url_peleador = "$base_url/peleador/$peleador_id";

        $links = [
            'whatsapp' => "https://wa.me/?text=" . urlencode("¡Apoya a este guerrero! $url_peleador"),
            'facebook' => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url_peleador),
            'twitter' => "https://twitter.com/intent/tweet?url=" . urlencode($url_peleador),
            'instagram' => $url_peleador
        ];

        return $links[$plataforma] ?? $url_peleador;
    }
}