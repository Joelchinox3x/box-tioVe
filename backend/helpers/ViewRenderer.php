<?php
/**
 * ViewRenderer - Renderiza templates PHP a HTML
 * Permite usar templates con variables extraídas
 */

class ViewRenderer {
    /**
     * Renderizar un template PHP con datos
     * @param string $templatePath Ruta relativa del template (sin .php)
     * @param array $data Array asociativo con datos para el template
     * @return string HTML renderizado
     * @throws Exception Si el template no existe
     */
    public static function render($templatePath, $data = []) {
        $fullPath = __DIR__ . '/../views/pdf_templates/' . $templatePath . '.php';

        if (!file_exists($fullPath)) {
            throw new Exception("Template PDF no encontrado: {$fullPath}");
        }

        // Extraer variables del array para usar en el template
        extract($data);

        // Capturar el output del template
        ob_start();
        include $fullPath;
        $html = ob_get_clean();

        return $html;
    }
}
