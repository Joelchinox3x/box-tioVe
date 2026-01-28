<?php
/**
 * Controlador de Peleas
 * Gestiona la cartelera y sistema de votación
 */
class PeleasController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Obtener cartelera completa
     */
    public function obtenerCartelera() {
        $query = "SELECT
            pel.id, pel.categoria_peso, pel.numero_rounds, pel.es_pelea_estelar,
            pel.entradas_agotadas, pel.votos_peleador_1, pel.votos_peleador_2,
            pel.orden_pelea,
            p1.id as p1_id, p1.apodo as p1_apodo, p1.foto_perfil as p1_foto,
            p1.club as p1_club, p1.victorias as p1_victorias, p1.derrotas as p1_derrotas,
            u1.nombre as p1_nombre,
            p2.id as p2_id, p2.apodo as p2_apodo, p2.foto_perfil as p2_foto,
            p2.club as p2_club, p2.victorias as p2_victorias, p2.derrotas as p2_derrotas,
            u2.nombre as p2_nombre,
            e.nombre as evento_nombre, e.fecha as evento_fecha, e.hora as evento_hora
            FROM peleas pel
            JOIN peleadores p1 ON pel.peleador_1_id = p1.id
            JOIN usuarios u1 ON p1.usuario_id = u1.id
            JOIN peleadores p2 ON pel.peleador_2_id = p2.id
            JOIN usuarios u2 ON p2.usuario_id = u2.id
            JOIN eventos e ON pel.evento_id = e.id
            WHERE pel.resultado = 'pendiente'
            ORDER BY pel.es_pelea_estelar DESC, pel.orden_pelea ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $peleas_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $peleas = [];
        foreach ($peleas_raw as $pelea) {
            $total_votos = $pelea['votos_peleador_1'] + $pelea['votos_peleador_2'];
            $porcentaje_p1 = $total_votos > 0 ? round(($pelea['votos_peleador_1'] / $total_votos) * 100) : 50;
            $porcentaje_p2 = $total_votos > 0 ? round(($pelea['votos_peleador_2'] / $total_votos) * 100) : 50;

            $peleas[] = [
                "id" => (int)$pelea['id'],
                "categoria" => $pelea['categoria_peso'],
                "rounds" => (int)$pelea['numero_rounds'],
                "estelar" => (bool)$pelea['es_pelea_estelar'],
                "soldout" => (bool)$pelea['entradas_agotadas'],
                "orden" => (int)$pelea['orden_pelea'],
                "peleador1" => [
                    "id" => (int)$pelea['p1_id'],
                    "nombre" => $pelea['p1_nombre'],
                    "apodo" => $pelea['p1_apodo'],
                    "foto" => $pelea['p1_foto'],
                    "club" => $pelea['p1_club'],
                    "record" => "{$pelea['p1_victorias']}-{$pelea['p1_derrotas']}",
                    "votos" => (int)$pelea['votos_peleador_1'],
                    "porcentaje" => $porcentaje_p1
                ],
                "peleador2" => [
                    "id" => (int)$pelea['p2_id'],
                    "nombre" => $pelea['p2_nombre'],
                    "apodo" => $pelea['p2_apodo'],
                    "foto" => $pelea['p2_foto'],
                    "club" => $pelea['p2_club'],
                    "record" => "{$pelea['p2_victorias']}-{$pelea['p2_derrotas']}",
                    "votos" => (int)$pelea['votos_peleador_2'],
                    "porcentaje" => $porcentaje_p2
                ],
                "evento" => [
                    "nombre" => $pelea['evento_nombre'],
                    "fecha" => $pelea['evento_fecha'],
                    "hora" => $pelea['evento_hora']
                ]
            ];
        }

        return [
            "success" => true,
            "total" => count($peleas),
            "peleas" => $peleas
        ];
    }

    /**
     * Votar por un peleador en una pelea
     */
    public function votar($pelea_id, $data) {
        if (empty($data['usuario_id']) || empty($data['peleador_id'])) {
            http_response_code(400);
            return [
                "success" => false,
                "message" => "Faltan datos requeridos"
            ];
        }

        try {
            // Verificar que la pelea existe y está pendiente
            $queryPelea = "SELECT * FROM peleas WHERE id = :id AND resultado = 'pendiente'";
            $stmtPelea = $this->db->prepare($queryPelea);
            $stmtPelea->bindParam(':id', $pelea_id);
            $stmtPelea->execute();
            $pelea = $stmtPelea->fetch(PDO::FETCH_ASSOC);

            if (!$pelea) {
                http_response_code(404);
                return [
                    "success" => false,
                    "message" => "Pelea no encontrada o ya finalizada"
                ];
            }

            // Verificar que el peleador pertenece a la pelea
            if ($data['peleador_id'] != $pelea['peleador_1_id'] &&
                $data['peleador_id'] != $pelea['peleador_2_id']) {
                http_response_code(400);
                return [
                    "success" => false,
                    "message" => "Peleador no pertenece a esta pelea"
                ];
            }

            $this->db->beginTransaction();

            // Registrar voto
            $queryVoto = "INSERT INTO votos (pelea_id, usuario_id, peleador_votado_id)
                VALUES (:pelea_id, :usuario_id, :peleador_id)
                ON DUPLICATE KEY UPDATE peleador_votado_id = :peleador_id";

            $stmtVoto = $this->db->prepare($queryVoto);
            $stmtVoto->bindParam(':pelea_id', $pelea_id);
            $stmtVoto->bindParam(':usuario_id', $data['usuario_id']);
            $stmtVoto->bindParam(':peleador_id', $data['peleador_id']);
            $stmtVoto->execute();

            // Actualizar contador de votos
            if ($data['peleador_id'] == $pelea['peleador_1_id']) {
                $queryUpdate = "UPDATE peleas SET votos_peleador_1 = votos_peleador_1 + 1 WHERE id = :id";
            } else {
                $queryUpdate = "UPDATE peleas SET votos_peleador_2 = votos_peleador_2 + 1 WHERE id = :id";
            }

            $stmtUpdate = $this->db->prepare($queryUpdate);
            $stmtUpdate->bindParam(':id', $pelea_id);
            $stmtUpdate->execute();

            $this->db->commit();

            return [
                "success" => true,
                "message" => "Voto registrado exitosamente"
            ];

        } catch (PDOException $e) {
            $this->db->rollBack();
            http_response_code(500);
            return [
                "success" => false,
                "message" => "Error al registrar el voto"
            ];
        }
    }
}