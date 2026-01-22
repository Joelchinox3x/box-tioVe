<?php
// app/Controllers/PdfTemplateController.php

namespace App\Controllers;

use Core\Controller;
use App\Models\PdfTemplate;

class PdfTemplateController extends Controller {
    private $pdfTemplateModel;

    public function __construct() {
        $this->pdfTemplateModel = new PdfTemplate();
    }

    // LISTADO (Dashboard)
    public function index() {
        // Ejecutar limpieza de PDFs temporales aleatoriamente
        require_once __DIR__ . '/../Helpers/CleanupHelper.php';
        \App\Helpers\CleanupHelper::randomCleanup();

        $templates = $this->pdfTemplateModel->getAll(); // Necesitaremos asegurar que este metodo exista o usar query directa
        // Si no existe getAll en el modelo, usamos query
        if(!method_exists($this->pdfTemplateModel, 'getAll')) {
             // Cambio solicitado: Orden por ID (1, 2, 3...)
             $templates = $this->pdfTemplateModel->query("SELECT * FROM pdf_templates ORDER BY id ASC");
        }

        $this->view('pdf_templates/index', [
            'templates' => $templates
        ]);
    }

    // FORMULARIO CREAR
    public function create() {
        $usedFooters = $this->pdfTemplateModel->getUsedFooters();
        $usedBackgrounds = $this->pdfTemplateModel->getUsedBackgrounds();
        $this->view('pdf_templates/create', [
            'usedFooters' => $usedFooters,
            'usedBackgrounds' => $usedBackgrounds
        ]);
    }

    // GUARDAR NUEVO
    public function store() {
        $nombre = $_POST['nombre'] ?? 'Nuevo Tema';
        // Generar nombre interno unico (slug)
        $internalName = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)));
        
        // Evitar duplicados de nombre interno
        $existe = $this->pdfTemplateModel->getByName($internalName);
        if($existe) {
            $internalName .= '-' . time();
        }

        $data = [
            'nombre' => $internalName, // identificador
            'display_name' => $nombre, // Nombre legible 
            'color_brand' => $_POST['color_brand'] ?? '#333333',
            'icon'        => $_POST['icon'] ?? 'ph-paint-brush', // Nuevo campo icono
            'fondo_img'   => '', // Se sube despues o aqui
            'es_default'  => 0
        ];

        // Insertar 
        $sql = "INSERT INTO pdf_templates (nombre, color_brand, icon, es_default) VALUES (:nombre, :color, :icon, 0)";
        $params = [
            ':nombre' => $data['nombre'],
            ':color' => $data['color_brand'],
            ':icon'  => $data['icon']
        ];
        
        $this->pdfTemplateModel->query($sql, $params);
        
        header('Location: /pdf-templates');
    }

    // FORMULARIO EDITAR
    public function edit($id) {
        $template = $this->pdfTemplateModel->getById($id);
        if(!$template) {
            die("Template no encontrado");
        }
        $usedFooters = $this->pdfTemplateModel->getUsedFooters();
        $usedBackgrounds = $this->pdfTemplateModel->getUsedBackgrounds();

        // Obtener fuentes disponibles (solo Regular y Bold)
        $fontDir = __DIR__ . '/../../public/assets/fonts';
        $availableFonts = [];
        if (is_dir($fontDir)) {
            $files = scandir($fontDir);
            foreach ($files as $f) {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                if ($ext === 'ttf' || $ext === 'otf') {
                    $filename = pathinfo($f, PATHINFO_FILENAME);

                    // Solo procesar Regular y Bold (ignorar SemiBold, Light, Medium, etc.)
                    if (preg_match('/^(.+?)(?:-(Regular|Bold))?$/i', $filename, $matches)) {
                        $variant = isset($matches[2]) ? strtolower($matches[2]) : 'regular';

                        // Saltar si no es Regular ni Bold
                        if ($variant !== 'regular' && $variant !== 'bold') {
                            continue;
                        }

                        $familyKey = strtolower(trim($matches[1]));

                        if (!isset($availableFonts[$familyKey])) {
                            // Formatting: "open-sans" -> "Open Sans"
                            $cleanName = str_replace(['-', '_'], ' ', $familyKey);
                            $availableFonts[$familyKey] = ucwords($cleanName);
                        }
                    }
                }
            }
        }

        $this->view('pdf_templates/edit', [
            'template' => $template, 
            'usedFooters' => $usedFooters,
            'usedBackgrounds' => $usedBackgrounds,
            'availableFonts' => $availableFonts
        ]);
    }

    // ACTUALIZAR
    public function update($id) {
        $template = $this->pdfTemplateModel->getById($id);
        if(!$template) die("Error");

        $color = $_POST['color_brand'];
        $icon  = $_POST['icon'];
        
        $nombre = $_POST['nombre'] ?? $template['nombre'];
        // Slugify name for file naming
        $nombreSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre)));

        // Nuevos campos de fuente
        $titleFont = $_POST['title_font'] ?? 'sans-serif';
        $titleSize = $_POST['title_size'] ?? 31;
        $titleBold = isset($_POST['title_bold']) ? 1 : 0;
        $titleColor = $_POST['title_color'] ?? '#000000';

        // Subida de Fondo
        $fondo_path = $template['fondo_img'];
        
        // 1. Selector de Galería (Prioridad Baja)
        if (!empty($_POST['fondo_select'])) {
             $fondo_path = $_POST['fondo_select'];
        }
        
        // 2. Logic Delete Fondo
        if (isset($_POST['delete_fondo']) && $_POST['delete_fondo'] == '1') {
             $fondo_path = ''; 
             // NOTA: Ya no eliminamos el archivo físico aquí de inmediato.
        }

        // 3. Subida Manual (Prioridad Alta)
        if(isset($_FILES['fondo_img']) && $_FILES['fondo_img']['error'] === 0) {
            $ext = pathinfo($_FILES['fondo_img']['name'], PATHINFO_EXTENSION);
            $filename = 'fondo_' . time() . '.' . $ext;
            $dir = __DIR__ . '/../Views/pdf/fondo/';
            if(!is_dir($dir)) mkdir($dir, 0777, true);
            
            $destination = $dir . $filename;
            if(move_uploaded_file($_FILES['fondo_img']['tmp_name'], $destination)) {
                $fondo_path = $filename;
            }
        }

        // Subida de Footer (Lógica Robustecida igual que Fondo)
        $footerImg = $template['footer_img'];
        
        // 1. Selector (footer_select)
        if (!empty($_POST['footer_select'])) {
             $footerImg = $_POST['footer_select'];
        }
        
        // 2. Logic Delete Footer
        if (isset($_POST['delete_footer']) && $_POST['delete_footer'] == '1') {
             $footerImg = ''; 
        }

        // 3. Subida Manual (Prioridad Alta) - Reemplaza todo lo anterior
        if(isset($_FILES['new_footer_file']) && $_FILES['new_footer_file']['error'] === 0) {
            $ext = pathinfo($_FILES['new_footer_file']['name'], PATHINFO_EXTENSION);
            // Format: footer_[SlugName]_[XXX].ext
            $filename = 'footer_' . $nombreSlug . '_' . rand(100,999) . '.' . $ext;
            
            $dir = __DIR__ . '/../Views/pdf/footer/';
            if(!is_dir($dir)) mkdir($dir, 0777, true);
            @chmod($dir, 0777); 

            $destination = $dir . $filename;
            
            if(move_uploaded_file($_FILES['new_footer_file']['tmp_name'], $destination)) {
                $footerImg = $filename; 
            }
        }

        // Actualizar SQL
        $sql = "UPDATE pdf_templates SET nombre = :nombre, color_brand = :color, icon = :icon, fondo_img = :fondo, header_php = :header, footer_img = :footer, title_font = :tfont, title_size = :tsize, title_bold = :tbold, title_color = :tcolor WHERE id = :id";
        
        $headerPhp = $_POST['header_php'] ?? 'header_simple.php';

        $this->pdfTemplateModel->query($sql, [
            ':nombre' => $nombreSlug, // We use slug as name/ID
            ':color' => $color,
            ':icon' => $icon,
            ':fondo' => $fondo_path,
            ':header' => $headerPhp,
            ':footer' => $footerImg,
            ':tfont' => $titleFont,
            ':tsize' => $titleSize, 
            ':tbold' => $titleBold,
            ':tcolor' => $titleColor,
            ':id' => $id
        ]);

        header("Location: /pdf-templates/edit/" . $id . "?msg=updated");
    }

    // AJAX: Subir Background (Para el botón "Agregar" en el select)
    public function uploadBackground() {
        header('Content-Type: application/json');

        if (!isset($_FILES['background_file']) || $_FILES['background_file']['error'] !== UPLOAD_ERR_OK) {
             http_response_code(400);
             echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo o hubo un error.']);
             return;
        }

        $dir = __DIR__ . '/../Views/pdf/fondo/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        @chmod($dir, 0777);

        $file = $_FILES['background_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['png', 'jpg', 'jpeg', 'webp'];

        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Formato no permitido. Solo PNG, JPG, JPEG, WEBP.']);
            return;
        }

        // Generate short random name (3 chars)
        $random = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 3);
        $filename = 'fondo_' . $random . '.' . $ext;
        
        if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            $displayName = 'Fondo ' . strtoupper($random);
            echo json_encode([
                'success' => true, 
                'filename' => $filename,
                'name' => $displayName,
                'url' => url('/pdf-templates/image/' . $filename)
            ]);
        } else {
             http_response_code(500);
             echo json_encode(['success' => false, 'message' => 'Error al mover el archivo subido.']);
        }
    }
    
    // SERVIR IMAGEN PROTEGIDA
    public function serveImage($filename) {
        $path = __DIR__ . '/../Views/pdf/fondo/' . basename($filename);
        if(file_exists($path)) {
            $mime = mime_content_type($path);
            header("Content-Type: $mime");
            readfile($path);
        } else {
            http_response_code(404);
            echo "Image not found";
        }
    }

    public function serveFooter($filename) {
        $filePath = __DIR__ . '/../Views/pdf/footer/' . basename($filename);

        if (file_exists($filePath)) {
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $filePath);
            finfo_close($fileInfo);

            header('Content-Type: ' . $mimeType);
            readfile($filePath);
            exit;
        } else {
            http_response_code(404);
            echo "Footer Image not found";
        }
    }



    // ELIMINAR TEMA
    public function delete($id) {
        $sql = "DELETE FROM pdf_templates WHERE id = :id";
        $this->pdfTemplateModel->query($sql, [':id' => $id]);
        header("Location: /pdf-templates");
    }

    // CLONAR
    public function duplicate($id) {
        $template = $this->pdfTemplateModel->getById($id);
        if(!$template) die("Error");

        // Permitir nombre personalizado desde el modal
        if (isset($_POST['new_name']) && !empty($_POST['new_name'])) {
            $displayName = trim($_POST['new_name']);
            // Slugify simple
            $newName = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $displayName))) . '-' . time();
        } else {
            $newName = $template['nombre'] . '-copy-' . time();
        }
        
        // El campo display_name no existe en la BD actual, asi que usamos solo nombre (slug)
        // $displayName se usa para generar el slug arriba, pero no se guarda.
        
        $sql = "INSERT INTO pdf_templates (nombre, color_brand, icon, fondo_img, es_default) 
                VALUES (:nombre, :color, :icon, :fondo, 0)";
                
        $icon = $template['icon'] ?? 'ph-paint-brush';
        
        $this->pdfTemplateModel->query($sql, [
            ':nombre' => $newName,
            ':color' => $template['color_brand'],
            ':icon' => $icon,
            ':fondo' => $template['fondo_img']
        ]);

        header("Location: /pdf-templates");
    }

    // ACTUALIZAR (Existing)
    // ...

    // AJAX: Subir Footer (Para el botón "Agregar" en el select)
    // AJAX Endpoint for Footer Upload
    public function uploadFooter() {
        header('Content-Type: application/json');

        if (!isset($_FILES['footer_file']) || $_FILES['footer_file']['error'] !== UPLOAD_ERR_OK) {
             http_response_code(400);
             echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo o hubo un error.']);
             return;
        }

        $dir = __DIR__ . '/../Views/pdf/footer/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        @chmod($dir, 0777);

        $file = $_FILES['footer_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['png', 'jpg', 'jpeg', 'webp'];

        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Formato no permitido. Solo PNG, JPG, JPEG, WEBP.']);
            return;
        }

        // Consistent Naming Logic (Like Background)
        // Generate short random name (3 chars)
        $random = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 3);
        $filename = 'footer_' . $random . '.' . $ext;
        
        $destination = $dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $src = url('/pdf-templates/footer-image/' . $filename);
            
            // Generate clean display name
            $display = 'Footer ' . strtoupper($random);
            
            echo json_encode([
                'success' => true,
                'filename' => $filename,
                'src' => $src,
                'name' => $display,
                'message' => 'Footer subido correctamente.'
            ]);
        } else {
             http_response_code(500);
             echo json_encode(['success' => false, 'message' => 'Error al mover el archivo.']);
        }
    }

    // AJAX: Eliminar Footer de la Galería
    public function deleteFooter() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        $filename = $data['filename'] ?? '';
        
        if (empty($filename)) {
            echo json_encode(['success' => false, 'message' => 'Filename required']);
            return;
        }

        // Security check: basename only
        $filename = basename($filename);
        
        // Check if in use
        $usedFooters = $this->pdfTemplateModel->getUsedFooters();
        if(in_array($filename, $usedFooters)) {
             echo json_encode(['success' => false, 'message' => 'No se puede eliminar: El archivo está en uso.']);
             return;
        }

        $path = __DIR__ . '/../Views/pdf/footer/' . $filename;

        if (file_exists($path)) {
            if (unlink($path)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Could not delete file']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'File not found']);
        }
    }

    // AJAX: Eliminar Fondo de la Galería
    public function deleteBackground() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        $filename = $data['filename'] ?? '';
        
        if (empty($filename)) {
            echo json_encode(['success' => false, 'message' => 'Filename required']);
            return;
        }

        // Security check: basename only
        $filename = basename($filename);
        
        // Check if in use
        $used = $this->pdfTemplateModel->getUsedBackgrounds();
        if(in_array($filename, $used)) {
             echo json_encode(['success' => false, 'message' => 'No se puede eliminar: El archivo está en uso.']);
             return;
        }

        $path = __DIR__ . '/../Views/pdf/fondo/' . $filename; 

        if (file_exists($path)) {
            if (unlink($path)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Could not delete file']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'File not found']);
        }
    }

    // VISTA PREVIA - Generar PDF temporal con datos fake
    public function preview($id) {
        $template = $this->pdfTemplateModel->getById($id);
        if (!$template) {
            die("Template no encontrado");
        }

        // Datos fake para la proforma de prueba
        $fakeData = [
            'id' => 99999,
            'fecha_creacion' => date('Y-m-d'),
            'moneda' => 'PEN',
            'cliente_nombre' => 'EMPRESA DEMO S.A.C.',
            'dni_ruc' => '20123456789',
            'direccion' => 'Av. Ejemplo 123, Lima, Perú',
            'telefono' => '999888777',
            'email' => 'demo@ejemplo.com',
            'items' => [
                [
                    'producto_id' => null,
                    'descripcion' => 'EXCAVADORA HIDRÁULICA CAT 320D - Demo',
                    'cantidad' => 1,
                    'precio_unitario' => 85000.00,
                    'subtotal' => 85000.00,
                    'incluir_ficha' => 0,
                    'incluir_fotos' => 0,
                    'incluir_galeria' => 0
                ],
                [
                    'producto_id' => null,
                    'descripcion' => 'CARGADOR FRONTAL 950H - Demo',
                    'cantidad' => 1,
                    'precio_unitario' => 45000.00,
                    'subtotal' => 45000.00,
                    'incluir_ficha' => 0,
                    'incluir_fotos' => 0,
                    'incluir_galeria' => 0
                ]
            ],
            'subtotal' => 130000.00,
            'igv' => 23400.00,
            'total' => 153400.00,
            'template' => $template['nombre']
        ];

        // Generar PDF usando PdfService
        require_once __DIR__ . '/../Services/PdfService.php';
        $pdfService = new \App\Services\PdfService();

        try {
            $pdfPath = $pdfService->generateProformaPdf($fakeData, $template['nombre']);

            // Marcar como temporal para auto-eliminación (agregar prefijo al nombre)
            $fullPath = __DIR__ . '/../../public/' . $pdfPath;
            $tempPath = str_replace('proforma_', 'proforma_PREVIEW_', $fullPath);
            rename($fullPath, $tempPath);

            $tempRelativePath = str_replace('proforma_', 'proforma_PREVIEW_', $pdfPath);

            // Crear archivo marcador con timestamp para auto-eliminación
            $markerPath = $tempPath . '.delete_after';
            file_put_contents($markerPath, time() + (5 * 60)); // 5 minutos

            // Redirigir al PDF
            header('Location: /' . $tempRelativePath);
            exit;

        } catch (\Exception $e) {
            die("Error al generar vista previa: " . $e->getMessage());
        }
    }

    // RESTAURAR desde Snapshot
    public function restore($id) {
        $template = $this->pdfTemplateModel->getById($id);
        if (!$template) {
            die("Template no encontrado");
        }

        // Restaurar SOLO desde snapshot
        $snapshotPath = __DIR__ . '/../Views/pdf/snapshots/tema_' . $id . '_snapshot.json';
        $snapshotDir = __DIR__ . '/../Views/pdf/snapshots/';

        if (!file_exists($snapshotPath)) {
            die("Error: Este tema no tiene un punto de restauración guardado");
        }

        // Cargar snapshot
        $snapshotData = json_decode(file_get_contents($snapshotPath), true);
        $original = $snapshotData;
        $sourceDir = $snapshotDir;

        // Restaurar archivos de fondo
        $fondoRestored = '';
        if (!empty($original['fondo_img'])) {
            $sourceFondoPath = $sourceDir . 'fondos/' . $original['fondo_img'];
            $targetFondoPath = __DIR__ . '/../Views/pdf/fondo/' . $original['fondo_img'];

            if (file_exists($sourceFondoPath)) {
                @copy($sourceFondoPath, $targetFondoPath);
                $fondoRestored = $original['fondo_img'];
            }
        }

        // Restaurar archivos de footer
        $footerRestored = '';
        if (!empty($original['footer_img'])) {
            $sourceFooterPath = $sourceDir . 'footers/' . $original['footer_img'];
            $targetFooterPath = __DIR__ . '/../Views/pdf/footer/' . $original['footer_img'];

            if (file_exists($sourceFooterPath)) {
                @copy($sourceFooterPath, $targetFooterPath);
                $footerRestored = $original['footer_img'];
            }
        }

        // Restaurar header PHP
        if (!empty($original['header_php'])) {
            $sourceHeaderPath = $sourceDir . 'headers/' . $original['header_php'];
            $targetHeaderPath = __DIR__ . '/../Views/pdf/master/partials/' . $original['header_php'];

            if (file_exists($sourceHeaderPath)) {
                @copy($sourceHeaderPath, $targetHeaderPath);
            }
        }

        // Actualizar base de datos
        $sql = "UPDATE pdf_templates SET
                nombre = :nombre,
                color_brand = :color,
                icon = :icon,
                header_php = :header,
                footer_img = :footer,
                fondo_img = :fondo,
                title_font = :tfont,
                title_size = :tsize,
                title_bold = :tbold,
                title_color = :tcolor,
                es_default = :default
                WHERE id = :id";

        $this->pdfTemplateModel->query($sql, [
            ':nombre' => $original['nombre'],
            ':color' => $original['color_brand'],
            ':icon' => $original['icon'],
            ':header' => $original['header_php'],
            ':footer' => $footerRestored ?: null,
            ':fondo' => $fondoRestored ?: null,
            ':tfont' => $original['title_font'] ?? 'sans-serif',
            ':tsize' => $original['title_size'] ?? 31,
            ':tbold' => $original['title_bold'] ?? 1,
            ':tcolor' => $original['title_color'] ?? '#000000',
            ':default' => $original['es_default'] ?? 0,
            ':id' => $id
        ]);

        header("Location: /pdf-templates");
    }

    // AJAX: Renombrar Fondo
    public function renameBackground() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        $oldName = isset($data['old_name']) ? basename($data['old_name']) : '';
        $newName = isset($data['new_name']) ? trim($data['new_name']) : '';
        
        if (empty($oldName) || empty($newName)) {
            echo json_encode(['success' => false, 'message' => 'Nombres inválidos']);
            return;
        }

        $dir = __DIR__ . '/../Views/pdf/fondo/';
        $oldPath = $dir . $oldName;
        
        if (!file_exists($oldPath)) {
            echo json_encode(['success' => false, 'message' => 'El archivo original no existe']);
            return;
        }

        // Slugify New Name
        // Slugify New Name (Use underscores as requested)
        $safeName = strtolower(trim(preg_replace('/[^A-Za-z0-9_]+/', '_', $newName)));
        $safeName = preg_replace('/_+/', '_', $safeName);
        
        // Get extension from old file
        $ext = pathinfo($oldName, PATHINFO_EXTENSION);
        if(!$ext) $ext = 'png'; // Fallback
        
        // Preserve prefix 'fondo_' consistency
        if (strpos($safeName, 'fondo_') !== 0) {
             $safeName = 'fondo_' . $safeName;
        }
        
        $newFilename = $safeName . '.' . $ext;
        $newPath = $dir . $newFilename;
        
        if (file_exists($newPath) && $newFilename !== $oldName) {
             echo json_encode(['success' => false, 'message' => 'Ya existe un archivo con ese nombre']);
             return;
        }

        if (rename($oldPath, $newPath)) {
            // Update Database References
             $sql = "UPDATE pdf_templates SET fondo_img = :new WHERE fondo_img = :old";
             $this->pdfTemplateModel->query($sql, [':new' => $newFilename, ':old' => $oldName]);

            echo json_encode([
                'success' => true,
                'new_name' => $newFilename,
                'new_url' => url('/pdf-templates/image/' . $newFilename),
                'display_name' => ucwords(strtolower($newName))
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al renombrar el archivo']);
        }
    }

    /**
     * Guardar snapshot del tema actual
     */
    public function saveSnapshot($id) {
        $template = $this->pdfTemplateModel->getById($id);
        if (!$template) {
            $this->redirect('/pdf-templates', ['error' => 'Tema no encontrado']);
            return;
        }

        try {
            // Crear snapshot JSON con la configuración actual
            $snapshotData = [
                'version' => '1.0',
                'saved_at' => date('Y-m-d H:i:s'),
                'id' => $template['id'],
                'nombre' => $template['nombre'],
                'color_brand' => $template['color_brand'],
                'icon' => $template['icon'],
                'header_php' => $template['header_php'],
                'footer_img' => $template['footer_img'],
                'fondo_img' => $template['fondo_img'],
                'title_font' => $template['title_font'],
                'title_size' => $template['title_size'],
                'title_bold' => $template['title_bold'],
                'title_color' => $template['title_color'],
                'activo' => $template['activo'],
                'es_default' => $template['es_default']
            ];

            // Guardar JSON del snapshot
            $snapshotDir = __DIR__ . '/../Views/pdf/snapshots/';
            $snapshotFile = $snapshotDir . 'tema_' . $id . '_snapshot.json';
            file_put_contents($snapshotFile, json_encode($snapshotData, JSON_PRETTY_PRINT));

            // Copiar archivos actuales a carpetas de snapshot
            $publicDir = __DIR__ . '/../../public/';

            // Copiar header
            if (!empty($template['header_php'])) {
                $headerSource = __DIR__ . '/../Views/pdf/master/partials/' . $template['header_php'];
                $headerDest = $snapshotDir . 'headers/' . $template['header_php'];
                if (file_exists($headerSource)) {
                    @copy($headerSource, $headerDest);
                }
            }

            // Copiar footer
            if (!empty($template['footer_img'])) {
                $footerSource = $publicDir . 'assets/img/footer/' . $template['footer_img'];
                $footerDest = $snapshotDir . 'footers/' . $template['footer_img'];
                if (file_exists($footerSource)) {
                    @copy($footerSource, $footerDest);
                }
            }

            // Copiar fondo
            if (!empty($template['fondo_img'])) {
                $fondoSource = __DIR__ . '/../Views/pdf/fondo/' . $template['fondo_img'];
                $fondoDest = $snapshotDir . 'fondos/' . $template['fondo_img'];
                if (file_exists($fondoSource)) {
                    @copy($fondoSource, $fondoDest);
                }
            }

            $this->redirect('/pdf-templates/edit/' . $id, ['msg' => 'snapshot_saved']);

        } catch (\Exception $e) {
            $this->redirect('/pdf-templates/edit/' . $id, ['error' => 'Error al guardar snapshot: ' . $e->getMessage()]);
        }
    }
    // AJAX: Renombrar Footer
    public function renameFooter() {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        $oldName = isset($data['old_name']) ? basename($data['old_name']) : '';
        $newName = isset($data['new_name']) ? trim($data['new_name']) : '';
        
        if (empty($oldName) || empty($newName)) {
            echo json_encode(['success' => false, 'message' => 'Nombres inválidos']);
            return;
        }

        $dir = __DIR__ . '/../Views/pdf/footer/';
        $oldPath = $dir . $oldName;
        
        if (!file_exists($oldPath)) {
            echo json_encode(['success' => false, 'message' => 'El archivo original no existe']);
            return;
        }

        // Slugify New Name
        $safeName = strtolower(trim(preg_replace('/[^A-Za-z0-9_]+/', '_', $newName)));
        $safeName = preg_replace('/_+/', '_', $safeName);
        
        // Get extension
        $ext = pathinfo($oldName, PATHINFO_EXTENSION);
        if(!$ext) $ext = 'png'; 
        
        // Preserve prefix 'footer_' consistency
        if (strpos($safeName, 'footer_') !== 0) {
             $safeName = 'footer_' . $safeName;
        }
        
        $newFilename = $safeName . '.' . $ext;
        $newPath = $dir . $newFilename;
        
        if (file_exists($newPath) && $newFilename !== $oldName) {
             echo json_encode(['success' => false, 'message' => 'Ya existe un archivo con ese nombre']);
             return;
        }

        if (rename($oldPath, $newPath)) {
            // Update Database References
             $sql = "UPDATE pdf_templates SET footer_img = :new WHERE footer_img = :old";
             $this->pdfTemplateModel->query($sql, [':new' => $newFilename, ':old' => $oldName]);

            echo json_encode([
                'success' => true,
                'new_name' => $newFilename,
                'new_url' => url('/pdf-templates/footer-image/' . $newFilename),
                'display_name' => ucwords(strtolower($newName))
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al renombrar el archivo']);
        }
    }


}
