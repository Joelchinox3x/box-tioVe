<?php
/**
 * ConversationManager - Maneja estado de conversaciones multi-paso en Telegram
 * Usa tabla telegram_conversations para persistir estado entre requests
 */
class ConversationManager {
    private $conn;

    // Estados posibles
    const IDLE = 'idle';

    // Flujo Anuncio
    const ANUNCIO_TIPO = 'anuncio_tipo';
    const ANUNCIO_MENSAJE = 'anuncio_mensaje';
    const ANUNCIO_MEDIA = 'anuncio_media';
    const ANUNCIO_CONFIRMAR = 'anuncio_confirmar';

    // Flujo Venta
    const VENTA_TIPO = 'venta_tipo';
    const VENTA_CANTIDAD = 'venta_cantidad';
    const VENTA_NOMBRE = 'venta_nombre';
    const VENTA_DNI = 'venta_dni';
    const VENTA_TELEFONO = 'venta_telefono';
    const VENTA_PAGO = 'venta_pago';
    const VENTA_CONFIRMAR = 'venta_confirmar';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener estado actual de la conversacion
     */
    public function getState($chatId) {
        $stmt = $this->conn->prepare(
            "SELECT state, step, data FROM telegram_conversations WHERE chat_id = :chat_id LIMIT 1"
        );
        $stmt->bindParam(':chat_id', $chatId);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return [
                'state' => self::IDLE,
                'step' => 0,
                'data' => [],
            ];
        }

        return [
            'state' => $row['state'],
            'step' => (int)$row['step'],
            'data' => $row['data'] ? json_decode($row['data'], true) : [],
        ];
    }

    /**
     * Establecer estado de la conversacion
     */
    public function setState($chatId, $state, $step = 0, $data = null) {
        $dataJson = $data !== null ? json_encode($data) : null;
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hora

        $stmt = $this->conn->prepare(
            "INSERT INTO telegram_conversations (chat_id, state, step, data, expires_at)
             VALUES (:chat_id, :state, :step, :data, :expires_at)
             ON DUPLICATE KEY UPDATE state = :state2, step = :step2, data = :data2, expires_at = :expires_at2"
        );
        $stmt->execute([
            ':chat_id' => $chatId,
            ':state' => $state,
            ':step' => $step,
            ':data' => $dataJson,
            ':expires_at' => $expiresAt,
            ':state2' => $state,
            ':step2' => $step,
            ':data2' => $dataJson,
            ':expires_at2' => $expiresAt,
        ]);
    }

    /**
     * Actualizar solo los datos sin cambiar el estado
     */
    public function updateData($chatId, $newData) {
        $current = $this->getState($chatId);
        $merged = array_merge($current['data'], $newData);
        $this->setState($chatId, $current['state'], $current['step'], $merged);
    }

    /**
     * Avanzar al siguiente paso
     */
    public function nextStep($chatId, $newState, $extraData = []) {
        $current = $this->getState($chatId);
        $data = array_merge($current['data'], $extraData);
        $this->setState($chatId, $newState, $current['step'] + 1, $data);
    }

    /**
     * Resetear al estado idle
     */
    public function reset($chatId) {
        $this->setState($chatId, self::IDLE, 0, []);
    }

    /**
     * Limpiar conversaciones expiradas
     */
    public function cleanExpired() {
        $this->conn->exec("DELETE FROM telegram_conversations WHERE expires_at < NOW()");
    }
}
