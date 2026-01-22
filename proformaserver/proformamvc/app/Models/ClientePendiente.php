<?php
namespace App\Models;

use Core\Model;

class ClientePendiente extends Model {
    protected $table = 'clientes_pendientes';
    
    // Crear lead
    public function createLead($data) {
        return $this->create([
            'nombre' => $data['nombre'],
            'dni_ruc' => $data['dni_ruc'],
            'telefono' => $data['telefono'] ?? null,
            'origen' => $data['origen'] ?? null,
            'estado' => 'pendiente'
        ]);
    }
    
    // Obtener pendientes
    public function getPending() {
        return $this->query(
            "SELECT * FROM {$this->table}
             WHERE estado = 'pendiente'
             ORDER BY
                COALESCE(fecha_modificacion, fecha_creacion) DESC"
        );
    }
    
    // Contar pendientes
    public function countPending() {
        $res = $this->query("SELECT COUNT(*) as total FROM {$this->table} WHERE estado = 'pendiente'");
        return $res[0]['total'] ?? 0;
    }
    
    // Aprobar
    public function markAs($id, $estado) {
        return $this->update($id, ['estado' => $estado]);
    }

    // Actualizar lead
    public function updateLead($id, $data) {
        $updateData = [];

        if (isset($data['nombre'])) {
            $updateData['nombre'] = $data['nombre'];
        }

        if (isset($data['direccion'])) {
            $updateData['direccion'] = $data['direccion'];
        }

        if (empty($updateData)) {
            return false;
        }

        return $this->update($id, $updateData);
    }
}
