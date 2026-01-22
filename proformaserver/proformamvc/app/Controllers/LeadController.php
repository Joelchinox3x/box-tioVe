<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\ClientePendiente;
use App\Models\Cliente;

class LeadController extends Controller {
    private $leadModel;
    private $clienteModel;

    public function __construct() {
        $this->leadModel = new ClientePendiente();
        $this->clienteModel = new Cliente();
    }

    // API PUBLIC: Guardar nuevo lead
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validación básica
        if (empty($input['nombre']) || empty($input['dni_ruc'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre y documento requeridos']);
            return;
        }

        try {
            $id = $this->leadModel->createLead([
                'nombre' => $input['nombre'],
                'dni_ruc' => $input['dni_ruc'],
                'telefono' => $input['telefono'] ?? null,
                'origen' => $input['origen'] ?? 'Web Public Rest'
            ]);

            if ($id) {
                echo json_encode(['success' => true, 'id' => $id]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al guardar (ID null)']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            error_log("Lead Store Error: " . $e->getMessage());
            echo json_encode(['error' => 'Excepción DB: ' . $e->getMessage()]);
        }
    }

    // ADMIN: Listar pendientes
    public function index() {
        
        $leads = $this->leadModel->getPending();
        $this->view('leads/index', ['leads' => $leads]);
    }

    // ADMIN: Aprobar (Mover a Clientes)
    public function approve() {

        $id = $_POST['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            return;
        }

        // Obtener datos del lead
        $lead = $this->leadModel->find($id);
        if (!$lead) {
            http_response_code(404);
            return;
        }

        // Crear en tabla Clientes
        $clienteId = $this->clienteModel->createCliente([
            'nombre' => $lead['nombre'],
            'dni_ruc' => $lead['dni_ruc'],
            'telefono' => $lead['telefono']
        ]);

        if ($clienteId) {
            // Marcar como aprobado
            $this->leadModel->markAs($id, 'aprobado');
            http_response_code(200);
            exit;
        } else {
            http_response_code(500);
            exit;
        }
    }

    // ADMIN: Rechazar
    public function reject() {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            return;
        }

        $this->leadModel->markAs($id, 'rechazado');
        http_response_code(200);
        exit;
    }

    // ADMIN: Actualizar lead desde consulta DNI/RUC
    public function updateFromQuery() {
        // Limpiar buffer para evitar que warnings/notices rompan el JSON
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        $id = $input['id'] ?? null;
        $nombre = $input['nombre'] ?? null;
        $direccion = $input['direccion'] ?? null;

        if (!$id || !$nombre) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }

        try {
            // Actualizar el lead
            $updated = $this->leadModel->updateLead($id, [
                'nombre' => $nombre,
                'direccion' => $direccion
            ]);

            if ($updated) {
                echo json_encode(['success' => true, 'message' => 'Lead actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el lead']);
            }
        } catch (\Exception $e) {
            error_log("Error al actualizar lead: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()]);
        }

        exit;
    }
}
