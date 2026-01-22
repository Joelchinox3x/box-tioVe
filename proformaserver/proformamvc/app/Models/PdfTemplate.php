<?php
namespace App\Models;

use Core\Model;

class PdfTemplate extends Model {
    protected $table = 'pdf_templates';
    
    // Obtener template por nombre (solo activos)
    public function getByName($name) {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE nombre = ? AND activo = 1", 
            [$name]
        );
    }
    
    // Obtener template por defecto
    public function getDefault() {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE es_default = 1 AND activo = 1 LIMIT 1"
        );
    }
    
    // Obtener todos los activos (para el selector)
    public function getAllActive() {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE activo = 1 ORDER BY es_default DESC, nombre ASC"
        );
    }

    // --- NUEVOS MÉTODOS PARA GESTOR ---
    public function getAll() {
        return $this->query("SELECT * FROM {$this->table} ORDER BY id ASC");
    }

    public function getById($id) {
        return $this->queryOne("SELECT * FROM {$this->table} WHERE id = ?", [$id]);
    }
    
    public function lastInsertId() {
        // Core/Model suele tener acceso a PDO
        return $this->db->lastInsertId();
    }
    // Obtener lista de footers en uso (distinct)
    public function getUsedFooters() {
        $rows = $this->query("SELECT DISTINCT footer_img FROM {$this->table} WHERE footer_img IS NOT NULL AND footer_img != ''");
        return array_column($rows, 'footer_img');
    }

    // Obtener lista de fondos en uso (distinct)
    public function getUsedBackgrounds() {
        $rows = $this->query("SELECT DISTINCT fondo_img FROM {$this->table} WHERE fondo_img IS NOT NULL AND fondo_img != ''");
        return array_column($rows, 'fondo_img');
    }
}
