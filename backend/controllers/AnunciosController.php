<?php
require_once __DIR__ . '/../config/Config.php';

class AnunciosController {
    private $conn;
    private $table_name = "anuncios";
    private $upload_dir;

    public function __construct($db) {
        $this->conn = $db;
        $this->upload_dir = __DIR__ . '/../files/anuncios/';
        if (!file_exists($this->upload_dir)) {
            mkdir($this->upload_dir, 0777, true);
        }
    }

    /**
     * Listar anuncios
     * @param bool $onlyActive Si true, solo devuelve activos y publicados (para publico)
     * @param int|null $eventoId Filtrar por evento (null = incluir globales)
     * @param int|null $limit Limite de resultados
     */
    public function listar($onlyActive = false, $eventoId = null, $limit = null) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";

            if ($onlyActive) {
                $query .= " AND activo = 1";
                $query .= " AND (fecha_publicacion IS NULL OR fecha_publicacion <= NOW())";
                $query .= " AND (fecha_expiracion IS NULL OR fecha_expiracion > NOW())";
            }

            if ($eventoId !== null) {
                $query .= " AND (evento_id = :evento_id OR evento_id IS NULL)";
            }

            $query .= " ORDER BY fijado DESC, orden ASC, created_at DESC";

            if ($limit !== null) {
                $query .= " LIMIT " . intval($limit);
            }

            $stmt = $this->conn->prepare($query);
            if ($eventoId !== null) {
                $stmt->bindParam(':evento_id', $eventoId);
            }
            $stmt->execute();
            $anuncios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agregar URLs completas para media
            $baseUrl = Config::getFileUrl('anuncios/', 'files');
            foreach ($anuncios as &$anuncio) {
                $anuncio['activo'] = (bool)$anuncio['activo'];
                $anuncio['fijado'] = (bool)$anuncio['fijado'];
                if ($anuncio['imagen_filename']) {
                    $anuncio['imagen_url'] = $baseUrl . $anuncio['imagen_filename'];
                } else {
                    $anuncio['imagen_url'] = null;
                }
                if ($anuncio['video_filename']) {
                    $anuncio['video_url'] = $baseUrl . $anuncio['video_filename'];
                } else {
                    $anuncio['video_url'] = null;
                }
            }

            return [
                "success" => true,
                "count" => count($anuncios),
                "data" => $anuncios
            ];

        } catch (PDOException $e) {
            error_log("Error listando anuncios: " . $e->getMessage());
            http_response_code(500);
            return ["success" => false, "message" => "Error al obtener anuncios"];
        }
    }

    /**
     * Obtener anuncio por ID
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$anuncio) {
                http_response_code(404);
                return ["success" => false, "message" => "Anuncio no encontrado"];
            }

            $baseUrl = Config::getFileUrl('anuncios/', 'files');
            $anuncio['activo'] = (bool)$anuncio['activo'];
            $anuncio['fijado'] = (bool)$anuncio['fijado'];
            $anuncio['imagen_url'] = $anuncio['imagen_filename'] ? $baseUrl . $anuncio['imagen_filename'] : null;
            $anuncio['video_url'] = $anuncio['video_filename'] ? $baseUrl . $anuncio['video_filename'] : null;

            return ["success" => true, "data" => $anuncio];

        } catch (PDOException $e) {
            error_log("Error obteniendo anuncio: " . $e->getMessage());
            http_response_code(500);
            return ["success" => false, "message" => "Error al obtener anuncio"];
        }
    }

    /**
     * Crear anuncio (soporta multipart para media)
     */
    public function crear($data, $files = []) {
        try {
            $mensaje = $data['mensaje'] ?? null;
            if (!$mensaje) {
                http_response_code(400);
                return ["success" => false, "message" => "El campo 'mensaje' es requerido"];
            }

            $titulo = $data['titulo'] ?? substr($mensaje, 0, 100);
            $tipo = $data['tipo'] ?? 'info';
            $medio = $data['medio'] ?? 'texto';
            $fijado = isset($data['fijado']) ? (int)$data['fijado'] : 0;
            $eventoId = !empty($data['evento_id']) ? $data['evento_id'] : null;
            $fechaPublicacion = !empty($data['fecha_publicacion']) ? $data['fecha_publicacion'] : null;
            $fechaExpiracion = !empty($data['fecha_expiracion']) ? $data['fecha_expiracion'] : null;
            $fuente = $data['fuente'] ?? 'admin';
            $telegramMessageId = !empty($data['telegram_message_id']) ? $data['telegram_message_id'] : null;

            $imagenFilename = null;
            $videoFilename = null;
            $linkUrl = !empty($data['link_url']) ? $data['link_url'] : null;
            $linkTipo = null;

            // Detectar link de video en texto o campo link_url
            $textToCheck = $linkUrl ?: $mensaje;
            $videoLink = $this->detectVideoLink($textToCheck);
            if ($videoLink) {
                $medio = 'link';
                $linkUrl = $videoLink['url'];
                $linkTipo = $videoLink['tipo'];
            }

            // Manejar subida de imagen
            if (!empty($files['imagen'])) {
                $file = $files['imagen'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($file['type'], $allowedTypes)) {
                    http_response_code(400);
                    return ["success" => false, "message" => "Formato de imagen no permitido. Use JPG, PNG o WebP"];
                }

                $imagenFilename = 'anuncio_' . uniqid() . '.webp';
                $destination = $this->upload_dir . $imagenFilename;

                $image = null;
                if ($file['type'] === 'image/jpeg') {
                    $image = imagecreatefromjpeg($file['tmp_name']);
                } elseif ($file['type'] === 'image/png') {
                    $image = imagecreatefrompng($file['tmp_name']);
                } elseif ($file['type'] === 'image/webp') {
                    $image = imagecreatefromwebp($file['tmp_name']);
                }

                if ($image) {
                    imagewebp($image, $destination, 80);
                    imagedestroy($image);
                    $medio = 'imagen';
                } else {
                    return ["success" => false, "message" => "Error al procesar la imagen"];
                }
            }

            // Manejar subida de video
            if (!empty($files['video'])) {
                $file = $files['video'];
                $allowedTypes = ['video/mp4', 'video/webm', 'video/quicktime'];
                if (!in_array($file['type'], $allowedTypes)) {
                    http_response_code(400);
                    return ["success" => false, "message" => "Formato de video no permitido. Use MP4, WebM o MOV"];
                }

                // Validar tamano (20MB max)
                if ($file['size'] > 20 * 1024 * 1024) {
                    http_response_code(400);
                    return ["success" => false, "message" => "El video excede el limite de 20MB"];
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'mp4';
                $videoFilename = 'anuncio_' . uniqid() . '.' . $ext;
                $destination = $this->upload_dir . $videoFilename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $medio = 'video';
                } else {
                    return ["success" => false, "message" => "Error al guardar el video"];
                }
            }

            // Determinar si debe estar activo
            $activo = 1;
            if ($fechaPublicacion && strtotime($fechaPublicacion) > time()) {
                $activo = 0; // Programado para el futuro
            }

            $query = "INSERT INTO " . $this->table_name . "
                (titulo, mensaje, tipo, medio, imagen_filename, video_filename, link_url, link_tipo,
                 evento_id, activo, fijado, fecha_publicacion, fecha_expiracion, fuente, telegram_message_id)
                VALUES
                (:titulo, :mensaje, :tipo, :medio, :imagen_filename, :video_filename, :link_url, :link_tipo,
                 :evento_id, :activo, :fijado, :fecha_publicacion, :fecha_expiracion, :fuente, :telegram_message_id)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':mensaje', $mensaje);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':medio', $medio);
            $stmt->bindParam(':imagen_filename', $imagenFilename);
            $stmt->bindParam(':video_filename', $videoFilename);
            $stmt->bindParam(':link_url', $linkUrl);
            $stmt->bindParam(':link_tipo', $linkTipo);
            $stmt->bindParam(':evento_id', $eventoId);
            $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
            $stmt->bindParam(':fijado', $fijado, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_publicacion', $fechaPublicacion);
            $stmt->bindParam(':fecha_expiracion', $fechaExpiracion);
            $stmt->bindParam(':fuente', $fuente);
            $stmt->bindParam(':telegram_message_id', $telegramMessageId);
            $stmt->execute();

            $anuncioId = $this->conn->lastInsertId();

            return [
                "success" => true,
                "message" => "Anuncio creado correctamente",
                "anuncio_id" => $anuncioId
            ];

        } catch (PDOException $e) {
            error_log("Error creando anuncio: " . $e->getMessage());
            http_response_code(500);
            return ["success" => false, "message" => "Error al crear anuncio"];
        }
    }

    /**
     * Actualizar anuncio
     */
    public function actualizar($id, $data) {
        try {
            $allowedFields = ['titulo', 'mensaje', 'tipo', 'activo', 'fijado', 'orden',
                              'fecha_publicacion', 'fecha_expiracion', 'evento_id', 'link_url'];

            $setClauses = [];
            $params = [':id' => $id];

            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $data)) {
                    $setClauses[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }

            if (empty($setClauses)) {
                http_response_code(400);
                return ["success" => false, "message" => "No hay campos para actualizar"];
            }

            $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $setClauses) . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                return ["success" => false, "message" => "Anuncio no encontrado"];
            }

            return ["success" => true, "message" => "Anuncio actualizado correctamente"];

        } catch (PDOException $e) {
            error_log("Error actualizando anuncio: " . $e->getMessage());
            http_response_code(500);
            return ["success" => false, "message" => "Error al actualizar anuncio"];
        }
    }

    /**
     * Eliminar anuncio
     */
    public function eliminar($id) {
        try {
            // Obtener archivos asociados
            $query = "SELECT imagen_filename, video_filename FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                http_response_code(404);
                return ["success" => false, "message" => "Anuncio no encontrado"];
            }

            // Borrar archivos fisicos
            if ($row['imagen_filename']) {
                $filePath = $this->upload_dir . $row['imagen_filename'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            if ($row['video_filename']) {
                $filePath = $this->upload_dir . $row['video_filename'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Borrar registro
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                return ["success" => true, "message" => "Anuncio eliminado"];
            }

            return ["success" => false, "message" => "Error al eliminar anuncio"];

        } catch (PDOException $e) {
            error_log("Error eliminando anuncio: " . $e->getMessage());
            http_response_code(500);
            return ["success" => false, "message" => "Error al eliminar anuncio"];
        }
    }

    /**
     * Detectar links de YouTube o TikTok en un texto
     * @return array|null ['tipo' => 'youtube'|'tiktok', 'url' => '...']
     */
    public function detectVideoLink($text) {
        if (!$text) return null;

        // YouTube
        $youtubePatterns = [
            '/https?:\/\/(?:www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/https?:\/\/youtu\.be\/([a-zA-Z0-9_-]+)/',
            '/https?:\/\/(?:www\.)?youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/',
        ];

        foreach ($youtubePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return ['tipo' => 'youtube', 'url' => $matches[0]];
            }
        }

        // TikTok
        $tiktokPatterns = [
            '/https?:\/\/(?:www\.)?tiktok\.com\/@[^\/]+\/video\/\d+/',
            '/https?:\/\/vm\.tiktok\.com\/[a-zA-Z0-9]+/',
        ];

        foreach ($tiktokPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return ['tipo' => 'tiktok', 'url' => $matches[0]];
            }
        }

        return null;
    }

    /**
     * Guardar archivo desde datos binarios (usado por Telegram)
     * @return string|null filename guardado
     */
    public function saveFileFromData($data, $extension, $prefix = 'tg_') {
        $filename = $prefix . uniqid() . '.' . $extension;
        $destination = $this->upload_dir . $filename;

        if (file_put_contents($destination, $data) !== false) {
            return $filename;
        }
        return null;
    }
}
