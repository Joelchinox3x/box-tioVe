<?php
// app/Services/ProformaService.php
namespace App\Services;

class ProformaService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // 1. Validar y limpiar datos
    public function validateInput($post) {
        if (empty($post['cliente_id']) || empty($post['items'])) {
            throw new \Exception("Faltan datos requeridos");
        }
        return [
            'cliente_id' => $post['cliente_id'],
            'items' => $post['items']
        ];
    }

    // 2. Procesar Items y Matemáticas
    public function processItems($rawItems) {
        $processed = [];
        $total = 0;

        foreach ($rawItems as $item) {
            $qty = floatval($item['qty']);
            $price = floatval($item['price']);
            $sub = $qty * $price;
            $total += $sub;

            $processed[] = [
                'id' => $item['id'],
                'desc' => $item['desc'],
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $sub,
                'incluir_ficha' => isset($item['ficha']), // Booleans limpios
                'incluir_fotos' => isset($item['fotos']),
                'incluir_galeria' => isset($item['galeria']) // El resto de fotos (hojas extra)
            ];
        }

        // Cálculos Perú (Base Imponible)
        $subtotal_base = $total / 1.18;
        $igv_monto = $total - $subtotal_base;

        return [
            'items' => $processed,
            'totals' => [
                'subtotal' => $subtotal_base,
                'igv' => $igv_monto,
                'total' => $total
            ]
        ];
    }

    // 3. Obtener Datos Completos (Cliente + Productos para PDF)
    public function getFullData($cliente_id, $items_processed) {
        // Datos Cliente
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$cliente_id]);
        $cliente = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Enriquecer items con info de BD (Producto + Specs + Fotos)
        foreach ($items_processed as &$it) {
            if ($it['id']) {
                // 1. Datos básicos e imágenes
                $stmtP = $this->pdo->prepare("SELECT nombre, descripcion, imagenes FROM productos WHERE id = ?");
                $stmtP->execute([$it['id']]);
                $prod = $stmtP->fetch(\PDO::FETCH_ASSOC);

                // 2. NUEVO: Obtener Ficha Técnica Detallada (Tabla producto_specs)
                if ($prod) {
                    $stmtSpecs = $this->pdo->prepare("SELECT atributo, valor FROM producto_specs WHERE producto_id = ? ORDER BY orden ASC, id ASC");
                    $stmtSpecs->execute([$it['id']]);
                    $prod['specs'] = $stmtSpecs->fetchAll(\PDO::FETCH_ASSOC);
                }

                $it['db_data'] = $prod; 
            }
        }

        return ['cliente' => $cliente, 'items' => $items_processed];
    }

    // 4. Persistencia (Guardar)
    public function saveToDatabase($cliente_id, $totals, $items) {
        $json = json_encode($items, JSON_UNESCAPED_UNICODE);
        
        $sql = "INSERT INTO proformas (cliente_id, fecha, subtotal, igv, total, items, creado_en) 
                VALUES (?, CURDATE(), ?, ?, ?, ?, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $cliente_id, 
            $totals['subtotal'], 
            $totals['igv'], 
            $totals['total'], 
            $json
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    public function updatePdfPath($id, $path) {
        $stmt = $this->pdo->prepare("UPDATE proformas SET pdf_path = ? WHERE id = ?");
        $stmt->execute([$path, $id]);
    }
}