<?php
namespace App\Pdf;

class ViewRenderer {
    // Carga un archivo de vista y devuelve el HTML como string
    public static function render($viewName, $data = []) {
        // Define PROJECT_ROOT constant if not already defined
        if (!defined('PROJECT_ROOT')) {
            define('PROJECT_ROOT', __DIR__ . '/../..');
        }

        // Extrae el array para que $data['cliente'] se convierta en variable $cliente
        extract($data);

        // Inicia el almacenamiento en búfer (para no imprimir nada en pantalla, solo capturar)
        ob_start();

        // Busca el archivo (corregir capitalización)
        $path = __DIR__ . '/../Views/pdf/' . $viewName . '.php';
        
        if (file_exists($path)) {
            include $path;
        } else {
            echo "Error: Vista '$viewName' no encontrada en $path";
        }

        // Devuelve el contenido capturado y limpia el búfer
        return ob_get_clean();
    }
}