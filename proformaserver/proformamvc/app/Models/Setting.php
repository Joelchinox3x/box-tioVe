<?php
// app/Models/Setting.php - Modelo de Configuración

namespace App\Models;

use Core\Model;

class Setting extends Model {
    protected $table = 'settings';

    /**
     * Obtiene el valor de una configuración por su clave
     * @param string $key Clave de configuración
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public function get($key, $default = null) {
        $result = $this->query(
            "SELECT setting_value FROM {$this->table} WHERE setting_key = ? LIMIT 1",
            [$key]
        );

        if (!empty($result)) {
            return $result[0]['setting_value'];
        }

        return $default;
    }

    /**
     * Establece el valor de una configuración
     * Si la clave existe, la actualiza. Si no existe, la crea.
     * @param string $key Clave de configuración
     * @param mixed $value Valor a guardar
     * @param string $description Descripción de la configuración (opcional)
     * @return bool
     */
    public function set($key, $value, $description = null) {
        // Verificar si existe
        $existing = $this->query(
            "SELECT id FROM {$this->table} WHERE setting_key = ? LIMIT 1",
            [$key]
        );

        if (!empty($existing)) {
            // Actualizar
            $sql = "UPDATE {$this->table} SET setting_value = ?, updated_at = NOW()";
            $params = [$value];

            if ($description !== null) {
                $sql .= ", description = ?";
                $params[] = $description;
            }

            $sql .= " WHERE setting_key = ?";
            $params[] = $key;

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } else {
            // Crear nuevo
            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table} (setting_key, setting_value, description) VALUES (?, ?, ?)"
            );
            return $stmt->execute([$key, $value, $description]);
        }
    }

    /**
     * Obtiene todas las configuraciones como un array asociativo
     * @return array [key => value]
     */
    public function getAll() {
        $results = $this->query("SELECT setting_key, setting_value FROM {$this->table}");
        $settings = [];

        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    /**
     * Obtiene múltiples configuraciones de una sola vez
     * @param array $keys Array de claves a obtener
     * @return array [key => value]
     */
    public function getMultiple($keys) {
        if (empty($keys)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $results = $this->query(
            "SELECT setting_key, setting_value FROM {$this->table} WHERE setting_key IN ({$placeholders})",
            $keys
        );

        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    /**
     * Elimina una configuración
     * @param string $key Clave de configuración
     * @return bool
     */
    public function remove($key) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE setting_key = ?");
        return $stmt->execute([$key]);
    }

    /**
     * Verifica si una configuración existe
     * @param string $key Clave de configuración
     * @return bool
     */
    public function exists($key) {
        $result = $this->query(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE setting_key = ?",
            [$key]
        );

        return $result[0]['count'] > 0;
    }
}
