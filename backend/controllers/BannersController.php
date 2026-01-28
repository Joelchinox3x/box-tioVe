<?php
require_once __DIR__ . '/../config/Database.php';

class BannersController {
    private $conn;
    private $table_name = "banners";
    private $upload_dir;

    public function __construct($db) {
        $this->conn = $db;
        $this->upload_dir = __DIR__ . '/../files/banners/';
        
        // Ensure directory exists
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    /**
     * Listar banners
     * @param bool $onlyActive Si es true, devuelve solo los activos (para la app)
     */
    public function listar($onlyActive = false) {
        $this->syncFiles(); // Sincronizar archivos físicos con DB

        $query = "SELECT * FROM " . $this->table_name;
        if ($onlyActive) {
            $query .= " WHERE active = 1";
        }
        $query .= " ORDER BY orden ASC, created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Agregar URL completa
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = "$protocol://$host/files/banners/";

        foreach ($banners as &$banner) {
            $banner['url'] = $baseUrl . $banner['filename'];
            $banner['active'] = (bool)$banner['active'];
        }

        return [
            "success" => true,
            "data" => $banners
        ];
    }

    /**
     * Sincroniza los archivos de la carpeta con la base de datos
     * Detecta archivos nuevos y los agrega.
     */
    private function syncFiles() {
        // 1. Obtener archivos físicos
        if (!is_dir($this->upload_dir)) return;
        
        $files = scandir($this->upload_dir);
        $validFiles = [];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExtensions)) {
                $validFiles[] = $file;
            }
        }

        // 2. Obtener archivos en BD
        $query = "SELECT filename FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $dbFiles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // 3. Encontrar nuevos (Están en carpeta pero NO en BD)
        $newFiles = array_diff($validFiles, $dbFiles);

        // 4. Insertar nuevos
        if (!empty($newFiles)) {
            $insertQuery = "INSERT INTO " . $this->table_name . " (filename, original_name, active, orden) VALUES (:filename, :original_name, 1, 0)";
            $insertStmt = $this->conn->prepare($insertQuery);

            foreach ($newFiles as $newFile) {
                // Usamos el nombre del archivo como nombre original
                $insertStmt->bindParam(":filename", $newFile);
                $insertStmt->bindParam(":original_name", $newFile);
                $insertStmt->execute();
            }
        }
    }

    /**
     * Subir nuevo banner
     */
    public function subir($files) {
        if (!isset($files['imagen'])) {
            return ["success" => false, "message" => "No se ha enviado ninguna imagen"];
        }

        $file = $files['imagen'];
        $originalName = $file['name'];
        $tmpName = $file['tmp_name'];
        $fileType = $file['type'];

        // Validar tipo de imagen
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($fileType, $allowedTypes)) {
            return ["success" => false, "message" => "Formato no permitido. Use JPG, PNG o WebP"];
        }

        // Generar nombre único
        $filename = uniqid() . '.webp'; // Convertimos todo a WebP
        $destination = $this->upload_dir . $filename;

        // Convertir y guardar como WebP
        $image = null;
        if ($fileType === 'image/jpeg') {
            $image = imagecreatefromjpeg($tmpName);
        } elseif ($fileType === 'image/png') {
            $image = imagecreatefrompng($tmpName);
        } elseif ($fileType === 'image/webp') {
            $image = imagecreatefromwebp($tmpName);
        }

        if ($image) {
            // Guardar como WebP con calidad 80
            imagewebp($image, $destination, 80);
            imagedestroy($image);
        } else {
            return ["success" => false, "message" => "Error al procesar la imagen"];
        }

        // Guardar en BD
        $query = "INSERT INTO " . $this->table_name . " (filename, original_name, active, orden) VALUES (:filename, :original_name, 1, 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":filename", $filename);
        $stmt->bindParam(":original_name", $originalName);

        if ($stmt->execute()) {
            return [
                "success" => true, 
                "message" => "Banner subido correctamente",
                "data" => [
                    "id" => $this->conn->lastInsertId(),
                    "url" => $this->upload_dir . $filename
                ]
            ];
        }

        return ["success" => false, "message" => "Error al guardar en base de datos"];
    }

    /**
     * Actualizar estado o orden
     */
    public function actualizar($id, $data) {
        if (isset($data['active'])) {
            $query = "UPDATE " . $this->table_name . " SET active = :active WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $activeInt = $data['active'] ? 1 : 0;
            $stmt->bindParam(":active", $activeInt);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
        }
        
        return ["success" => true, "message" => "Actualizado correctamente"];
    }

    /**
     * Eliminar banner
     */
    public function eliminar($id) {
        // Obtener nombre archivo
        $query = "SELECT filename FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return ["success" => false, "message" => "Banner no encontrado"];
        }

        // Borrar archivo físico
        $filePath = $this->upload_dir . $row['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Borrar registro DB
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if ($stmt->execute()) {
            return ["success" => true, "message" => "Banner eliminado"];
        }

        return ["success" => false, "message" => "Error al eliminar de base de datos"];
    }
}
