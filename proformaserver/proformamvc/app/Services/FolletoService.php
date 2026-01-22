<?php
// app/Services/FolletoService.php - Servicio para manejo de folletos PDF

namespace App\Services;

class FolletoService {

    
    /**
     * Subir un PDF directamente
     *
     * @param array $file Archivo PDF de $_FILES
     * @return array ['success' => bool, 'ruta' => string, 'tamanio' => int, 'error' => string]
     */
    public function subirPdf($file) {
        try {
            // Validar que el archivo existe
            if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
                return ['success' => false, 'error' => 'Archivo no recibido'];
            }

            // Validar tamaño (máximo 15MB)
            $maxSize = 15 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                return ['success' => false, 'error' => 'El archivo excede el tamaño máximo de 15MB'];
            }

            // Validar MIME type
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if ($mimeType !== 'application/pdf') {
                return ['success' => false, 'error' => "Archivo no es un PDF válido. Tipo: {$mimeType}"];
            }

            // Validar extensión
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                return ['success' => false, 'error' => 'La extensión debe ser .pdf'];
            }

            // Generar nombre único
            $filename = $this->generateFilename('folleto', 'pdf');

            // Directorio de destino
            $uploadDir = __DIR__ . '/../../public/uploads/folletos';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $targetPath = $uploadDir . '/' . $filename;

            // Mover archivo
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return [
                    'success' => true,
                    'ruta' => 'uploads/folletos/' . $filename,
                    'tamanio' => filesize($targetPath)
                ];
            }

            return ['success' => false, 'error' => 'Error al mover el archivo'];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Guardar imágenes fuente para generar PDF
     *
     * @param array $files Array de archivos $_FILES
     * @return array ['success' => bool, 'rutas' => array, 'error' => string]
     */
    public function guardarImagenesFuente($files) {
        $savedPaths = [];

        try {
            if (!isset($files['name']) || !is_array($files['name'])) {
                return ['success' => false, 'error' => 'No se recibieron imágenes'];
            }

            // Directorio de destino
            $uploadDir = __DIR__ . '/../../public/uploads/folletos/fuente';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($files['name'] as $index => $name) {
                if ($files['error'][$index] === UPLOAD_ERR_OK) {
                    // Validar que es una imagen
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($files['tmp_name'][$index]);

                    $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
                    if (!in_array($mimeType, $allowedMimes)) {
                        continue; // Saltar archivos no válidos
                    }

                    // Validar tamaño (máximo 10MB por imagen)
                    $maxSize = 10 * 1024 * 1024;
                    if ($files['size'][$index] > $maxSize) {
                        continue;
                    }

                    // Generar nombre único usando la extensión original (ej. webp)
                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                    $filename = $this->generateFilename('fuente', $ext);

                    $targetPath = $uploadDir . '/' . $filename;

                    // Mover archivo
                    if (move_uploaded_file($files['tmp_name'][$index], $targetPath)) {
                        $savedPaths[] = 'uploads/folletos/fuente/' . $filename;
                    }
                }
            }

            if (empty($savedPaths)) {
                return ['success' => false, 'error' => 'No se pudo guardar ninguna imagen'];
            }

            return ['success' => true, 'rutas' => $savedPaths];

        } catch (\Exception $e) {
            // Limpiar archivos guardados si hubo error
            foreach ($savedPaths as $path) {
                $fullPath = __DIR__ . '/../../public/' . $path;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Generar PDF desde imágenes
     *
     * @param array $imagenesPaths Rutas de las imágenes
     * @param string $nombreProducto Nombre del producto para el PDF
     * @return array ['success' => bool, 'ruta' => string, 'tamanio' => int, 'error' => string]
     */
    /**
     * Generar PDF desde imágenes
     *
     * @param array $imagenesPaths Rutas de las imágenes
     * @param string $nombreProducto Nombre del producto para el PDF
     * @return array ['success' => bool, 'ruta' => string, 'tamanio' => int, 'error' => string]
     */
    public function generarPdfDesdeImagenes($imagenesPaths, $nombreProducto = 'Producto') {
        $tempFiles = [];

        try {
            if (empty($imagenesPaths)) {
                return ['success' => false, 'error' => 'No se proporcionaron imágenes'];
            }

            // Verificar librería mPDF
            $mpdfPath = __DIR__ . '/../../vendor/autoload.php';
            if (!file_exists($mpdfPath)) {
                return ['success' => false, 'error' => 'mPDF no está instalado'];
            }
            require_once $mpdfPath;

            // 1. CONFIGURACIÓN "PRO" (Idéntica a PdfEngine)
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];
            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_top' => 0,
                'margin_bottom' => 0,
                'margin_left' => 0,
                'margin_right' => 0,
                'tempDir' => __DIR__ . '/../../public/tmp',
                'fontDir' => array_merge($fontDirs, [__DIR__ . '/../../public/assets/fonts']),
                'fontdata' => $fontData + [
                    'tekolocal' => ['R' => 'Teko-Regular.ttf', 'B' => 'Teko-Bold.ttf'],
                    'robotocondensed' => ['R' => 'RobotoCondensed-Regular.ttf', 'B' => 'RobotoCondensed-Bold.ttf'],
                    'abadi' => ['R' => 'abadi-mt.ttf', 'B' => 'abadi-mt-bold.ttf']
                ],
                'default_font' => 'sans-serif'
            ]);

            // Metadatos
            $mpdf->SetCreator('Tradimacova App');
            $mpdf->SetAuthor('Tradimacova');
            $mpdf->SetTitle('Folleto - ' . $nombreProducto);
            
            // 2. PROCESAR CADA IMAGEN COMO UNA "VISTA" INDEPENDIENTE
            $isFirstPage = true;

            foreach ($imagenesPaths as $imagenPath) {
                $fullPath = __DIR__ . '/../../public/' . $imagenPath;
                if (!file_exists($fullPath)) continue;

                // USAR WEBP DIRECTO (Solicitado por usuario)
                // mPDF 8+ soporta WebP si la extensión GD lo permite.
                // Eliminamos la conversión a JPG.
                $imageSrc = $fullPath;

                // 3. AGREGAR PÁGINA (Si no es la primera)
                if (!$isFirstPage) {
                    $mpdf->AddPage();
                }
                $isFirstPage = false;

                // 4. ESCRIBIR HTML COMPLETO PARA ESTA PÁGINA
                // Simulamos comportarniento de "styles.php" inyectando el CSS nuevamente
                // Esto fuerza a mPDF a re-evaluar el background para el body en esta página
                $htmlPage = '
                <style>
                    body {
                        background-image: url("' . $imageSrc . '");
                        background-image-resize: 6; /* Stretch to page */
                        background-repeat: no-repeat;
                        background-position: center center;
                        margin: 0;
                        padding: 0;
                    }
                </style>
                <body>
                    <!-- Contenido vacío, el fondo hace todo el trabajo -->
                    <div></div>
                </body>';

                $mpdf->WriteHTML($htmlPage);
            }

            // CLEANUP
            foreach ($tempFiles as $tFile) {
                if (file_exists($tFile)) unlink($tFile);
            }

            // Guardar PDF
            $filename = $this->generateFilename('folleto_generado', 'pdf');
            $outputDir = __DIR__ . '/../../public/uploads/folletos';
            if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
            
            $outputPath = $outputDir . '/' . $filename;
            $mpdf->Output($outputPath, \Mpdf\Output\Destination::FILE);

            return [
                'success' => true,
                'ruta' => 'uploads/folletos/' . $filename,
                'tamanio' => filesize($outputPath)
            ];

        } catch (\Exception $e) {
            foreach ($tempFiles as $tFile) if (file_exists($tFile)) unlink($tFile);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Eliminar archivo PDF y sus imágenes fuente
     *
     * @param string $rutaPdf Ruta relativa del PDF
     * @param array $rutasImagenes Rutas de imágenes fuente (opcional)
     * @return bool
     */
    public function eliminarFolleto($rutaPdf, $rutasImagenes = []) {
        $success = true;

        // Eliminar PDF
        if (!empty($rutaPdf)) {
            $pdfPath = __DIR__ . '/../../public/' . $rutaPdf;
            if (file_exists($pdfPath)) {
                $success = unlink($pdfPath) && $success;
            }
        }

        // Eliminar imágenes fuente
        if (!empty($rutasImagenes) && is_array($rutasImagenes)) {
            foreach ($rutasImagenes as $imagen) {
                $imgPath = __DIR__ . '/../../public/' . $imagen;
                if (file_exists($imgPath)) {
                    $success = unlink($imgPath) && $success;
                }
            }
        }

        return $success;
    }

    /**
     * Obtener tamaño del archivo en formato legible
     *
     * @param string $path Ruta relativa del archivo
     * @return string
     */
    public function getFileSize($path) {
        $fullPath = __DIR__ . '/../../public/' . $path;

        if (!file_exists($fullPath)) {
            return '0 KB';
        }

        $bytes = filesize($fullPath);

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    /**
     * Generar nombre de archivo único
     *
     * @param string $prefix Prefijo
     * @param string $extension Extensión
     * @return string
     */
    private function generateFilename($prefix, $extension) {
        $prefix = preg_replace('/[^a-z0-9]/i', '_', $prefix);
        $prefix = substr($prefix, 0, 20);

        $timestamp = time();
        $random = substr(md5(uniqid()), 0, 8);

        return "{$prefix}_{$timestamp}_{$random}.{$extension}";
    }
}
