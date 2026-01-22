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

        // DIRECTO: Escanear carpeta de fuentes para carga dinámica
        // Solo registra Regular y Bold (ignora SemiBold, Light, Medium, Italic, etc.)
        $customFontDir = __DIR__ . '/../../public/assets/fonts';
        $dynamicFonts = [];

        if (is_dir($customFontDir)) {
            $files = scandir($customFontDir);
            foreach ($files as $file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if ($ext === 'ttf' || $ext === 'otf') {
                    $filename = pathinfo($file, PATHINFO_FILENAME);

                    // Detectar Familia y Variante
                    // Solo procesar Regular y Bold
                    if (preg_match('/^(.+?)(?:-(Regular|Bold))?$/i', $filename, $matches)) {
                        $familyName = strtolower(trim($matches[1]));
                        $variant = isset($matches[2]) ? strtolower($matches[2]) : 'regular';

                        // Solo permitir Regular y Bold
                        if ($variant !== 'regular' && $variant !== 'bold') {
                            continue; // Saltar esta fuente
                        }

                        // Mapear variante
                        $mpdfKey = ($variant === 'bold') ? 'B' : 'R';

                        // Inicializar familia si no existe
                        if (!isset($dynamicFonts[$familyName])) {
                            $dynamicFonts[$familyName] = [];
                        }

                        // Asignar archivo a la variante (solo R y B)
                        $dynamicFonts[$familyName][$mpdfKey] = $file;
                    }
                }
            }
        }

// 2. INICIALIZAR MPDF CON LA CONFIGURACIÓN
        $this->mpdf = new Mpdf([
            'margin_top' => 10,
            'margin_bottom' => 5,
            'margin_left' => 10,
            'margin_right' => 10,

            // CONFIGURAR DIRECTORIO TEMPORAL
            'tempDir' => __DIR__ . '/../../public/tmp',

            // AGREGAR TU CARPETA DE FUENTES
            'fontDir' => array_merge($fontDirs, [
                $customFontDir, 
            ]),

            // REGISTRAR TUS FUENTES (La clave del array es el nombre que usarás en CSS)
            // Combinamos las default + las hardcodeadas antiguas (por compatibilidad) + las dinámicas
            'fontdata' => $fontData + [
                'tekolocal' => [ 
                    'R' => 'Teko-Regular.ttf', 
                    'B' => 'Teko-Bold.ttf',    
                ],
                // ... otras hardcodeadas si se necesitan ...
            ] + $dynamicFonts, // <--- AQUÍ SE MAGIA

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
        $this->processSpecs($fullData['items'] ?? [], $fullData, $basePath);
        $this->processMainPhotos($fullData['items'] ?? [], $fullData, $basePath);
        $this->processExtraGallery($fullData['items'] ?? [], $fullData, $basePath);

        $this->mpdf->Output($outputPath, $mode);
    }

    private function processSpecs($items, $fullData, $basePath) {
        $validItems = [];
        foreach($items as $it) {
             $hasData = !empty($it['db_data']['descripcion']) || !empty($it['db_data']['specs']);
             if($it['incluir_ficha'] && $hasData) {
                 $validItems[] = $it;
             }
        }

        if (count($validItems) > 0) {
            foreach ($validItems as $it) {
                $this->mpdf->AddPage();
                
                // Determine layout view based on preference
                $layout = $it['db_data']['layout_specs'] ?? '2col';
                $viewName = ($layout === '1col') ? 'hoja2_specs' : 'hoja2_specs-2col';

                $html = ViewRenderer::render($basePath . $viewName, [
                    'item' => $it,
                    'fullData' => $fullData
                ]);
                $this->mpdf->WriteHTML($html);
            }
        }
    }

    private function processMainPhotos($items, $fullData, $basePath) {
        $validItems = [];
        foreach($items as $it) {
            if ($it['incluir_fotos'] && !empty($it['db_data']['imagenes'])) {
                $fotos = json_decode($it['db_data']['imagenes'], true);
                // Solo si hay al menos una foto para mostrar en la hoja principal
                if (count($fotos) > 0) {
                     $validItems[] = $it;
                }
            }
        }

        if (count($validItems) > 0) {
            foreach ($validItems as $it) {
                 $fotos = json_decode($it['db_data']['imagenes'], true);
                 // Tomar 2 primeras
                 $dos_primeras = array_slice($fotos, 0, 2);
                 
                 // Convertir rutas absolutas
                 $fotosAbsolutas = array_map(function($foto) {
                    if (strpos($foto, PROJECT_ROOT) === 0 || strpos($foto, '/') === 0) {
                        return $foto;
                    }
                    return PROJECT_ROOT . '/public/' . $foto;
                }, $dos_primeras);

                $this->mpdf->AddPage();
                $html = ViewRenderer::render($basePath . 'hoja3_fotos', [
                    'item_name' => $it['desc'],
                    'photos' => $fotosAbsolutas,
                    'fullData' => $fullData
                ]);
                $this->mpdf->WriteHTML($html);
            }
        }
    }

    private function processExtraGallery($items, $fullData, $basePath) {
        $validItems = [];
        foreach($items as $it) {
            if ($it['incluir_galeria'] && !empty($it['db_data']['imagenes'])) {
                 $fotos = json_decode($it['db_data']['imagenes'], true);
                 // Offset depends on whether main photos are included
                 $offset = ($it['incluir_fotos']) ? 2 : 0;
                 if (count(array_slice($fotos, $offset)) > 0) {
                     $validItems[] = $it;
                 }
            }
        }

        if (count($validItems) > 0) {
            foreach ($validItems as $it) {
                $fotos = json_decode($it['db_data']['imagenes'], true);
                
                // Determine start index and offset
                $offset = ($it['incluir_fotos']) ? 2 : 0;
                $current_index = ($it['incluir_fotos']) ? 3 : 1;

                $resto_fotos = array_slice($fotos, $offset);
                
                // Convert relative to absolute paths for all extra photos
                $resto_fotos_abs = array_map(function($foto) {
                    if (strpos($foto, PROJECT_ROOT) === 0 || strpos($foto, '/') === 0) {
                        return $foto;
                    }
                    return PROJECT_ROOT . '/public/' . $foto;
                }, $resto_fotos);

                // Chunk into groups of 2
                $grupos = array_chunk($resto_fotos_abs, 2);
                
                foreach($grupos as $grupo) {
                    $this->mpdf->AddPage();
                    $html = ViewRenderer::render($basePath . 'hoja4_galeria', [
                        'item_name' => $it['desc'],
                        'photos_group' => $grupo,
                        'start_index' => $current_index, // Pass the starting global index
                        'fullData' => $fullData 
                    ]);
                    $this->mpdf->WriteHTML($html);
                    
                    // Increment global index by the size of this group
                    $current_index += count($grupo);
                }
            }
        }
    }
}