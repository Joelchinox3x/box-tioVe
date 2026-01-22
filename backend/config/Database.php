<?php
/**
 * Clase de conexión a la base de datos
 * BoxEvent App
 */
class Database {
    private $host = "mi_mysql";
    private $db_name = "eventobox_db";
    private $username = "server_admin";
    private $password = "Cocacola123";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "error" => "Error de conexión a la base de datos"
            ]);
            exit();
        }

        return $this->conn;
    }
}