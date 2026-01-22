<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Producto;

class ApiController extends Controller {
    
    public function getSKUs() {
        header('Content-Type: application/json');

        $productoModel = new Producto();
        $productos = $productoModel->getAll();

        // Obtener todos los SKUs que empiezan con T
        $skus = array_filter(array_map(function($p) {
            $sku = $p['sku'] ?? null;
            if ($sku && strpos($sku, 'T') === 0) {
                return $sku;
            }
            return null;
        }, $productos));

        // Extraer los números de los SKUs (T01 -> 1, T12 -> 12)
        $numeros = [];
        foreach ($skus as $sku) {
            // Extraer número después de T (puede ser T01, T-01, T001, etc)
            if (preg_match('/^T-?(\d+)/', $sku, $matches)) {
                $numeros[] = (int)$matches[1];
            }
        }

        // Encontrar el siguiente número disponible
        $siguienteNumero = 1;
        if (!empty($numeros)) {
            $siguienteNumero = max($numeros) + 1;
        }

        // Formatear el siguiente SKU (T01, T02, ... T09, T10, T11...)
        $siguienteSKU = 'T' . str_pad($siguienteNumero, 2, '0', STR_PAD_LEFT);

        echo json_encode([
            'skus' => array_values($skus),
            'nextSKU' => $siguienteSKU
        ]);
        exit;
    }
}
