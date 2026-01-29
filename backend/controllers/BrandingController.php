<?php
class BrandingController {
    private $db;
    private $uploadDir = __DIR__ . '/../files/logos/';

    public function __construct($db) {
        $this->db = $db;
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    /**
     * Obtener los logos activos para todos los tipos
     */
    public function getActiveLogos() {
        try {
            $query = "SELECT * FROM logos WHERE activo = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $logos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formatear respuesta con URLs completas
            $host = $_SERVER['HTTP_HOST'];
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $baseUrl = $protocol . "://" . $host . "/files/logos/";

            $result = [
                "card" => null,
                "pdf" => null,
                "header" => null
            ];

            $timestamp = time();
            foreach ($logos as $logo) {
                $result[$logo['tipo']] = [
                    "id" => $logo['id'],
                    "url" => $baseUrl . $logo['nombre_archivo'] . "?t=" . $timestamp,
                    "etiqueta" => $logo['etiqueta'],
                    "dimensiones" => $logo['dimensiones'],
                    "config" => json_decode($logo['config_json'] ?? '{}')
                ];
            }

            return [
                "success" => true,
                "logos" => $result
            ];

        } catch (PDOException $e) {
            error_log("Error obteniendo logos activos: " . $e->getMessage());
            return ["success" => false, "message" => "Error al obtener logos"];
        }
    }

    /**
     * Listar todo el historial de logos
     */
    public function getAllLogos($tipo = null) {
        try {
            $query = "SELECT * FROM logos";
            if ($tipo) {
                $query .= " WHERE tipo = :tipo";
            }
            $query .= " ORDER BY fecha_subida DESC";

            $stmt = $this->db->prepare($query);
            if ($tipo) {
                $stmt->bindParam(':tipo', $tipo);
            }
            $stmt->execute();
            $logos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $host = $_SERVER['HTTP_HOST'];
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $baseUrl = $protocol . "://" . $host . "/files/logos/";

            $timestamp = time();
            foreach ($logos as &$logo) {
                $logo['url'] = $baseUrl . $logo['nombre_archivo'] . "?t=" . $timestamp;
                $logo['config'] = json_decode($logo['config_json'] ?? '{}');
            }

            return [
                "success" => true,
                "logos" => $logos
            ];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Error al listar historial"];
        }
    }

    /**
     * Subir un nuevo logo y activarlo
     */
    public function uploadLogo($files, $data) {
        if (!isset($files['logo'])) {
            return ["success" => false, "message" => "No se recibió el archivo"];
        }

        $tipo = $data['tipo'] ?? 'card'; // default a card si no viene
        $etiqueta = $data['etiqueta'] ?? 'Nuevo Logo';
        
        $file = $files['logo'];
        
        // Obtener extensión de forma más robusta
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($extension)) {
            // Try to determine extension from MIME type if not present in filename
            $mimes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'image/svg+xml' => 'svg'
            ];
            $extension = $mimes[$file['type']] ?? null;
        }

        // Fallback to 'png' if extension still not found
        if (empty($extension)) {
            $extension = 'png';
        }

        $nonce = substr(md5(time() . $file['name']), 0, 8);
        $newFilename = "logo_" . $tipo . "_" . $nonce . "." . $extension;
        $targetPath = $this->uploadDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            try {
                $this->db->beginTransaction();

                // Desactivar anteriores del mismo tipo
                $updateQuery = "UPDATE logos SET activo = 0 WHERE tipo = :tipo";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(':tipo', $tipo);
                $updateStmt->execute();

                // Insertar nuevo
                $imgSize = getimagesize($targetPath);
                $dimensiones = $imgSize ? $imgSize[0] . "x" . $imgSize[1] : null;
                $peso = filesize($targetPath);
                $mime = $file['type'];

                $insertQuery = "INSERT INTO logos (nombre_archivo, tipo_mime, tipo, etiqueta, activo, dimensiones, peso) 
                                VALUES (:nombre, :mime, :tipo, :etiqueta, 1, :dim, :peso)";
                $insertStmt = $this->db->prepare($insertQuery);
                $insertStmt->bindParam(':nombre', $newFilename);
                $insertStmt->bindParam(':mime', $mime);
                $insertStmt->bindParam(':tipo', $tipo);
                $insertStmt->bindParam(':etiqueta', $etiqueta);
                $insertStmt->bindParam(':dim', $dimensiones);
                $insertStmt->bindParam(':peso', $peso);
                $insertStmt->execute();

                $this->db->commit();
                return $this->getActiveLogos();

            } catch (PDOException $e) {
                $this->db->rollBack();
                if (file_exists($targetPath)) unlink($targetPath);
                error_log("Error DB al subir logo: " . $e->getMessage());
                return [
                    "success" => false, 
                    "message" => "Error en base de datos al guardar registro: " . $e->getMessage()
                ];
            }
        } else {
            error_log("Fallo move_uploaded_file. Tmp: " . $file['tmp_name'] . " Dest: " . $targetPath);
            $error = error_get_last();
            
            return [
                "success" => false, 
                "message" => "No se pudo guardar el archivo en el servidor. Revisa permisos de carpeta.",
                "debug" => [
                    "php_error" => $error['message'] ?? 'none',
                    "upload_error_code" => $file['error'],
                    "post_received" => !empty($data),
                    "target_dir" => $this->uploadDir
                ]
            ];
        }
    }

    /**
     * Cambiar logo activo del historial
     */
    public function setActiveLogo($id) {
        try {
            $this->db->beginTransaction();

            // Obtener el tipo de este logo
            $checkQuery = "SELECT tipo FROM logos WHERE id = :id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':id', $id);
            $checkStmt->execute();
            $logo = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$logo) {
                $this->db->rollBack();
                return ["success" => false, "message" => "Logo no encontrado"];
            }

            $tipo = $logo['tipo'];

            // Desactivar todos de ese tipo
            $offQuery = "UPDATE logos SET activo = 0 WHERE tipo = :tipo";
            $offStmt = $this->db->prepare($offQuery);
            $offStmt->bindParam(':tipo', $tipo);
            $offStmt->execute();

            // Activar el elegido
            $onQuery = "UPDATE logos SET activo = 1 WHERE id = :id";
            $onStmt = $this->db->prepare($onQuery);
            $onStmt->bindParam(':id', $id);
            $onStmt->execute();

            $this->db->commit();
            return $this->getActiveLogos();

        } catch (PDOException $e) {
            $this->db->rollBack();
            return ["success" => false, "message" => "Error al activar logo"];
        }
    }
}
