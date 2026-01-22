<?php
// app/Models/Cliente.php - Modelo de Cliente

namespace App\Models;

use Core\Model;

class Cliente extends Model {
    protected $table = 'clientes';
    
    // Obtener clientes ordenados por modificación
    public function getAllOrdered() {
        return $this->query(
            "SELECT * FROM {$this->table} ORDER BY fecha_modificacion DESC"
        );
    }
    
    // Buscar por RUC/DNI
    public function findByDocument($dni_ruc) {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE dni_ruc = ?",
            [$dni_ruc]
        );
    }
    
    // Crear cliente con validación
    public function createCliente($data) {
        $clienteData = [
            'nombre' => $data['nombre'],
            'dni_ruc' => $data['dni_ruc'] ?? '',
            'direccion' => $data['direccion'] ?? '',
            'telefono' => $data['telefono'] ?? '',
            'email' => $data['email'] ?? '',
            'foto_url' => $data['foto_url'] ?? null,
            'latitud' => $data['latitud'] ?? null,
            'longitud' => $data['longitud'] ?? null,
            'protegido' => $data['protegido'] ?? 0,
            'fecha_modificacion' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($clienteData);
    }
    
    // Actualizar cliente
    public function updateCliente($id, $data) {
        $data['fecha_modificacion'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }
    
    // Eliminar solo si no está protegido
    public function deleteIfNotProtected($id) {
        $cliente = $this->find($id);

        if (!$cliente) {
            return ['success' => false, 'message' => 'Cliente no encontrado'];
        }

        if ($cliente['protegido'] == 1) {
            return ['success' => false, 'message' => 'Cliente protegido'];
        }

        // PRIMERO: Eliminar TODAS las proformas del cliente
        $stmt = $this->db->prepare("DELETE FROM proformas WHERE cliente_id = ?");
        $stmt->execute([$id]);

        // SEGUNDO: Eliminar foto física si existe
        if (!empty($cliente['foto_url']) && file_exists(__DIR__ . '/../../public/' . $cliente['foto_url'])) {
            unlink(__DIR__ . '/../../public/' . $cliente['foto_url']);
        }

        // TERCERO: Eliminar el cliente
        $this->delete($id);
        return ['success' => true, 'message' => 'Cliente eliminado'];
    }
    
    // Eliminar múltiples (solo no protegidos)
    public function deleteMultiple($ids) {
        $deleted = 0;
        foreach ($ids as $id) {
            $result = $this->deleteIfNotProtected($id);
            if ($result['success']) {
                $deleted++;
            }
        }
        return $deleted;
    }
    
    // Contar clientes
    public function getTotalClientes() {
        return $this->count();
    }
    
    // Buscar clientes
    public function search($term) {
        return $this->query(
            "SELECT * FROM {$this->table} 
             WHERE nombre LIKE ? OR dni_ruc LIKE ? OR telefono LIKE ?
             ORDER BY nombre ASC",
            ["%{$term}%", "%{$term}%", "%{$term}%"]
        );
    }
}