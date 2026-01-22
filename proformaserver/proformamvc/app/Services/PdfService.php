<?php
// app/Services/PdfService.php
namespace App\Services;

use App\Pdf\PdfEngine;
use App\Models\Producto;

class PdfService {
    private $productoModel;

    public function __construct() {
        $this->productoModel = new Producto();
    }

    /**
     * Generar PDF de proforma
     *
     * @param array $proforma Datos de la proforma
     * @param string $template Nombre del template (orange, blue, simple)
     * @return string Path del PDF generado
     */
    public function generateProformaPdf($proforma, $templateName = null) {
        // 1. Cargar Configuración del Tema desde DB
        $templateName = $templateName ?? ($proforma['template'] ?? 'orange');
        $pdfTemplateModel = new \App\Models\PdfTemplate();
        $themeConfig = $pdfTemplateModel->getByName($templateName);

        // Fallback si no existe o está inactivo
        if (!$themeConfig) {
            $themeConfig = $pdfTemplateModel->getDefault();
        }

        // 2. Preparar Variables Visuales (Rutas Absolutas)
        $viewsPath =  __DIR__ . '/../Views/pdf/';
        $colorBrand = $themeConfig['color_brand'] ?? '#f37021';
        
        $fondoImg = '';
        if (!empty($themeConfig['fondo_img'])) {
            $imgName = basename($themeConfig['fondo_img']); 
            $fondoImg = $viewsPath . 'fondo/' . $imgName;
        }

        $footerImg = '';
        if (!empty($themeConfig['footer_img'])) {
            $imgName = basename($themeConfig['footer_img']); 
            $footerImg = $viewsPath . 'footer/' . $imgName;
        }
        
        $headerView = $themeConfig['header_php'] ?? 'header.php';

        // Crear carpeta de PDFs si no existe
        $pdfDir = __DIR__ . '/../../public/uploads/pdfs';
        if (!is_dir($pdfDir)) {
            mkdir($pdfDir, 0777, true);
        }

        // Preparar datos para el PDF
        $fullData = [
            'color_brand' => $colorBrand,
            'fondo_img' => $fondoImg,
            'footer_img' => $footerImg,
            'header_view' => $headerView,
            'proforma_id' => $proforma['id'],
            'fecha' => $proforma['fecha_creacion'],
            'moneda' => $proforma['moneda'] ?? 'PEN',
            'cliente' => [
                'nombre' => $proforma['cliente_nombre'],
                'ruc_dni' => $proforma['dni_ruc'],
                'direccion' => $proforma['direccion'] ?? '',
                'telefono' => $proforma['telefono'] ?? '',
                'email' => $proforma['email'] ?? ''
            ],
            'items' => [],
            'template_config' => $themeConfig // <--- NUEVO: Pasamos toda la config para estilos dinámicos
        ];

        // Preparar items con flags de PDF
        if (isset($proforma['items']) && is_array($proforma['items'])) {
            foreach ($proforma['items'] as $item) {
                // Si el item tiene producto_id, obtener datos completos del producto desde la BD
                $dbData = null;
                if (!empty($item['producto_id'])) {
                    $producto = $this->productoModel->getWithSpecs($item['producto_id']);

                    if ($producto) {
                        // Asegurar que imagenes esté en formato JSON string
                        $imagenes = $producto['imagenes'] ?? '[]';
                        if (is_array($imagenes)) {
                            $imagenes = json_encode($imagenes);
                        }

                        $dbData = [
                            'nombre' => $producto['nombre'],
                            'descripcion' => $producto['descripcion'] ?? '',
                            'imagenes' => $imagenes,
                            'specs' => $producto['specs'] ?? [],
                            'layout_specs' => $producto['layout_specs'] ?? '2col' // Default to 2col
                        ];
                    }
                }

                // Si no hay datos del producto, crear estructura vacía
                if (!$dbData) {
                    // Para items manuales, usar las imágenes subidas si existen
                    $imagenesManuales = $item['imagenes_manuales'] ?? '[]';

                    $dbData = [
                        'nombre' => $item['descripcion'],
                        'descripcion' => '',
                        'imagenes' => $imagenesManuales,
                        'specs' => []
                    ];
                }

                $fullData['items'][] = [
                    'id' => $item['producto_id'] ?? null,
                    'desc' => $item['descripcion'],
                    'qty' => $item['cantidad'],
                    'price' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                    'incluir_ficha' => isset($item['incluir_ficha']) && $item['incluir_ficha'] == 1,
                    'incluir_fotos' => isset($item['incluir_fotos']) && $item['incluir_fotos'] == 1,
                    'incluir_galeria' => isset($item['incluir_galeria']) && $item['incluir_galeria'] == 1,
                    'db_data' => $dbData
                ];
            }
        }

        // Totales
        $totals = [
            'subtotal' => $proforma['subtotal'],
            'igv' => $proforma['igv'],
            'total' => $proforma['total']
        ];

        // Nombre del archivo
        $filename = 'proforma_' . $proforma['id'] . '_' . time() . '.pdf';
        $outputPath = $pdfDir . '/' . $filename;

        // Generar PDF (Siempre usar master template)
        $pdfEngine = new PdfEngine('master');
        $pdfEngine->generate($fullData, $totals, $outputPath);

        // Retornar path relativo
        return 'uploads/pdfs/' . $filename;
    }

    /**
     * Descargar PDF
     *
     * @param string $pdfPath Path del PDF
     * @param string $filename Nombre del archivo para descarga
     */
    public function download($pdfPath, $filename = 'proforma.pdf') {
        $fullPath = __DIR__ . '/../../public/' . $pdfPath;

        if (file_exists($fullPath)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($fullPath));
            readfile($fullPath);
            exit;
        }

        throw new \Exception('PDF no encontrado');
    }

    /**
     * Ver PDF en navegador
     *
     * @param string $pdfPath Path del PDF
     */
    public function view($pdfPath) {
        $fullPath = __DIR__ . '/../../public/' . $pdfPath;

        if (file_exists($fullPath)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="proforma.pdf"');
            header('Content-Length: ' . filesize($fullPath));
            readfile($fullPath);
            exit;
        }

        throw new \Exception('PDF no encontrado');
    }
}