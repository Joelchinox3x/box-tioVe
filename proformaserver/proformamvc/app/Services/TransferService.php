<?php
// app/Services/TransferService.php

namespace App\Services;

use ZipArchive;
use App\Models\Producto;
use App\Models\ProductoFolleto;
use App\Services\ImageService;

class TransferService {
    private $productoModel;
    private $folletoModel;
    private $imageService;

    public function __construct() {
        $this->productoModel = new Producto();
        $this->folletoModel = new ProductoFolleto();
        $this->imageService = new ImageService();
    }

    /**
     * Exportar producto a ZIP
     */
    public function exportProduct($id) {
        $producto = $this->productoModel->getWithSpecs($id);
        
        if (!$producto) {
            throw new \Exception("Producto no encontrado");
        }

        // Obtener folletos
        $folletos = $this->folletoModel->getByProducto($id, false);

        // Crear ZIP temporal
        $zipFile = tempnam(sys_get_temp_dir(), 'prod_') . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception("No se pudo crear el archivo temporal");
        }

        // 1. Agregar datos JSON (Producto + Folletos)
        $data = [
            'version' => '1.1', // Incrementamos version
            'exported_at' => date('Y-m-d H:i:s'),
            'producto' => $producto,
            'folletos' => $folletos
        ];
        
        // Limpiar datos sensibles
        unset($data['producto']['id']);
        unset($data['producto']['token']);
        unset($data['producto']['fecha_creacion']);
        unset($data['producto']['fecha_modificacion']);

        $zip->addFromString('data.json', json_encode($data, JSON_PRETTY_PRINT));

        $publicDir = __DIR__ . '/../../public/';

        // 2. Agregar imágenes de producto
        $imagenes = $producto['imagenes'] ?? [];
        if (is_string($imagenes)) {
             $imagenes = json_decode($imagenes, true) ?? [];
        }

        foreach ($imagenes as $imgRelPath) {
            $sourcePath = $publicDir . $imgRelPath;
            if (file_exists($sourcePath)) {
                $zip->addFile($sourcePath, 'images/' . basename($imgRelPath));
            }
        }

        // 3. Agregar PDFs y recursos de Folletos
        if (!empty($folletos)) {
            foreach ($folletos as $folleto) {
                // PDF Principal
                if (!empty($folleto['ruta_pdf'])) {
                    $pdfPath = $publicDir . $folleto['ruta_pdf'];
                    if (file_exists($pdfPath)) {
                        $zip->addFile($pdfPath, 'pdfs/' . basename($folleto['ruta_pdf']));
                    }
                }

                // Imágenes Fuente (para folletos generados)
                $imgsFuente = $folleto['imagenes_fuente'] ?? [];
                if (is_string($imgsFuente)) $imgsFuente = json_decode($imgsFuente, true) ?? [];

                foreach ($imgsFuente as $imgF) {
                    $imgPath = $publicDir . $imgF;
                    if (file_exists($imgPath)) {
                         $zip->addFile($imgPath, 'folletos_src/' . basename($imgF));
                    }
                }
            }
        }

        $zip->close();

        return $zipFile;
    }

    /**
     * Importar producto desde ZIP
     */
    public function importProduct($zipFilePath) {
        $zip = new ZipArchive();
        
        if ($zip->open($zipFilePath) !== TRUE) {
            throw new \Exception("No se puede abrir el archivo ZIP");
        }

        // 1. Leer data.json
        $jsonContent = $zip->getFromName('data.json');
        if (!$jsonContent) {
            $zip->close();
            throw new \Exception("Archivo inválido: falta data.json");
        }

        $data = json_decode($jsonContent, true);
        if (!$data || !isset($data['producto'])) {
            $zip->close();
            throw new \Exception("Estructura de datos inválida");
        }

        $productoData = $data['producto'];
        
        // --- PROCESAR IMAGENES PRODUCTO ---
        $nuevasImagenes = [];
        $uploadDir = __DIR__ . '/../../public/uploads/equipos/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            
            // Imágenes de producto (carpeta images/)
            if (strpos($filename, 'images/') === 0) {
                $baseName = basename($filename);
                if (empty($baseName)) continue;
                // Verificar si esta imagen pertenece al producto (comparando basename)
                // (Para simplificar, importamos todas las de la carpeta images/)
                
                $info = pathinfo($baseName);
                $ext = $info['extension'] ?? 'jpg';
                $newFileName = 'import_' . time() . '_' . substr(md5(uniqid()), 0, 8) . '.' . $ext;
                
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    file_put_contents($uploadDir . $newFileName, $content);
                    $nuevasImagenes[] = 'uploads/equipos/' . $newFileName;
                }
            }
        }

        // --- CREAR PRODUCTO ---
        $productoDbData = [
            'nombre' => $productoData['nombre'],
            'modelo' => $productoData['modelo'],
            'sku' => $productoData['sku'],
            'precio' => $productoData['precio'],
            'moneda' => $productoData['moneda'],
            'descripcion' => $productoData['descripcion'],
            'imagenes' => $nuevasImagenes,
            'protegido' => 0
        ];

        $specs = [];
        if (isset($productoData['specs'])) {
            foreach ($productoData['specs'] as $spec) {
                $specs[] = [
                    'atributo' => $spec['atributo'],
                    'valor' => $spec['valor']
                ];
            }
        }

        $newProductoId = $this->productoModel->createProducto($productoDbData, $specs);

        // --- PROCESAR FOLLETOS ---
        if (isset($data['folletos']) && !empty($data['folletos'])) {
            $pdfUploadDir = __DIR__ . '/../../public/uploads/folletos/';
            if (!is_dir($pdfUploadDir)) mkdir($pdfUploadDir, 0755, true);

            foreach ($data['folletos'] as $folletoItem) {
                // Recuperar PDF
                $nuevaRutaPdf = '';
                if (!empty($folletoItem['ruta_pdf'])) {
                    $pdfBasename = basename($folletoItem['ruta_pdf']);
                    // Buscar en el ZIP
                    $zipPdfIndex = $zip->locateName('pdfs/' . $pdfBasename);
                    if ($zipPdfIndex !== false) {
                        $newPdfName = 'ficha_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.pdf';
                        $content = $zip->getFromIndex($zipPdfIndex);
                        file_put_contents($pdfUploadDir . $newPdfName, $content);
                        $nuevaRutaPdf = 'uploads/folletos/' . $newPdfName;
                    }
                }

                // Recuperar Imágenes Fuente (si hay)
                $nuevasFuentes = [];
                $imgsFuente = $folletoItem['imagenes_fuente'] ?? [];
                if (is_string($imgsFuente)) $imgsFuente = json_decode($imgsFuente, true) ?? [];
                
                foreach ($imgsFuente as $imgF) {
                    $imgBasename = basename($imgF);
                    $zipImgIndex = $zip->locateName('folletos_src/' . $imgBasename);
                    if ($zipImgIndex !== false) {
                        $newImgName = 'src_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.jpg';
                        $content = $zip->getFromIndex($zipImgIndex);
                        file_put_contents($pdfUploadDir . $newImgName, $content); // Las guardamos junto a los pdfs
                        $nuevasFuentes[] = 'uploads/folletos/' . $newImgName;
                    }
                }

                // Insertar Folleto
                if ($nuevaRutaPdf) {
                    $this->folletoModel->createFolleto([
                        'producto_id' => $newProductoId,
                        'nombre' => $folletoItem['nombre'],
                        'tipo' => $folletoItem['tipo'] ?? 'subido',
                        'categoria' => $folletoItem['categoria'] ?? 'general',
                        'ruta_pdf' => $nuevaRutaPdf,
                        'imagenes_fuente' => $nuevasFuentes,
                        'tamanio' => $folletoItem['tamanio'] ?? 0,
                        'activo' => 1,
                        'orden' => $folletoItem['orden'] ?? 0
                    ]);
                }
            }
        }

        $zip->close();

        return $newProductoId;
    }
}
