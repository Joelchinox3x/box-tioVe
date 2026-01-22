<?php
namespace App\Pdf;

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class PdfEngine {
    private $mpdf;
    private $templateName; // Variable para guardar "orange", "blue", etc.

    // Recibimos el nombre del template en el constructor
  
    public function __construct($templateName = 'orange') {
        $this->templateName = $templateName;

        // 1. CONFIGURAR DIRECTORIOS DE FUENTES
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

// 2. INICIALIZAR MPDF CON LA CONFIGURACIÓN
        $this->mpdf = new Mpdf([
            'margin_top' => 10,
            'margin_bottom' => 5,
            'margin_left' => 10,
            'margin_right' => 10,

            // Carpeta temporal con permisos de escritura
            'tempDir' => __DIR__ . '/../../uploads/tmp',

            // AGREGAR TU CARPETA DE FUENTES
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/../../assets/fonts', // Ruta hacia tu carpeta assets/fonts
            ]),
            
            // REGISTRAR TUS FUENTES (La clave del array es el nombre que usarás en CSS)
            'fontdata' => $fontData + [
                'tekolocal' => [ // <--- Este nombre usas en font-family
                    'R' => 'Teko-Regular.ttf', // Nombre real del archivo
                    'B' => 'Teko-Bold.ttf',    // Nombre real del archivo Bold
                ],
                'robotocondensed' => [
                    'R' => 'RobotoCondensed-Regular.ttf',
                    'B' => 'RobotoCondensed-Bold.ttf',
                ]
            ],
            'default_font' => 'sans-serif'
        ]);
    }

    
    public function generate(array $fullData, array $totals, string $outputPath, $mode = 'F') {
        $basePath = $this->templateName . '/';
        // --- HOJA 1: PROFORMA ---
        $html1 = ViewRenderer::render($basePath . 'hoja1', [
            'fullData' => $fullData,
            'totals' => $totals
        ]);
        $this->mpdf->WriteHTML($html1);

        // --- HOJAS EXTRA ---
        // Pasamos $fullData a todas para que el Header funcione en cualquier hoja
        $this->processSpecs($fullData['items'], $fullData, $basePath);
        $this->processMainPhotos($fullData['items'], $fullData, $basePath);
        $this->processExtraGallery($fullData['items'], $fullData, $basePath);

        $this->mpdf->Output($outputPath, $mode);
    }

    private function processSpecs($items, $fullData, $basePath) {
        $hasSpecs = false;
        foreach($items as $it) if($it['incluir_ficha']) $hasSpecs = true;

        if ($hasSpecs) {
            $this->mpdf->AddPage();
            // Renderizamos la vista pasando AMBOS: el item y la data global
            foreach ($items as $it) {
                $hasData = !empty($it['db_data']['descripcion']) || !empty($it['db_data']['specs']);
                if ($it['incluir_ficha'] && $hasData) {
                    $html = ViewRenderer::render($basePath . 'hoja2_specs', [
                        'item' => $it,
                        'fullData' => $fullData // <--- ESTO ES LA CLAVE
                    ]);
                    $this->mpdf->WriteHTML($html);
                }
            }
        }
    }

    private function processMainPhotos($items, $fullData, $basePath) {
        $hasMain = false;
        foreach($items as $it) if($it['incluir_fotos']) $hasMain = true;

        if ($hasMain) {
            $this->mpdf->AddPage();
            foreach ($items as $it) {
                if ($it['incluir_fotos'] && !empty($it['db_data']['imagenes'])) {
                    $fotos = json_decode($it['db_data']['imagenes'], true);
                    $dos_primeras = array_slice($fotos, 0, 2);
                    if (count($dos_primeras) > 0) {
                        $html = ViewRenderer::render($basePath . 'hoja3_fotos', [
                            'item_name' => $it['desc'],
                            'photos' => $dos_primeras,
                            'fullData' => $fullData // <--- CLAVE
                        ]);
                        $this->mpdf->WriteHTML($html);
                    }
                }
            }
        }
    }

    private function processExtraGallery($items, $fullData, $basePath) {
        $hasExtra = false;
        foreach($items as $it) if($it['incluir_galeria']) $hasExtra = true;

        if ($hasExtra) {
            foreach ($items as $it) {
                if ($it['incluir_galeria'] && !empty($it['db_data']['imagenes'])) {
                    $fotos = json_decode($it['db_data']['imagenes'], true);
                    $offset = ($it['incluir_fotos']) ? 2 : 0;
                    $resto_fotos = array_slice($fotos, $offset);
                    if (count($resto_fotos) > 0) {
                        $grupos = array_chunk($resto_fotos, 2);
                        foreach($grupos as $grupo) {
                            $this->mpdf->AddPage();
                            $html = ViewRenderer::render($basePath . 'hoja4_galeria', [
                                'item_name' => $it['desc'],
                                'photos_group' => $grupo,
                                'fullData' => $fullData // <--- CLAVE
                            ]);
                            $this->mpdf->WriteHTML($html);
                        }
                    }
                }
            }
        }
    }
}