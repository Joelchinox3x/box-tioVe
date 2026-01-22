<?php
// app/Services/ImageService.php - Servicio para manejo de imágenes

namespace App\Services;

class ImageService {

    /**
     * Guardar imagen desde base64
     *
     * @param string $base64Data Datos de imagen en base64
     * @param string $folder Carpeta de destino (clientes, productos, etc)
     * @param string $prefix Prefijo para el nombre del archivo
     * @return string Ruta relativa del archivo guardado
     */
    public function saveBase64Image($base64Data, $folder, $prefix = '') {
        // Limpiar el prefijo base64 si existe (data:image/png;base64,...)
        if (strpos($base64Data, 'data:image') !== false) {
            $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $base64Data);
        }

        // Decodificar
        $imageData = base64_decode($base64Data);

        if ($imageData === false) {
            throw new \Exception('Error al decodificar la imagen');
        }

        // Detectar tipo de imagen
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageData);

        $extension = $this->getExtensionFromMime($mimeType);

        // Generar nombre único
        $filename = $this->generateFilename($prefix, $extension);

        // Crear directorio si no existe
        $uploadDir = __DIR__ . '/../../public/uploads/' . $folder;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Guardar archivo
        $filepath = $uploadDir . '/' . $filename;

        if (file_put_contents($filepath, $imageData) === false) {
            throw new \Exception('Error al guardar la imagen');
        }

        // Retornar ruta relativa desde public/ (nginx DOCUMENT_ROOT ya es public/)
        return 'uploads/' . $folder . '/' . $filename;
    }

    /**
     * Obtener extensión desde MIME type
     */
    private function getExtensionFromMime($mimeType) {
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];

        return $mimeMap[$mimeType] ?? 'jpg';
    }

    /**
     * Generar nombre de archivo único
     */
    private function generateFilename($prefix, $extension) {
        $prefix = preg_replace('/[^a-z0-9]/i', '_', $prefix);
        $prefix = substr($prefix, 0, 20); // Limitar longitud

        $timestamp = time();
        $random = substr(md5(uniqid()), 0, 8);

        return "{$prefix}_{$timestamp}_{$random}.{$extension}";
    }

public function saveMultipleFiles($files, $folder) {
    $savedPaths = [];
    
    // PHP organiza los archivos múltiples de una forma extraña, 
    // así que recorremos por índice
    if (isset($files['name']) && is_array($files['name'])) {
        foreach ($files['name'] as $index => $name) {
            if ($files['error'][$index] === UPLOAD_ERR_OK) {
                // Obtener extensión
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                
                // Generar nombre único
                $filename = $this->generateFilename('img', $ext);
                
                // Directorio
                $uploadDir = __DIR__ . '/../../public/uploads/' . $folder;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $targetPath = $uploadDir . '/' . $filename;
                
                // Mover archivo
                if (move_uploaded_file($files['tmp_name'][$index], $targetPath)) {
                    // OPTIMIZAR IMAGEN (Redimensionar a 1200px y convertir a JPG 75%)
                    $this->optimizeUploadedImage($targetPath);
                    $savedPaths[] = 'uploads/' . $folder . '/' . $filename;
                }
            }
        }
    }
    
    return $savedPaths;
}

    /**
     * Optimizar imagen subida (Reducir tamaño y peso)
     */
    private function optimizeUploadedImage($path) {
        if (!file_exists($path)) return;

        // Obtener info de la imagen
        $info = getimagesize($path);
        if ($info === false) return;

        $mime = $info['mime'];
        $width = $info[0];
        $height = $info[1];

        // Crear recurso de imagen según tipo
        switch ($mime) {
            case 'image/jpeg': $image = imagecreatefromjpeg($path); break;
            case 'image/png': $image = imagecreatefrompng($path); break;
            case 'image/webp': $image = imagecreatefromwebp($path); break;
            default: return; // No soportado
        }

        if (!$image) return;

        // Aumentar memoria temporalmente para manipular imágenes grandes (4K+)
        ini_set('memory_limit', '512M');

        // Corregir orientación básica (EXIF) para JPEGs
        /* 
        if ($mime == 'image/jpeg' && function_exists('exif_read_data')) {
            // Suprimir advertencias de EXIF corruptos
            $exif = @exif_read_data($path);
            if (!empty($exif['Orientation'])) {
                $angle = 0;
                switch ($exif['Orientation']) {
                    case 3: $angle = 180; break;
                    case 6: $angle = -90; break; // Sentido horario
                    case 8: $angle = 90; break;  // Anti-horario
                }

                if ($angle != 0) {
                    $rotated = imagerotate($image, $angle, 0);
                    if ($rotated) {
                        imagedestroy($image); // Liberar original
                        $image = $rotated;
                        
                        // Actualizar dimensiones
                        $width = imagesx($image);
                        $height = imagesy($image);
                    }
                }
            }
        }
        */

        // Calcular nuevas dimensiones (Max 1200px)
        $maxWidth = 1200;
        if ($width > $maxWidth) {
            $newWidth = (int) $maxWidth;
            $newHeight = (int) (($height / $width) * $newWidth);
        } else {
            $newWidth = (int) $width;
            $newHeight = (int) $height;
        }

        // Crear lienzo nuevo
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preservar transparencia inicial (para evitar fondo negro al convertir a JPG)
        $white = imagecolorallocate($newImage, 255, 255, 255);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $white);

        // Redimensionar
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Guardar como JPG COMPRIMIDO (Calidad 75) - Sobrescribe el original
        imagejpeg($newImage, $path, 75);

        // Liberar memoria
        imagedestroy($image);
        imagedestroy($newImage);
    }

    /**
     * Eliminar imagen
     *
     * @param string $path Ruta relativa de la imagen
     * @return bool
     */
    public function deleteImage($path) {
        $fullPath = __DIR__ . '/../../public/' . $path;

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    /**
     * Redimensionar imagen
     *
     * @param string $sourcePath Ruta de la imagen original
     * @param int $maxWidth Ancho máximo
     * @param int $maxHeight Alto máximo
     * @return bool
     */
    public function resizeImage($sourcePath, $maxWidth = 800, $maxHeight = 800) {
        $fullPath = __DIR__ . '/../../public/' . $sourcePath;

        if (!file_exists($fullPath)) {
            return false;
        }

        list($width, $height, $type) = getimagesize($fullPath);

        // Calcular nuevas dimensiones manteniendo proporción
        $ratio = min($maxWidth / $width, $maxHeight / $height);

        if ($ratio >= 1) {
            return true; // Ya es más pequeña
        }

        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        // Crear imagen desde archivo
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($fullPath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($fullPath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($fullPath);
                break;
            default:
                return false;
        }

        // Crear imagen redimensionada
        $destination = imagecreatetruecolor($newWidth, $newHeight);

        // Preservar transparencia para PNG
        if ($type == IMAGETYPE_PNG) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
        }

        // Redimensionar
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Guardar
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($destination, $fullPath, 85);
                break;
            case IMAGETYPE_PNG:
                imagepng($destination, $fullPath, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($destination, $fullPath);
                break;
        }

        // Liberar memoria
        imagedestroy($source);
        imagedestroy($destination);

        return true;
    }
}
