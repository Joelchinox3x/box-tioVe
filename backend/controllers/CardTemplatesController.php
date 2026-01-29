<?php
require_once __DIR__ . '/../config/Database.php';

class CardTemplatesController {
    private $base_dir;
    private $base_url;

    public function __construct() {
        $this->base_dir = __DIR__ . '/../files/card_templates/';
        
        // Determinar URL base
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $this->base_url = "$protocol://$host/files/card_templates/";
    }

    /**
     * Listar archivos de una categoría (backgrounds o borders)
     */
    public function listar($category) {
        // Validar categoría para seguridad (evitar traversal)
        if (!in_array($category, ['backgrounds', 'borders', 'stickers'])) {
            return [
                "success" => false, 
                "message" => "Categoría inválida. Use 'backgrounds', 'borders' o 'stickers'."
            ];
        }

        $target_dir = $this->base_dir . $category . '/';
        
        // Crear directorio si no existe
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $files = scandir($target_dir);
        $result = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExtensions)) {
                $result[] = [
                    'filename' => $file,
                    'url' => $this->base_url . $category . '/' . $file,
                    'type' => $category
                ];
            }
        }

        return [
            "success" => true,
            "data" => $result
        ];
    }
}
