<?php
class SettingsController {
    private $conn;
    private $table_name = "system_settings";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getSetting($key) {
        $query = "SELECT setting_value FROM " . $this->table_name . " WHERE setting_key = :key LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                "success" => true,
                "key" => $key,
                "value" => $row['setting_value']
            ];
        } else {
            return [
                "success" => true,
                "key" => $key,
                "value" => null,
                "is_default" => true
            ];
        }
    }

    public function getAllSettings() {
        $query = "SELECT id, setting_key, setting_value, description, last_updated FROM " . $this->table_name . " ORDER BY id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "success" => true,
            "settings" => $settings
        ];
    }

    public function updateSetting($key, $data) {
        $value = $data['value'] ?? null;

        // Verificar si el setting existe
        $checkQuery = "SELECT id FROM " . $this->table_name . " WHERE setting_key = :key";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(":key", $key);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0) {
            // UPDATE
            $query = "UPDATE " . $this->table_name . " SET setting_value = :value WHERE setting_key = :key";
        } else {
            // INSERT
            $description = $data['description'] ?? null;
            $query = "INSERT INTO " . $this->table_name . " (setting_key, setting_value, description) VALUES (:key, :value, :desc)";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->bindParam(":value", $value);
        if ($checkStmt->rowCount() === 0) {
            $description = $data['description'] ?? null;
            $stmt->bindParam(":desc", $description);
        }
        $stmt->execute();

        return [
            "success" => true,
            "message" => "Setting actualizado",
            "key" => $key,
            "value" => $value
        ];
    }
}
