<?php
// app/Models/Proforma.php - Modelo de Proforma

namespace App\Models;

use Core\Model;

class Proforma extends Model {
    protected $table = 'proformas';

    // Obtener proformas con información del cliente
    public function getAllWithCliente() {
        return $this->query(
            "SELECT p.*, c.nombre as cliente_nombre, c.dni_ruc, c.direccion, c.telefono,
             (SELECT pr.sku 
              FROM proforma_items pi 
              JOIN productos pr ON pi.producto_id = pr.id 
              WHERE pi.proforma_id = p.id 
              ORDER BY pi.orden ASC LIMIT 1) as primer_sku,
             (SELECT pr.imagenes 
              FROM proforma_items pi 
              JOIN productos pr ON pi.producto_id = pr.id 
              WHERE pi.proforma_id = p.id 
              ORDER BY pi.orden ASC LIMIT 1) as primer_imagen_json
             FROM {$this->table} p
             LEFT JOIN clientes c ON p.cliente_id = c.id
             ORDER BY p.fecha_creacion DESC"
        );
    }

    // Obtener proforma con detalles
    public function getWithDetails($id) {
        $proforma = $this->queryOne(
            "SELECT p.*, c.nombre as cliente_nombre, c.dni_ruc, c.direccion, c.telefono, c.email
             FROM {$this->table} p
             LEFT JOIN clientes c ON p.cliente_id = c.id
             WHERE p.id = ?",
            [$id]
        );

        if ($proforma) {
            // Obtener items de la proforma
            $proforma['items'] = $this->query(
                "SELECT pi.*, pr.nombre as producto_nombre, pr.modelo, pr.sku
                 FROM proforma_items pi
                 LEFT JOIN productos pr ON pi.producto_id = pr.id
                 WHERE pi.proforma_id = ?
                 ORDER BY pi.orden",
                [$id]
            );
        }

        return $proforma;
    }

    // Obtener proforma por TOKEN (para vista pública)
    public function getByToken($token) {
        $proforma = $this->queryOne(
            "SELECT p.*, c.nombre as cliente_nombre, c.dni_ruc, c.direccion, c.telefono, c.email
             FROM {$this->table} p
             LEFT JOIN clientes c ON p.cliente_id = c.id
             WHERE p.token = ?",
            [$token]
        );

        if ($proforma) {
            // Obtener items de la proforma
            $proforma['items'] = $this->query(
                "SELECT pi.*, pr.nombre as producto_nombre, pr.modelo, pr.sku
                 FROM proforma_items pi
                 LEFT JOIN productos pr ON pi.producto_id = pr.id
                 WHERE pi.proforma_id = ?
                 ORDER BY pi.orden",
                [$proforma['id']]
            );
        }

        return $proforma;
    }


    // Crear proforma con items
    public function createProforma($data, $items) {
        try {
            // Iniciar transacción
            $this->db->beginTransaction();

            // Crear proforma
            $proformaData = [
                'cliente_id' => $data['cliente_id'],
                'correlativo' => $data['correlativo'] ?? $this->getNextCorrelativo(),
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'vigencia_dias' => $data['vigencia_dias'] ?? 30,
                'moneda' => $data['moneda'] ?? 'PEN',
                'subtotal' => $data['subtotal'] ?? 0,
                'descuento' => $data['descuento'] ?? 0,
                'igv' => $data['igv'] ?? 0,
                'total' => $data['total'] ?? 0,
                'observaciones' => $data['observaciones'] ?? '',
                'condiciones' => $data['condiciones'] ?? '',
                'template' => $data['template'] ?? 'orange',
                'token' => $data['token'] ?? null
            ];

            $proformaId = $this->create($proformaData);

            // Insertar items
            if (!empty($items)) {
                foreach ($items as $index => $item) {
                    $this->execute(
                        "INSERT INTO proforma_items (proforma_id, producto_id, descripcion, cantidad, precio_unitario, subtotal, orden, incluir_ficha, incluir_fotos, incluir_galeria, imagenes_manuales)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                        [
                            $proformaId,
                            $item['producto_id'] ?? null,
                            $item['descripcion'],
                            $item['cantidad'],
                            $item['precio_unitario'],
                            $item['subtotal'],
                            $index,
                            $item['incluir_ficha'] ?? 0,
                            $item['incluir_fotos'] ?? 0,
                            $item['incluir_galeria'] ?? 0,
                            $item['imagenes_manuales'] ?? null
                        ]
                    );
                }
            }

            // Confirmar transacción
            $this->db->commit();

            return $proformaId;

        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Obtener siguiente correlativo
    public function getNextCorrelativo() {
        $result = $this->queryOne(
            "SELECT MAX(CAST(SUBSTRING(correlativo, 4) AS UNSIGNED)) as max_num
             FROM {$this->table}
             WHERE correlativo LIKE 'TRA%'"
        );

        $nextNum = ($result['max_num'] ?? 0) + 1;
        return 'TRA' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }

    // Eliminar proforma
    public function deleteProforma($id) {
        try {
            $this->db->beginTransaction();

            // Eliminar items
            $this->execute("DELETE FROM proforma_items WHERE proforma_id = ?", [$id]);

            // Eliminar proforma
            $this->delete($id);

            $this->db->commit();

            return ['success' => true];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Obtener total de proformas
    public function getTotalProformas() {
        return $this->count();
    }

    // Obtener proformas recientes
    public function getRecientes($limit = 5) {
        return $this->query(
            "SELECT p.*, c.nombre as cliente_nombre
             FROM {$this->table} p
             LEFT JOIN clientes c ON p.cliente_id = c.id
             ORDER BY p.fecha_creacion DESC
             LIMIT {$limit}"
        );
    }

    // Buscar proformas
    public function search($term) {
        return $this->query(
            "SELECT p.*, c.nombre as cliente_nombre
             FROM {$this->table} p
             LEFT JOIN clientes c ON p.cliente_id = c.id
             WHERE p.correlativo LIKE ? OR c.nombre LIKE ? OR c.dni_ruc LIKE ?
             ORDER BY p.fecha_creacion DESC
             LIMIT 20",
            ["%{$term}%", "%{$term}%", "%{$term}%"]
        );
    }
}
