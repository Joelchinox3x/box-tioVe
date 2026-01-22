<?php
// app/Models/ProductoFolleto.php - Modelo de Folletos de Productos

namespace App\Models;

use Core\Model;

class ProductoFolleto extends Model {
    protected $table = 'producto_folletos';

    /**
     * Obtener todos los folletos de un producto
     *
     * @param int $productoId
     * @param bool $soloActivos Solo folletos activos
     * @return array
     */
    public function getByProducto($productoId, $soloActivos = true) {
        $sql = "SELECT * FROM {$this->table} WHERE producto_id = ?";

        if ($soloActivos) {
            $sql .= " AND activo = 1";
        }

        $sql .= " ORDER BY orden ASC, fecha_creacion DESC";

        $folletos = $this->query($sql, [$productoId]);

        // Decodificar imagenes_fuente de JSON a array
        foreach ($folletos as &$folleto) {
            $value = $folleto['imagenes_fuente'] ?? null;
            $folleto['imagenes_fuente'] = (!empty($value) && is_string($value)) 
                ? (json_decode($value, true) ?? []) 
                : [];
        }

        return $folletos;
    }

    /**
     * Obtener folletos por categoría
     *
     * @param int $productoId
     * @param string $categoria
     * @return array
     */
    public function getByCategoria($productoId, $categoria) {
        $sql = "SELECT * FROM {$this->table}
                WHERE producto_id = ? AND categoria = ? AND activo = 1
                ORDER BY orden ASC, fecha_creacion DESC";

        $folletos = $this->query($sql, [$productoId, $categoria]);

        foreach ($folletos as &$folleto) {
            $value = $folleto['imagenes_fuente'] ?? null;
            $folleto['imagenes_fuente'] = (!empty($value) && is_string($value)) 
                ? (json_decode($value, true) ?? []) 
                : [];
        }

        return $folletos;
    }

    /**
     * Crear un nuevo folleto
     *
     * @param array $data
     * @return int ID del folleto creado
     */
    public function createFolleto($data) {
        $folletoData = [
            'producto_id' => $data['producto_id'],
            'nombre' => $data['nombre'],
            'tipo' => $data['tipo'] ?? 'subido',
            'categoria' => $data['categoria'] ?? 'general',
            'ruta_pdf' => $data['ruta_pdf'],
            'imagenes_fuente' => isset($data['imagenes_fuente'])
                ? json_encode($data['imagenes_fuente'])
                : null,
            'tamanio' => $data['tamanio'] ?? null,
            'activo' => $data['activo'] ?? 1,
            'orden' => $data['orden'] ?? 0
        ];

        return $this->create($folletoData);
    }

    /**
     * Actualizar un folleto
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateFolleto($id, $data) {
        $folletoData = [];

        if (isset($data['nombre'])) {
            $folletoData['nombre'] = $data['nombre'];
        }

        if (isset($data['categoria'])) {
            $folletoData['categoria'] = $data['categoria'];
        }

        if (isset($data['activo'])) {
            $folletoData['activo'] = $data['activo'];
        }

        if (isset($data['orden'])) {
            $folletoData['orden'] = $data['orden'];
        }

        if (isset($data['imagenes_fuente'])) {
            $folletoData['imagenes_fuente'] = json_encode($data['imagenes_fuente']);
        }

        if (empty($folletoData)) {
            return false;
        }

        return $this->update($id, $folletoData);
    }

    /**
     * Eliminar un folleto (y sus archivos físicos)
     *
     * @param int $id
     * @return bool
     */
    public function deleteFolleto($id) {
        $folleto = $this->find($id);

        if (!$folleto) {
            return false;
        }

        // Eliminar PDF
        if (!empty($folleto['ruta_pdf'])) {
            $pdfPath = __DIR__ . '/../../public/' . $folleto['ruta_pdf'];
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }
        }

        // Eliminar imágenes fuente si existen
        if (!empty($folleto['imagenes_fuente'])) {
            $imagenesFuente = json_decode($folleto['imagenes_fuente'], true);
            if (is_array($imagenesFuente)) {
                foreach ($imagenesFuente as $img) {
                    $imgPath = __DIR__ . '/../../public/' . $img;
                    if (file_exists($imgPath)) {
                        unlink($imgPath);
                    }
                }
            }
        }

        return $this->delete($id);
    }

    /**
     * Incrementar contador de descargas
     *
     * @param int $id
     * @return bool
     */
    public function incrementarDescargas($id) {
        $sql = "UPDATE {$this->table} SET descargas = descargas + 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Activar/Desactivar folleto
     *
     * @param int $id
     * @param bool $activo
     * @return bool
     */
    public function toggleActivo($id, $activo) {
        return $this->update($id, ['activo' => $activo ? 1 : 0]);
    }

    /**
     * Reordenar folletos
     *
     * @param array $orden Array con ['id' => orden]
     * @return bool
     */
    public function reordenar($orden) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE {$this->table} SET orden = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);

            foreach ($orden as $id => $ordenNum) {
                $stmt->execute([$ordenNum, $id]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Obtener estadísticas de folletos de un producto
     *
     * @param int $productoId
     * @return array
     */
    public function getEstadisticas($productoId) {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN tipo = 'generado' THEN 1 ELSE 0 END) as generados,
                    SUM(CASE WHEN tipo = 'subido' THEN 1 ELSE 0 END) as subidos,
                    SUM(descargas) as total_descargas
                FROM {$this->table}
                WHERE producto_id = ?";

        return $this->queryOne($sql, [$productoId]) ?? [
            'total' => 0,
            'activos' => 0,
            'generados' => 0,
            'subidos' => 0,
            'total_descargas' => 0
        ];
    }
}
