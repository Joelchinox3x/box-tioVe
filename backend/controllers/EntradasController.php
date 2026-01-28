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
     * NOTA: Este método necesita ser reescrito para usar la tabla tipos_boleto
     */
    public function comprar($data) {
        http_response_code(501);
        return [
            "success" => false,
            "message" => "Sistema de compra en migración. Usa el nuevo sistema de boletos."
        ];
    }

    /**
     * Generar código QR único
     */
    private function generarCodigoQR() {
        return 'EVT-' . strtoupper(bin2hex(random_bytes(8)));
    }
}
