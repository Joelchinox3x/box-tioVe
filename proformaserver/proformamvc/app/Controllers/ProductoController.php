<?php
// app/Controllers/ProductoController.php

namespace App\Controllers;

use Core\Controller;
use App\Models\Producto;
use App\Models\ProductoFolleto;
use App\Services\ImageService;
use App\Services\FolletoService;
use App\Services\TransferService;

class ProductoController extends Controller {
    private $productoModel;
    private $folletoModel;
    private $imageService;
    private $folletoService;
    private $transferService;

    public function __construct() {
        $this->productoModel = new Producto();
        $this->folletoModel = new ProductoFolleto();
        $this->imageService = new ImageService();
        $this->folletoService = new FolletoService();
        $this->transferService = new TransferService();
    }
    
    // Listar inventario
    public function index() {
        $productos = $this->productoModel->getAllOrdered();
        $total = $this->productoModel->getTotalProductos();
        
        $this->view('productos/index', [
            'productos' => $productos,
            'total_equipos' => $total,
            'mensaje' => $this->get('msg')
        ]);
    }
    
    // Mostrar formulario de creación
    public function create() {
        $this->view('productos/create');
    }
    
    // Guardar nuevo producto
    public function store() {
        try {
            // Validar datos básicos
            $errors = $this->validate($this->post(), [
                'nombre' => 'required|min:3',
                'precio' => 'required|numeric'
            ]);
            
            if (!empty($errors)) {
                return $this->redirect('/inventario/create', [
                    'error' => 'Datos incompletos'
                ]);
            }
            
            // Procesar imágenes
            $imagenes = [];
            if (isset($_FILES['imagenes_nuevas']) && !empty($_FILES['imagenes_nuevas']['name'][0])) {
                $imagenes = $this->imageService->saveMultipleFiles($_FILES['imagenes_nuevas'], 'equipos');
            }

            // Preparar datos del producto
            $productoData = [
                'nombre' => $this->post('nombre'),
                'modelo' => $this->post('modelo'),
                'sku' => $this->post('sku'),
                'precio' => $this->post('precio'),
                'moneda' => $this->post('moneda', 'PEN'),
                'descripcion' => $this->post('descripcion'),
                'layout_specs' => $this->post('layout_specs', '2col'), // Nuevo campo
                'imagenes' => $imagenes
            ];
            
            // Preparar specs
            $specs = [];
            if ($this->post('specs')) {
                foreach ($this->post('specs') as $spec) {
                    if (!empty($spec['attr']) && !empty($spec['val'])) {
                        $specs[] = [
                            'atributo' => $spec['attr'],
                            'valor' => $spec['val']
                        ];
                    }
                }
            }
            
            // Guardar
            $productoId = $this->productoModel->createProducto($productoData, $specs);
            
            $this->redirect('/inventario', ['msg' => 'created']);
            
        } catch (\Exception $e) {
            $this->redirect('/inventario/create', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // Mostrar detalle de producto
    public function show($id) {
        $producto = $this->productoModel->getWithSpecs($id);
        
        if (!$producto) {
            $this->redirect('/inventario', ['msg' => 'not_found']);
        }
        
        // Obtener folletos asociados (para mostrar en detalle y export)
        $folletos = $this->folletoModel->getByProducto($id, false);

        // Calcular datos para el modal de exportación (Prevenir errores en la vista)
        $skuForFilename = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($producto['sku'] ?? 'UNKNOWN'));
        
        $imagenesList = $producto['imagenes'] ?? [];
        if (is_string($imagenesList)) {
            $imagenesList = json_decode($imagenesList, true);
        }
        if (!is_array($imagenesList)) $imagenesList = [];
        
        $folletosList = is_array($folletos) ? $folletos : [];
        
        $countImagenes = count($imagenesList);
        $countFolletos = count($folletosList);
        
        // Calcular peso REAL (sumando filesize de cada archivo)
        $totalSizeBytes = 0;
        $publicDir = __DIR__ . '/../../public/';

        // 1. Peso estimado del JSON (despreciable pero sumamos 2KB)
        $totalSizeBytes += 2048; 

        // 2. Peso de Imágenes de Producto
        foreach ($imagenesList as $imgRel) {
            $path = $publicDir . $imgRel;
            if (file_exists($path)) {
                $totalSizeBytes += filesize($path);
            }
        }

        // 3. Peso de Folletos (PDFs + Imágenes Fuente)
        foreach ($folletosList as $foll) {
            // PDF Principal
            if (!empty($foll['ruta_pdf'])) {
                $pathPdf = $publicDir . $foll['ruta_pdf'];
                if (file_exists($pathPdf)) {
                    $totalSizeBytes += filesize($pathPdf);
                }
            }
            
            // Imágenes fuente del folleto (si existen)
            $srcImgs = $foll['imagenes_fuente'] ?? [];
            if (is_string($srcImgs)) $srcImgs = json_decode($srcImgs, true) ?? [];
            if (is_array($srcImgs)) {
                foreach ($srcImgs as $srcImg) {
                    $pathSrc = $publicDir . $srcImg;
                    if (file_exists($pathSrc)) {
                        $totalSizeBytes += filesize($pathSrc);
                    }
                }
            }
        }

        $totalSizeMB = round($totalSizeBytes / 1048576, 2); // Bytes -> MB

        $exportData = [
            'filename' => 'producto_SKU-' . $skuForFilename . '.zip',
            'countImagenes' => $countImagenes,
            'countFolletos' => $countFolletos,
            'totalSizeMB' => $totalSizeMB
        ];

        $this->view('productos/show', [
            'producto' => $producto,
            'folletos' => $folletos,
            'exportData' => $exportData
        ]);
    }
    
    // Formulario de edición
    public function edit($id) {
        $producto = $this->productoModel->getWithSpecs($id);

        if (!$producto) {
            $this->redirect('/inventario', ['msg' => 'not_found']);
        }

        // Obtener folletos del producto
        $folletos = $this->folletoModel->getByProducto($id, false); // false = incluir inactivos

        $this->view('productos/edit', [
            'producto' => $producto,
            'folletos' => $folletos
        ]);
    }
    
    // Actualizar producto
    public function update($id) {
        try {
            $producto = $this->productoModel->find($id);

            if (!$producto) {
                return $this->redirect('/inventario', ['msg' => 'not_found']);
            }

            // Verificar si está intentando desbloquear un producto protegido
            $protegidoActual = (bool)($producto['protegido'] ?? false);
            $protegidoNuevo = $this->post('protegido') ? true : false;

            // Si está desbloqueando (estaba protegido y ahora no)
            if ($protegidoActual && !$protegidoNuevo) {
                $adminPin = $this->post('admin_pin');

                // Obtener PIN desde la base de datos
                require_once __DIR__ . '/../Helpers/SettingsHelper.php';
                $correctPin = \App\Helpers\SettingsHelper::getPinCode();

                // Validar PIN
                if ($adminPin !== $correctPin) {
                    $this->redirect("/inventario/edit/{$id}", ['error' => 'PIN incorrecto']);
                    return;
                }
            }

            // Procesar imágenes
            $imagenesActuales = json_decode($producto['imagenes'] ?? '', true) ?? [];
            $imagenesAnteriores = json_decode($producto['imagenes'] ?? '', true) ?? [];

            // Mantener imágenes viejas que no se eliminaron
            $imagenesViejas = $this->post('imagenes_viejas', []);

            // Agregar nuevas imágenes
            $imagenesNuevas = [];
            if (isset($_FILES['imagenes_nuevas']) && !empty($_FILES['imagenes_nuevas']['name'][0])) {
                $imagenesNuevas = $this->imageService->saveMultipleFiles(
                    $_FILES['imagenes_nuevas'],
                    'equipos'
                );
            }

            // Combinar
            $imagenes = array_merge($imagenesViejas, $imagenesNuevas);
            
            // Preparar datos
            $productoData = [
                'nombre' => $this->post('nombre'),
                'modelo' => $this->post('modelo'),
                'sku' => $this->post('sku'),
                'precio' => $this->post('precio'),
                'moneda' => $this->post('moneda', 'PEN'),
                'descripcion' => $this->post('descripcion'),
                'layout_specs' => $this->post('layout_specs', '2col'),
                'imagenes' => $imagenes,
                'protegido' => $protegidoNuevo ? 1 : 0
            ];
            
            // Preparar specs
            $specs = [];
            $atributos = $this->post('spec_atributo', []);
            $valores = $this->post('spec_valor', []);
            
            for ($i = 0; $i < count($atributos); $i++) {
                if (!empty($atributos[$i]) && !empty($valores[$i])) {
                    $specs[] = [
                        'atributo' => $atributos[$i],
                        'valor' => $valores[$i]
                    ];
                }
            }
            
            // Actualizar
            $this->productoModel->updateProducto($id, $productoData, $specs);

            $this->redirect('/inventario', ['msg' => 'updated']);

        } catch (\Exception $e) {
            $this->redirect("/inventario/edit/{$id}", [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // Eliminar producto
    public function delete($id) {
        $result = $this->productoModel->deleteProducto($id);
        
        if ($result['success']) {
            $this->redirect('/inventario', ['msg' => 'deleted']);
        } else {
            $this->redirect('/inventario', ['msg' => 'locked']);
        }
    }

    // Exportar Producto (Descargar Paquete ZIP)
    public function export($id) {
        try {
            $zipFile = $this->transferService->exportProduct($id);
            $producto = $this->productoModel->find($id);
            $sku = preg_replace('/[^a-zA-Z0-9]/', '', strtoupper($producto['sku'] ?? 'UNKNOWN'));
            $filename = 'producto_SKU-' . $sku . '.zip';

            if (file_exists($zipFile)) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($zipFile));
                header('Pragma: no-cache');
                readfile($zipFile);
                unlink($zipFile); // Borrar temporal después de enviar
                exit;
            } else {
                $this->redirect('/inventario', ['error' => 'Error, archivo no generado']);
            }

        } catch (\Exception $e) {
            $this->redirect('/inventario', ['error' => 'Error al exportar: ' . $e->getMessage()]);
        }
    }

    // Importar Producto (Subir Paquete ZIP)
    public function import() {
        try {
            if (!isset($_FILES['package']) || $_FILES['package']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('No se seleccionó ningún archivo o hubo un error en la subida.');
            }

            $zipPath = $_FILES['package']['tmp_name'];
            $newId = $this->transferService->importProduct($zipPath);

            $this->redirect("/inventario", ['msg' => 'imported']);

        } catch (\Exception $e) {
            $this->redirect('/inventario', ['error' => 'Error al importar: ' . $e->getMessage()]);
        }
    }
    
    // Bloquear/Desbloquear producto (AJAX)
    public function toggleLock() {
        $id = $this->post('id');
        $status = $this->post('status');

        try {
            $this->productoModel->update($id, ['protegido' => $status]);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
    
    // Buscar productos (AJAX)
    public function search() {
        $term = $this->get('q', '');
        $productos = $this->productoModel->search($term);

        return $this->json($productos);
    }

    // Verificar PIN de seguridad (AJAX)
    public function verificarPin() {
        // Establecer header JSON
        header('Content-Type: application/json');

        // Obtener el JSON del body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $pin = $data['pin'] ?? '';

        // Obtener PIN desde la base de datos
        require_once __DIR__ . '/../Helpers/SettingsHelper.php';
        $correctPin = \App\Helpers\SettingsHelper::getPinCode();

        $valid = ($pin === $correctPin);

        // Devolver respuesta JSON
        echo json_encode([
            'valid' => $valid
        ]);
        exit;
    }

    // Agregar fotos rápidas (AJAX)
    public function addPhotos($id) {
        try {
            $producto = $this->productoModel->find($id);
            if (!$producto) {
                return $this->json(['success' => false, 'error' => 'Producto no encontrado'], 404);
            }

            $imagenesActuales = json_decode($producto['imagenes'] ?? '[]', true) ?? [];
            
            if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
                $nuevasImagenes = $this->imageService->saveMultipleFiles($_FILES['imagenes'], 'equipos');
                $todasLasImagenes = array_merge($imagenesActuales, $nuevasImagenes);
                
                // Actualizar DB
                $this->productoModel->update($id, [
                    'imagenes' => json_encode($todasLasImagenes),
                    'fecha_modificacion' => date('Y-m-d H:i:s')
                ]);
                
                return $this->json(['success' => true]);
            } else {
                return $this->json(['success' => false, 'error' => 'No se recibieron imágenes'], 400);
            }

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Vista Pública del Producto (Share)
    public function publicShow($token) {
        // Usamos getByToken para seguridad
        $producto = $this->productoModel->getByToken($token);
        
        if (!$producto) {
            http_response_code(404);
            $this->view('errors/404_public', [], false); // Sin layout
            exit;
        }

        // Obtener configuración global
        require_once __DIR__ . '/../Helpers/SettingsHelper.php';
        $appLogo = \App\Helpers\SettingsHelper::getAppLogo() ?: 'assets/img/logo.png';
        $appName = \App\Helpers\SettingsHelper::getAppName() ?: 'Tradimacova';

        // Obtener productos relacionados para el carrusel (Random 10 excluyendo el actual)
        $todos = $this->productoModel->getAllOrdered('RAND()');
        $otrosProductos = array_filter($todos, function($p) use ($producto) {
            return $p['id'] != $producto['id'];
        });
        $otrosProductos = array_slice($otrosProductos, 0, 10);

        // DETECCIÓN DE DISPOSITIVO (Simple UA Check)
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        $isMobile = (bool)preg_match('/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini)/i', $userAgent);
        
        // Cargar vista dedicada según dispositivo
        // show_mobile.php -> Vista intocable para celular
        // show_desktop.php -> Vista optimizada para PC/Tablet
        $viewName = $isMobile ? 'public/show_mobile' : 'public/show_desktop';

        $this->view($viewName, [
            'producto' => $producto,
            'otrosProductos' => $otrosProductos,
            'appLogo' => $appLogo,
            'appName' => $appName
        ], false); // Sin layout
    }

    // Catálogo Público (Modo Vitrina)
    public function publicCatalog() {
        // Obtener configuración global
        require_once __DIR__ . '/../Helpers/SettingsHelper.php';
        $appName = \App\Helpers\SettingsHelper::getAppName() ?: 'Tradimacova';
        $appLogo = \App\Helpers\SettingsHelper::getAppLogo() ?: 'assets/img/logo.png';
        
        // Obtener TODOS los productos (ordenados)
        $productos = $this->productoModel->getAllOrdered();
        
        // Renderizar vista pública de catálogo
        $this->view('public/catalog', [
            'productos' => $productos,
            'appName' => $appName,
            'appLogo' => $appLogo
        ], false);
    }

    // ============================================
    // GESTIÓN DE FOLLETOS
    // ============================================

    /**
     * Crear un nuevo folleto
     */
    public function crearFolleto() {
        header('Content-Type: application/json');

        try {
            $productoId = $this->post('producto_id');
            $nombre = $this->post('nombre');
            $categoria = $this->post('categoria', 'general');
            $tipo = $this->post('tipo', 'subido');

            // Validar producto
            $producto = $this->productoModel->find($productoId);
            if (!$producto) {
                echo json_encode(['success' => false, 'error' => 'Producto no encontrado']);
                exit;
            }

            if ($tipo === 'subido') {
                // Subir PDF directo
                if (!isset($_FILES['pdf'])) {
                    echo json_encode(['success' => false, 'error' => 'No se recibió el PDF']);
                    exit;
                }

                $resultado = $this->folletoService->subirPdf($_FILES['pdf']);

                if (!$resultado['success']) {
                    echo json_encode($resultado);
                    exit;
                }

                // Guardar en BD
                $folletoId = $this->folletoModel->createFolleto([
                    'producto_id' => $productoId,
                    'nombre' => $nombre,
                    'tipo' => 'subido',
                    'categoria' => $categoria,
                    'ruta_pdf' => $resultado['ruta'],
                    'tamanio' => $resultado['tamanio']
                ]);

                echo json_encode(['success' => true, 'id' => $folletoId]);

            } else {
                // Generar PDF desde fotos
                if (!isset($_FILES['fotos'])) {
                    echo json_encode(['success' => false, 'error' => 'No se recibieron fotos']);
                    exit;
                }

                // Guardar fotos fuente
                $resultadoFotos = $this->folletoService->guardarImagenesFuente($_FILES['fotos']);

                if (!$resultadoFotos['success']) {
                    echo json_encode($resultadoFotos);
                    exit;
                }

                // Generar PDF
                $resultadoPdf = $this->folletoService->generarPdfDesdeImagenes(
                    $resultadoFotos['rutas'],
                    $producto['nombre']
                );

                if (!$resultadoPdf['success']) {
                    // Limpiar fotos si falla
                    foreach ($resultadoFotos['rutas'] as $ruta) {
                        $this->folletoService->eliminarFolleto($ruta, []);
                    }
                    echo json_encode($resultadoPdf);
                    exit;
                }

                // Guardar en BD
                $folletoId = $this->folletoModel->createFolleto([
                    'producto_id' => $productoId,
                    'nombre' => $nombre,
                    'tipo' => 'generado',
                    'categoria' => $categoria,
                    'ruta_pdf' => $resultadoPdf['ruta'],
                    'imagenes_fuente' => $resultadoFotos['rutas'],
                    'tamanio' => $resultadoPdf['tamanio']
                ]);

                echo json_encode(['success' => true, 'id' => $folletoId]);
            }

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Eliminar folleto
     */
    public function eliminarFolleto($id) {
        header('Content-Type: application/json');

        try {
            $folleto = $this->folletoModel->find($id);

            if (!$folleto) {
                echo json_encode(['success' => false, 'error' => 'Folleto no encontrado']);
                exit;
            }

            // Eliminar archivos físicos
            $imagenesFuente = json_decode($folleto['imagenes_fuente'], true) ?? [];
            $this->folletoService->eliminarFolleto($folleto['ruta_pdf'], $imagenesFuente);

            // Eliminar de BD
            $this->folletoModel->delete($id);

            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Activar/Desactivar folleto
     */
    public function toggleFolleto($id) {
        header('Content-Type: application/json');

        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $activo = $data['activo'] ?? 1;

            $this->folletoModel->toggleActivo($id, $activo);

            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Descargar folleto (incrementa contador)
     */
    public function descargarFolleto($id) {
        try {
            $folleto = $this->folletoModel->find($id);

            if (!$folleto) {
                http_response_code(404);
                echo 'Folleto no encontrado';
                exit;
            }

            // Incrementar contador
            $this->folletoModel->incrementarDescargas($id);

            // Descargar
            $fullPath = __DIR__ . '/../../public/' . $folleto['ruta_pdf'];

            if (!file_exists($fullPath)) {
                http_response_code(404);
                echo 'Archivo no encontrado';
                exit;
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($folleto['ruta_pdf']) . '"');
            header('Content-Length: ' . filesize($fullPath));
            readfile($fullPath);
            exit;

        } catch (\Exception $e) {
            http_response_code(500);
            echo 'Error: ' . $e->getMessage();
            exit;
        }
    }
}