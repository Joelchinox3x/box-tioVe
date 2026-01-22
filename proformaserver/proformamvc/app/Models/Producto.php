<?php
// app/Models/Producto.php - Modelo de Producto

namespace App\Models;

use Core\Model;

class Producto extends Model {
    protected $table = 'productos';
    
    // Obtener todos con orden (más recientes primero)
    public function getAllOrdered($orderBy = 'fecha_modificacion DESC') {
        return $this->all($orderBy);
    }
    
    // Crear producto con specs
    public function createProducto($data, $specs = []) {
        try {
            $this->db->beginTransaction();
            
            // Generar token seguro
            $token = bin2hex(random_bytes(16));

            // Crear producto principal
            $productoData = [
                'token' => $token, // Nuevo
                'nombre' => $data['nombre'],
                'modelo' => $data['modelo'] ?? '',
                'sku' => $data['sku'] ?? '',
                'precio' => $data['precio'] ?? 0,
                'moneda' => $data['moneda'] ?? 'PEN',
                'descripcion' => $data['descripcion'] ?? '',
                'layout_specs' => $data['layout_specs'] ?? '2col', // Nuevo campo
                'imagenes' => json_encode($data['imagenes'] ?? []),
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'protegido' => $data['protegido'] ?? 0
            ];
            
            $productoId = $this->create($productoData);
            
            // Crear especificaciones
            if (!empty($specs)) {
                $this->createSpecs($productoId, $specs);
            }
            
            $this->db->commit();
            return $productoId;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Obtener por Token Público
    public function getByToken($token) {
        $producto = $this->queryOne("SELECT * FROM {$this->table} WHERE token = ?", [$token]);

        if (!$producto) return null;

        $producto['specs'] = $this->getSpecs($producto['id']);
        $producto['imagenes'] = json_decode($producto['imagenes'], true) ?? [];

        return $producto;
    }
    
    // Actualizar producto con specs
    public function updateProducto($id, $data, $specs = []) {
        try {
            $this->db->beginTransaction();
            
            // Actualizar producto principal
            $productoData = [
                'nombre' => $data['nombre'],
                'modelo' => $data['modelo'] ?? '',
                'sku' => $data['sku'] ?? '',
                'precio' => $data['precio'] ?? 0,
                'moneda' => $data['moneda'] ?? 'PEN',
                'descripcion' => $data['descripcion'] ?? '',
                'layout_specs' => $data['layout_specs'] ?? '2col'
            ];

            if (isset($data['imagenes'])) {
                $productoData['imagenes'] = json_encode($data['imagenes']);
            }

            if (isset($data['protegido'])) {
                $productoData['protegido'] = $data['protegido'];
            }

            $this->update($id, $productoData);
            
            // Actualizar especificaciones
            $this->deleteSpecs($id);
            if (!empty($specs)) {
                $this->createSpecs($id, $specs);
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    // Crear especificaciones
    private function createSpecs($productoId, $specs) {
        $sql = "INSERT INTO producto_specs (producto_id, atributo, valor, orden) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        foreach ($specs as $index => $spec) {
            $stmt->execute([
                $productoId,
                $spec['atributo'],
                $spec['valor'],
                $index
            ]);
        }
    }
    
    // Eliminar especificaciones
    private function deleteSpecs($productoId) {
        $sql = "DELETE FROM producto_specs WHERE producto_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$productoId]);
    }
    
    // Obtener producto con specs
    public function getWithSpecs($id) {
        $producto = $this->find($id);

        if (!$producto) {
            return null;
        }

        $producto['specs'] = $this->getSpecs($id);
        $producto['imagenes'] = json_decode($producto['imagenes'], true) ?? [];

        return $producto;
    }
    
    // Obtener specs de un producto
    public function getSpecs($productoId) {
        return $this->query(
            "SELECT * FROM producto_specs WHERE producto_id = ? ORDER BY orden ASC",
            [$productoId]
        );
    }
    
    // Eliminar producto (con validación de bloqueo)
    public function deleteProducto($id) {
        $producto = $this->find($id);
        
        if (!$producto) {
            return ['success' => false, 'message' => 'Producto no encontrado'];
        }
        
        if ($producto['protegido'] == 1) {
            return ['success' => false, 'message' => 'Producto protegido'];
        }
        
        // Eliminar imágenes físicas
        $imagenes = json_decode($producto['imagenes'], true);
        if (is_array($imagenes)) {
            foreach ($imagenes as $img) {
                $path = __DIR__ . '/../../public/' . $img;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }

        $this->delete($id);
        return ['success' => true, 'message' => 'Producto eliminado'];
    }
    
    // Contar productos
    public function getTotalProductos() {
        return $this->count();
    }
    
    // Buscar productos
    public function search($term) {
        return $this->query(
            "SELECT * FROM {$this->table}
             WHERE nombre LIKE ? OR modelo LIKE ? OR sku LIKE ?
             ORDER BY fecha_modificacion DESC",
            ["%{$term}%", "%{$term}%", "%{$term}%"]
        );
    }
}