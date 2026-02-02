<?php
/**
 * Controlador de Subidas Temporales
 * Para puente entre App Nativa y Web Tools
 */
class TempUploadController {
    
    // No necesitamos DB para esto, pero el router la pasa
    public function __construct($db = null) {}

    /**
     * Subir una imagen temporal
     */
    public function upload($files) {
        error_log("--- TEMP UPLOAD REQUEST ---");
        
        if (!isset($files['image']) && !isset($files['foto'])) {
            http_response_code(400);
            return ["success" => false, "message" => "No se recibió ninguna imagen (campo 'image' o 'foto')"];
        }

        $file = isset($files['image']) ? $files['image'] : $files['foto'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            return ["success" => false, "message" => "Error en la subida del archivo", "code" => $file['error']];
        }

        // Directorio temporal (público)
        $uploadDir = __DIR__ . '/../files/temp';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Limpieza básica: Eliminar archivos de más de 1 hora
        $this->cleanOldFiles($uploadDir);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($ext)) $ext = 'jpg';
        
        // Nombre único
        $fileName = 'tmp_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destination = $uploadDir . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Construir URL pública
            // Asumimos que la API está en /api y los files en /files (hermanos en el server real)
            // Ojo: Esto depende de tu estructura de carpetas en producción. 
            // Si /backend es root, entonces /files está bien.
            
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            
            // Ajuste para entorno local vs prod si es necesario
            $publicUrl = "$protocol://$host/backend/files/temp/$fileName";
            
            // Si estamos en boxtiove.com, la ruta suele ser directa si apuntamos a la carpeta correcta
            // En producción probablemente sea: https://boxtiove.com/api/files/temp/... o similar
            // Vamos a intentar devolver una ruta relativa segura para que el cliente la complete o una absoluta estándar
            
            // INTENTO DE RUTA ABSOLUTA INTELIGENTE
            // Si el script está en /home/server/public_html/api/index.php
            // y guardamos en /home/server/public_html/files/temp/
            // la url es domain.com/files/temp/
            
            // Simplificación: Devolver ruta relativa a la raíz del dominio
            $webPath = "files/temp/$fileName"; 
            /* 
               NOTA: En muchos hostings compartidos, 'backend' es una carpeta oculta. 
               Si 'files' debe ser público, debería estar en 'public_html/files'. 
               Asumiremos que ../files es accesible vía web por ahora.
               Si no, tendremos que moverlo a ../public/files
            */
            
            return [
                "success" => true,
                "url" => $publicUrl, // Url completa sugerida
                "path" => $webPath,  // Path relativo
                "message" => "Imagen subida temporalmente"
            ];
        } else {
            http_response_code(500);
            return ["success" => false, "message" => "Error al mover el archivo al destino temporal"];
        }
    }

    private function cleanOldFiles($dir) {
        // Borrar archivos modificados hace más de 60 minutos
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    $filepath = $dir . '/' . $file;
                    if (is_file($filepath)) {
                        if (time() - filemtime($filepath) > 3600) {
                            @unlink($filepath);
                        }
                    }
                }
            }
            closedir($handle);
        }
    }
}
