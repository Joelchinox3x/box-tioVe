<?php
/**
 * Controlador de Eventos
 * Gestiona la información del evento principal
 */
class EventosController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Obtener el evento principal con toda su información
     */
    public function getEventoPrincipal() {
        // Obtener evento activo
        $query = "SELECT * FROM eventos WHERE estado = 'proximamente' ORDER BY fecha DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$evento) {
            return [
                "success" => false,
                "message" => "No hay eventos programados"
            ];
        }

        // Obtener peleadores destacados (top 6 más populares)
        $queryPeleadores = "SELECT
            p.id, p.apodo, p.foto_perfil, p.estilo, p.genero,
            p.victorias, p.derrotas, p.empates, p.total_promociones,
            u.nombre, u.apellidos,
            c.nombre as club_nombre
            FROM peleadores p
            JOIN usuarios u ON p.usuario_id = u.id
            LEFT JOIN clubs c ON p.club_id = c.id
            WHERE p.estado_inscripcion = 'aprobado'
            ORDER BY p.total_promociones DESC
            LIMIT 6";

        $stmtPeleadores = $this->db->prepare($queryPeleadores);
        $stmtPeleadores->execute();
        $peleadores_destacados = $stmtPeleadores->fetchAll(PDO::FETCH_ASSOC);

        // Obtener peleas pactadas
        $queryPeleas = "SELECT
            pel.id, pel.categoria_peso, pel.numero_rounds, pel.es_pelea_estelar,
            pel.entradas_agotadas, pel.votos_peleador_1, pel.votos_peleador_2,
            p1.id as p1_id, p1.apodo as p1_apodo, p1.foto_perfil as p1_foto, p1.genero as p1_genero,
            p1.victorias as p1_victorias, p1.derrotas as p1_derrotas,
            u1.nombre as p1_nombre, u1.apellidos as p1_apellidos,
            c1.nombre as p1_club,
            p2.id as p2_id, p2.apodo as p2_apodo, p2.foto_perfil as p2_foto, p2.genero as p2_genero,
            p2.victorias as p2_victorias, p2.derrotas as p2_derrotas,
            u2.nombre as p2_nombre, u2.apellidos as p2_apellidos,
            c2.nombre as p2_club
            FROM peleas pel
            JOIN peleadores p1 ON pel.peleador_1_id = p1.id
            JOIN usuarios u1 ON p1.usuario_id = u1.id
            LEFT JOIN clubs c1 ON p1.club_id = c1.id
            JOIN peleadores p2 ON pel.peleador_2_id = p2.id
            JOIN usuarios u2 ON p2.usuario_id = u2.id
            LEFT JOIN clubs c2 ON p2.club_id = c2.id
            WHERE pel.evento_id = :evento_id
            ORDER BY pel.orden_pelea ASC";

        $stmtPeleas = $this->db->prepare($queryPeleas);
        $stmtPeleas->bindParam(':evento_id', $evento['id']);
        $stmtPeleas->execute();
        $peleas_raw = $stmtPeleas->fetchAll(PDO::FETCH_ASSOC);

        // Formatear peleas
        $peleas_pactadas = [];
        foreach ($peleas_raw as $pelea) {
            $peleas_pactadas[] = [
                "id" => $pelea['id'],
                "categoria" => $pelea['categoria_peso'],
                "rounds" => $pelea['numero_rounds'],
                "estelar" => (bool)$pelea['es_pelea_estelar'],
                "soldout" => (bool)$pelea['entradas_agotadas'],
                "peleador1" => [
                    "id" => $pelea['p1_id'],
                    "nombre" => $pelea['p1_nombre'],
                    "apodo" => $pelea['p1_apodo'],
                    "foto" => $pelea['p1_foto'],
                    "club" => $pelea['p1_club'],
                    "record" => "{$pelea['p1_victorias']}-{$pelea['p1_derrotas']}",
                    "votos" => $pelea['votos_peleador_1']
                ],
                "peleador2" => [
                    "id" => $pelea['p2_id'],
                    "nombre" => $pelea['p2_nombre'],
                    "apodo" => $pelea['p2_apodo'],
                    "foto" => $pelea['p2_foto'],
                    "club" => $pelea['p2_club'],
                    "record" => "{$pelea['p2_victorias']}-{$pelea['p2_derrotas']}",
                    "votos" => $pelea['votos_peleador_2']
                ]
            ];
        }

        // Convertir tipos del evento
        $evento = $this->convertirTipos($evento);

        // Convertir tipos de peleadores
        $peleadores_destacados = array_map([$this, 'convertirTipos'], $peleadores_destacados);

        return [
            "success" => true,
            "evento" => $evento,
            "peleadores_destacados" => $peleadores_destacados,
            "peleas_pactadas" => $peleas_pactadas,
            "countdown" => $this->calcularCountdown($evento['fecha'], $evento['hora'])
        ];
    }

    /**
     * Convertir tipos de datos para JSON
     */
    private function convertirTipos($data) {
        if (!is_array($data)) return $data;

        foreach ($data as $key => $value) {
            // Convertir números
            if (in_array($key, ['id', 'capacidad_total', 'victorias', 'derrotas', 'empates', 'total_promociones', 'experiencia_anos'])) {
                $data[$key] = (int)$value;
            }
            // Convertir decimales
            if (in_array($key, ['peso_actual', 'altura'])) {
                $data[$key] = (float)$value;
            }
            // Convertir booleanos
            if (in_array($key, ['es_pelea_estelar', 'entradas_agotadas'])) {
                $data[$key] = (bool)$value;
            }
        }

        return $data;
    }

    /**
     * Calcular tiempo restante para el evento
     */
    private function calcularCountdown($fecha, $hora) {
        $ahora = new DateTime();
        $evento = new DateTime($fecha . ' ' . $hora);
        $diff = $ahora->diff($evento);

        return [
            "dias" => $diff->days,
            "horas" => $diff->h,
            "minutos" => $diff->i,
            "segundos" => $diff->s
        ];
    }
}
