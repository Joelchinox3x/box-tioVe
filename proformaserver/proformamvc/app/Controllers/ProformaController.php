<?php
// app/Controllers/ProformaController.php

namespace App\Controllers;

use Core\Controller;
use App\Models\Proforma;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\PdfTemplate;
use App\Services\PdfService;

class ProformaController extends Controller {
    private $proformaModel;
    private $clienteModel;
    private $productoModel;
    private $pdfService;

    public function __construct() {
        $this->proformaModel = new Proforma();
        $this->clienteModel = new Cliente();
        $this->productoModel = new Producto();
        $this->pdfService = new PdfService();
    }

    // Listar todas las proformas
    public function index() {
        $proformas = $this->proformaModel->getAllWithCliente();
        $total = $this->proformaModel->getTotalProformas();

        $this->view('proformas/index', [
            'proformas' => $proformas,
            'total_proformas' => $total,
            'mensaje' => $this->get('msg')
        ]);
    }

    // Mostrar formulario de creación
    public function create() {
        $clientes = $this->clienteModel->getAllOrdered();
        $productos = $this->productoModel->getAllOrdered();

        // Cargar templates de PDF activos
        $pdfTemplateModel = new PdfTemplate();
        $templates = $pdfTemplateModel->getAllActive();

        // Cargar modelo de folletos y obtener los activos
        $folletoModel = new \App\Models\ProductoFolleto();
        $folletos = $folletoModel->query("SELECT * FROM producto_folletos WHERE activo = 1 ORDER BY orden ASC, fecha_creacion DESC");
        
        // Agrupar folletos por producto_id para fácil acceso en JS
        $folletosPorProducto = [];
        foreach ($folletos as $f) {
            $pid = $f['producto_id'];
            if (!isset($folletosPorProducto[$pid])) {
                $folletosPorProducto[$pid] = [];
            }
            $folletosPorProducto[$pid][] = $f;
        }

        // Obtener cliente_id si viene desde el botón "Cotizar"
        $clienteIdPreseleccionado = $this->get('cliente_id');

        // Obtener producto_id si viene desde el botón "Cotizar" del inventario
        $productoIdPreseleccionado = $this->get('producto_id');
        $productoPreseleccionado = null;

        if ($productoIdPreseleccionado) {
            $productoPreseleccionado = $this->productoModel->find($productoIdPreseleccionado);
        }

        $this->view('proformas/create', [
            'clientes' => $clientes,
            'productos' => $productos,
            'folletos_json' => json_encode($folletosPorProducto), // Pasamos como JSON
            'cliente_id_preseleccionado' => $clienteIdPreseleccionado,
            'producto_preseleccionado' => $productoPreseleccionado,
            'templates' => $templates
        ]);
    }

    // Guardar nueva proforma
    public function store() {
        try {
            // Validar datos básicos
            $errors = $this->validate($this->post(), [
                'cliente_id' => 'required',
                'total' => 'required|numeric'
            ]);

            if (!empty($errors)) {
                return $this->redirect('/proformas/create', [
                    'error' => 'Datos incompletos'
                ]);
            }

            // Preparar datos de la proforma
            $proformaData = [
                'cliente_id' => $this->post('cliente_id'),
                'correlativo' => $this->post('correlativo'),
                'vigencia_dias' => $this->post('vigencia_dias', 7),
                'moneda' => $this->post('moneda', 'PEN'),
                'subtotal' => $this->post('subtotal', 0),
                'descuento' => $this->post('descuento', 0),
                'igv' => $this->post('igv', 0),
                'total' => $this->post('total', 0),
                'template' => $this->post('template', 'orange'), // Guardar template elegido
                'observaciones' => $this->post('observaciones', ''),
                'condiciones' => $this->post('condiciones', ''),
                'condiciones' => $this->post('condiciones', ''),
                'template' => $this->post('template', 'orange'),
                'token' => bin2hex(random_bytes(16)) // Generar token único seguro
            ];

            // Preparar items
            $items = [];
            $cantidades = $this->post('cantidad', []);
            $descripciones = $this->post('descripcion', []);
            $preciosUnitarios = $this->post('precio_unitario', []);
            $subtotales = $this->post('item_subtotal', []);
            $productosIds = $this->post('producto_id', []);
            $incluirFicha = $this->post('incluir_ficha', []);
            $incluirFotos = $this->post('incluir_fotos', []);
            $incluirGaleria = $this->post('incluir_galeria', []);

            // Crear carpeta de uploads si no existe
            $uploadsDir = __DIR__ . '/../../public/uploads/proforma_items';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }

            // DEBUG: Guardar estructura de $_FILES
            //   file_put_contents(__DIR__ . '/../../public/debug_files.txt', print_r($_FILES, true));
            //  file_put_contents(__DIR__ . '/../../public/debug_post.txt', print_r($_POST, true));

            // Reorganizar $_FILES SIN reindexar - mantener índices originales
            $filesArray = [];
            if (isset($_FILES['items']) && isset($_FILES['items']['name'])) {
                foreach ($_FILES['items']['name'] as $formIndex => $fileData) {
                    if (isset($fileData['imagenes']) && is_array($fileData['imagenes'])) {
                        foreach ($fileData['imagenes'] as $imgIndex => $filename) {
                            if (!empty($filename)) {
                                $filesArray[$formIndex][] = [
                                    'name' => $filename,
                                    'tmp_name' => $_FILES['items']['tmp_name'][$formIndex]['imagenes'][$imgIndex] ?? '',
                                    'error' => $_FILES['items']['error'][$formIndex]['imagenes'][$imgIndex] ?? UPLOAD_ERR_NO_FILE,
                                    'size' => $_FILES['items']['size'][$formIndex]['imagenes'][$imgIndex] ?? 0
                                ];
                            }
                        }
                    }
                }
            }

            // Crear mapeo de POST index -> FORM index para items manuales
            // Los items manuales son los únicos que pueden tener archivos
            $manualItemsFormIndices = array_keys($filesArray);
            sort($manualItemsFormIndices);
            $manualItemCounter = 0;

            for ($i = 0; $i < count($descripciones); $i++) {
                if (!empty($descripciones[$i]) && !empty($cantidades[$i])) {
                    // Procesar imágenes manuales si existen
                    $imagenesManuales = [];

                    // Solo items manuales (sin producto_id) pueden tener imágenes subidas
                    $esItemManual = empty($productosIds[$i]) || $productosIds[$i] == 0;

                    if ($esItemManual && isset($manualItemsFormIndices[$manualItemCounter])) {
                        $formIndex = $manualItemsFormIndices[$manualItemCounter];

                        if (isset($filesArray[$formIndex]) && is_array($filesArray[$formIndex])) {
                            foreach ($filesArray[$formIndex] as $j => $fileInfo) {
                                if ($fileInfo['error'] === UPLOAD_ERR_OK && !empty($fileInfo['tmp_name'])) {
                                    $extension = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);

                                    // Generar nombre único
                                    $newFilename = 'item_' . time() . '_' . uniqid() . '.' . $extension;
                                    $destPath = $uploadsDir . '/' . $newFilename;

                                    if (move_uploaded_file($fileInfo['tmp_name'], $destPath)) {
                                        // OPTIMIZAR IMAGEN
                                        $this->optimizeImage($destPath);
                                        $imagenesManuales[] = 'uploads/proforma_items/' . $newFilename;
                                    }
                                }
                            }
                        }

                        $manualItemCounter++;
                    }

                    $items[] = [
                        'producto_id' => !empty($productosIds[$i]) ? $productosIds[$i] : null,
                        'descripcion' => $descripciones[$i],
                        'cantidad' => $cantidades[$i],
                        'precio_unitario' => $preciosUnitarios[$i],
                        'subtotal' => $subtotales[$i],
                        'incluir_ficha' => isset($incluirFicha[$i]) ? 1 : 0,
                        'incluir_fotos' => isset($incluirFotos[$i]) ? 1 : 0,
                        'incluir_galeria' => isset($incluirGaleria[$i]) ? 1 : 0,
                        'imagenes_manuales' => !empty($imagenesManuales) ? json_encode($imagenesManuales) : null
                    ];
                }
            }

            if (empty($items)) {
                return $this->redirect('/proformas/create', [
                    'error' => 'Debe agregar al menos un item a la proforma'
                ]);
            }

            // Guardar
            $proformaId = $this->proformaModel->createProforma($proformaData, $items);

            $this->redirect('/proformas', ['msg' => 'created']);

        } catch (\Exception $e) {
            $this->redirect('/proformas/create', [
                'error' => $e->getMessage()
            ]);
        }
    }

    // Mostrar detalle de proforma
    public function show($id) {
        $proforma = $this->proformaModel->getWithDetails($id);

        if (!$proforma) {
            $this->redirect('/proformas', ['msg' => 'not_found']);
        }

        $this->view('proformas/show', [
            'proforma' => $proforma
        ]);
    }

    // Generar PDF de proforma
    public function pdf($id) {
        try {
            $proforma = $this->proformaModel->getWithDetails($id);

            if (!$proforma) {
                $this->redirect('/proformas', ['msg' => 'not_found']);
                return;
            }

            // Generar PDF usando mPDF (usa el template guardado en la proforma)
            $pdfPath = $this->pdfService->generateProformaPdf($proforma);

            // Actualizar path en BD
            $this->proformaModel->update($id, ['pdf_path' => $pdfPath]);

            // Abrir PDF en el navegador
            $this->pdfService->view($pdfPath);

        } catch (\Exception $e) {
            $this->redirect('/proformas', ['msg' => 'error', 'error' => $e->getMessage()]);
        }
    }

    // Ver PDF en navegador
    public function viewPdf($id) {
        try {
            $proforma = $this->proformaModel->getWithDetails($id);

            if (!$proforma) {
                $this->redirect('/proformas', ['msg' => 'not_found']);
                return;
            }

            // Si ya existe PDF, mostrarlo
            if (!empty($proforma['pdf_path']) && file_exists(__DIR__ . '/../../public/' . $proforma['pdf_path'])) {
                $this->pdfService->view($proforma['pdf_path']);
            } else {
                // Generar nuevo PDF (usa el template guardado en la proforma)
                $pdfPath = $this->pdfService->generateProformaPdf($proforma);
                $this->proformaModel->update($id, ['pdf_path' => $pdfPath]);
                $this->pdfService->view($pdfPath);
            }

        } catch (\Exception $e) {
            $this->redirect('/proformas', ['msg' => 'error', 'error' => $e->getMessage()]);
        }
    }

    // Vista PÚBLICA por token (Sin Login)
    public function publicView($token) {
        try {
            $proforma = $this->proformaModel->getByToken($token);

            if (!$proforma) {
                // Mostrar 404 simple si token es inválido
                http_response_code(404);
                echo "Proforma no encontrada o enlace expirado.";
                return;
            }

            // Si ya existe PDF, mostrarlo
            if (!empty($proforma['pdf_path']) && file_exists(__DIR__ . '/../../public/' . $proforma['pdf_path'])) {
                $this->pdfService->view($proforma['pdf_path']);
            } else {
                // Generar si no existe
                $pdfPath = $this->pdfService->generateProformaPdf($proforma);
                $this->proformaModel->update($proforma['id'], ['pdf_path' => $pdfPath]);
                $this->pdfService->view($pdfPath);
            }

        } catch (\Exception $e) {
            http_response_code(500);
            echo "Error al generar el documento.";
        }
    }

    // Descarga PÚBLICA por token
    public function publicDownload($token) {
        try {
            $proforma = $this->proformaModel->getByToken($token);

            if (!$proforma) {
                http_response_code(404);
                echo "Proforma no encontrada.";
                return;
            }

            // Generar o recuperar path
            if (!empty($proforma['pdf_path']) && file_exists(__DIR__ . '/../../public/' . $proforma['pdf_path'])) {
                $pdfPath = $proforma['pdf_path'];
            } else {
                $pdfPath = $this->pdfService->generateProformaPdf($proforma);
                $this->proformaModel->update($proforma['id'], ['pdf_path' => $pdfPath]);
            }

            // Nombre del archivo
            $proformaId = str_pad($proforma['id'], 5, '0', STR_PAD_LEFT);
            $clienteNombre = preg_replace('/[^a-zA-Z0-9_-]/', '', $proforma['cliente_nombre'] ?? 'Cliente');
            $filename = "Proforma_TRA{$proformaId}_{$clienteNombre}.pdf";

            $this->pdfService->download($pdfPath, $filename);

        } catch (\Exception $e) {
            http_response_code(500);
            echo "Error al descargar.";
        }
    }

    // Descargar PDF de la proforma
    public function downloadPdf($id) {
        try {
            // Obtener la proforma con detalles
            $proforma = $this->proformaModel->getWithDetails($id);

            if (!$proforma) {
                $this->redirect('/proformas', ['msg' => 'not_found']);
                return;
            }

            // Si ya existe PDF, descargarlo
            if (!empty($proforma['pdf_path']) && file_exists(__DIR__ . '/../../public/' . $proforma['pdf_path'])) {
                $pdfPath = $proforma['pdf_path'];
            } else {
                // Generar nuevo PDF (usa el template guardado en la proforma)
                $pdfPath = $this->pdfService->generateProformaPdf($proforma);
                $this->proformaModel->update($id, ['pdf_path' => $pdfPath]);
            }

            // Nombre del archivo para descarga
            $proformaId = str_pad($id, 5, '0', STR_PAD_LEFT);
            $clienteNombre = preg_replace('/[^a-zA-Z0-9_-]/', '', $proforma['cliente_nombre'] ?? 'Cliente');
            $filename = "Proforma_TRA{$proformaId}_{$clienteNombre}.pdf";

            // Descargar el PDF
            $this->pdfService->download($pdfPath, $filename);

        } catch (\Exception $e) {
            $this->redirect('/proformas', ['msg' => 'error', 'error' => $e->getMessage()]);
        }
    }

    // Formulario de edición
    public function edit($id) {
        $proforma = $this->proformaModel->getWithDetails($id);

        if (!$proforma) {
            $this->redirect('/proformas', ['msg' => 'not_found']);
        }

        $clientes = $this->clienteModel->getAllOrdered();
        $productos = $this->productoModel->getAllOrdered();

        // Cargar templates de PDF activos
        $pdfTemplateModel = new \App\Models\PdfTemplate();
        $templates = $pdfTemplateModel->getAllActive();

        $this->view('proformas/edit', [
            'proforma' => $proforma,
            'clientes' => $clientes,
            'productos' => $productos,
            'templates' => $templates
        ]);
    }

    // Actualizar proforma
    public function update($id) {
        try {
            $proforma = $this->proformaModel->find($id);

            if (!$proforma) {
                return $this->redirect('/proformas', ['msg' => 'not_found']);
            }

            // Preparar datos de la proforma
            $proformaData = [
                'cliente_id' => $this->post('cliente_id'),
                'vigencia_dias' => $this->post('vigencia_dias', 7),
                'moneda' => $this->post('moneda', 'PEN'),
                'subtotal' => $this->post('subtotal', 0),
                'descuento' => $this->post('descuento', 0),
                'igv' => $this->post('igv', 0),
                'total' => $this->post('total', 0),
                'template' => $this->post('template', 'orange'), // Guardar cambio de template
                'observaciones' => $this->post('observaciones', ''),
                'condiciones' => $this->post('condiciones', '')
            ];

            // Preparar items
            $items = [];
            $cantidades = $this->post('cantidad', []);
            $descripciones = $this->post('descripcion', []);
            $preciosUnitarios = $this->post('precio_unitario', []);
            $subtotales = $this->post('item_subtotal', []);
            $productosIds = $this->post('producto_id', []);

            for ($i = 0; $i < count($descripciones); $i++) {
                if (!empty($descripciones[$i]) && !empty($cantidades[$i])) {
                    $items[] = [
                        'producto_id' => !empty($productosIds[$i]) ? $productosIds[$i] : null,
                        'descripcion' => $descripciones[$i],
                        'cantidad' => $cantidades[$i],
                        'precio_unitario' => $preciosUnitarios[$i],
                        'subtotal' => $subtotales[$i]
                    ];
                }
            }

            // Eliminar items antiguos
            $this->proformaModel->execute(
                "DELETE FROM proforma_items WHERE proforma_id = ?",
                [$id]
            );

            // Actualizar proforma
            $this->proformaModel->update($id, $proformaData);

            // Insertar nuevos items
            foreach ($items as $index => $item) {
                $this->proformaModel->execute(
                    "INSERT INTO proforma_items (proforma_id, producto_id, descripcion, cantidad, precio_unitario, subtotal, orden)
                     VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [
                        $id,
                        $item['producto_id'],
                        $item['descripcion'],
                        $item['cantidad'],
                        $item['precio_unitario'],
                        $item['subtotal'],
                        $index
                    ]
                );
            }

            $this->redirect('/proformas', ['msg' => 'updated']);

        } catch (\Exception $e) {
            $this->redirect("/proformas/edit/{$id}", [
                'error' => $e->getMessage()
            ]);
        }
    }

    // Eliminar proforma
    public function delete($id) {
        try {
            // Obtener proforma antes de eliminar para borrar el PDF
            $proforma = $this->proformaModel->find($id);

            if (!$proforma) {
                $this->redirect('/proformas', ['msg' => 'not_found']);
                return;
            }

            // Eliminar PDF si existe
            if (!empty($proforma['pdf_path'])) {
                $pdfFullPath = __DIR__ . '/../../public/' . $proforma['pdf_path'];
                if (file_exists($pdfFullPath)) {
                    unlink($pdfFullPath);
                }
            }

            // Eliminar proforma de BD
            $result = $this->proformaModel->deleteProforma($id);

            if ($result['success']) {
                $this->redirect('/proformas', ['msg' => 'deleted']);
            } else {
                $this->redirect('/proformas', ['msg' => 'error']);
            }
        } catch (\Exception $e) {
            $this->redirect('/proformas', ['msg' => 'error']);
        }
    }

    // Buscar proformas (AJAX)
    public function search() {
        $term = $this->get('q', '');
        $proformas = $this->proformaModel->search($term);

        return $this->json($proformas);
    }

    // Obtener productos (AJAX) - para autocompletar
    public function getProductos() {
        $term = $this->get('q', '');

        if (empty($term)) {
            $productos = $this->productoModel->getAllOrdered();
        } else {
            $productos = $this->productoModel->search($term);
        }

        return $this->json($productos);
    }

    // Obtener clientes (AJAX) - para autocompletar
    public function getClientes() {
        $term = $this->get('q', '');

        if (empty($term)) {
            $clientes = $this->clienteModel->getAllOrdered();
        } else {
            $clientes = $this->clienteModel->search($term);
        }

        return $this->json($clientes);
    }
    // Optimizar imagen (Reducir tamaño y peso)
    private function optimizeImage($path) {
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

        // Corregir orientación básica (EXIF) para JPEGs
        if ($mime == 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $image = imagerotate($image, 180, 0);
                        break;
                    case 6:
                        $image = imagerotate($image, -90, 0);
                        break;
                    case 8:
                        $image = imagerotate($image, 90, 0);
                        break;
                }
                // Actualizar dimensiones después de rotar
                $width = imagesx($image);
                $height = imagesy($image);
            }
        }

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

        // Preservar transparencia para PNG/WebP (aunque convertiremos a JPG, es buena práctica inicial)
        if ($mime == 'image/png' || $mime == 'image/webp') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        // Redimensionar
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Guardar como JPG COMPRIMIDO (Calidad 75) - Sobrescribe el original
        // Siempre convertimos a JPG para asegurar compatibilidad con mPDF y bajo peso
        imagejpeg($newImage, $path, 75);

        // Liberar memoria
        imagedestroy($image);
        imagedestroy($newImage);
    }
}