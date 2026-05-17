<?php
/**
 * PdfEngine - Motor centralizado para generación de PDFs
 * Utiliza mPDF para crear documentos PDF desde templates HTML
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class PdfEngine {
    private $mpdf;
    private $templateName;

    /**
     * Constructor
     * @param string $templateName Nombre del template a usar
     * @param array $config Configuración adicional para mPDF
     */
    public function __construct($templateName = 'default', $config = []) {
        $this->templateName = $templateName;

        // Configuración base de mPDF
        $defaultConfig = [
            'margin_top' => 10,
            'margin_bottom' => 10,
            'margin_left' => 10,
            'margin_right' => 10,
            'tempDir' => __DIR__ . '/../public/tmp',
            'format' => 'A4',
            'orientation' => 'P', // Portrait
            'default_font' => 'dejavusans'
        ];

        $config = array_merge($defaultConfig, $config);

        // Registrar fonts custom
        $customFontDir = __DIR__ . '/../assets/fonts/';
        $defaultFontConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultFontConfig['fontDir'];
        $config['fontDir'] = array_merge($fontDirs, [$customFontDir]);

        $defaultFontData = (new FontVariables())->getDefaults();
        $fontData = $defaultFontData['fontdata'];
        $config['fontdata'] = $fontData + [
            'oswald' => [
                'R' => 'Oswald-Regular.ttf',
                'B' => 'Oswald-Bold.ttf',
            ],
            'montserrat' => [
                'R' => 'Montserrat-Regular.ttf',
                'B' => 'Montserrat-Bold.ttf',
            ],
        ];

        // Inicializar mPDF
        $this->mpdf = new Mpdf($config);
    }

    /**
     * Generar PDF desde un template
     * @param string $templatePath Ruta relativa del template (ej: 'comprobante_pago/body')
     * @param array $data Datos para el template
     * @param string $outputPath Ruta completa donde guardar el PDF
     * @param string $mode Modo de salida (F=archivo, I=browser, D=descarga, S=string)
     * @return mixed
     */
    private $watermark = null;

    /**
     * Configurar marca de agua con imagen
     * @param string $imagePath Ruta absoluta de la imagen
     * @param float $x Posición X en mm desde izquierda
     * @param float $y Posición Y en mm desde arriba
     * @param float $w Ancho en mm
     * @param float $alpha Opacidad 0.0 a 1.0
     */
    public function setWatermark($imagePath, $x = 10, $y = 80, $w = 30, $alpha = 0.12) {
        $this->watermark = [
            'path' => $imagePath,
            'x' => $x,
            'y' => $y,
            'w' => $w,
            'alpha' => $alpha,
        ];
    }

    public function generate($templatePath, $data, $outputPath, $mode = 'F') {
        error_log('PdfEngine::generate() - Template: ' . $templatePath);

        // Renderizar HTML con ViewRenderer
        require_once __DIR__ . '/ViewRenderer.php';
        error_log('Renderizando template...');

        $html = ViewRenderer::render($templatePath, $data);
        error_log('HTML renderizado: ' . strlen($html) . ' caracteres');

        $html = $this->aplicarFondoTemplateSiCorresponde($html, $data);

        // Escribir HTML al PDF
        error_log('Escribiendo HTML a mPDF...');
        $this->mpdf->WriteHTML($html);

        // Marca de agua (entre WriteHTML y Output)
        if ($this->watermark) {
            $wm = $this->watermark;
            $this->mpdf->SetAlpha($wm['alpha']);
            $this->mpdf->Image($wm['path'], $wm['x'], $wm['y'], $wm['w'], 0, '', '', true, false);
            $this->mpdf->SetAlpha(1);
        }

        // Generar PDF
        error_log('Generando PDF en modo: ' . $mode);
        $result = $this->mpdf->Output($outputPath, $mode);
        error_log('mPDF Output completado');

        return $result;
    }

    /**
     * Agregar una nueva página
     */
    public function addPage() {
        $this->mpdf->AddPage();
    }

    /**
     * Obtener instancia de mPDF (para operaciones avanzadas)
     * @return Mpdf
     */
    public function getMpdf() {
        return $this->mpdf;
    }

    /**
     * Escribir HTML directamente
     * @param string $html HTML a escribir
     */
    public function writeHTML($html) {
        $this->mpdf->WriteHTML($html);
    }

    private function aplicarFondoTemplateSiCorresponde($html, $data) {
        if (!is_array($data) || empty($data['template_background'])) {
            return $html;
        }

        if (stripos($html, 'background-image-resize') !== false || stripos($html, 'data-auto-template-bg') !== false) {
            return $html;
        }

        $bgPath = (string)$data['template_background'];
        if (!preg_match('/^(https?:)?\/\//i', $bgPath) && strpos($bgPath, 'file://') !== 0) {
            $bgPath = 'file://' . str_replace('\\', '/', $bgPath);
        }
        $bg = htmlspecialchars($bgPath, ENT_QUOTES, 'UTF-8');
        $css = '<style data-auto-template-bg="1">'
             . 'body{background-image:url(\'' . $bg . '\');background-repeat:no-repeat;background-position:center center;background-image-resize:6;}'
             . '</style>';

        if (stripos($html, '</head>') !== false) {
            return preg_replace('/<\/head>/i', $css . '</head>', $html, 1);
        }

        return $css . $html;
    }
}
