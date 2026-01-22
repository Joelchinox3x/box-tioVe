<?php
// app/Controllers/ClienteController.php

namespace App\Controllers;

use Core\Controller;
use App\Models\Cliente;
use App\Services\ImageService;
use App\Helpers\SettingsHelper;

class ClienteController extends Controller {
    private $clienteModel;
    private $imageService;

    public function __construct() {
        $this->clienteModel = new Cliente();
        $this->imageService = new ImageService();

        // Cargar helper de configuración
        require_once __DIR__ . '/../Helpers/SettingsHelper.php';
        
    }
    
    // Listar todos los clientes
    public function index() {
        $clientes = $this->clienteModel->getAllOrdered();
        $total = $this->clienteModel->getTotalClientes();
        
        $this->view('clientes/index', [
            'clientes' => $clientes,
            'total_clientes' => $total,
            'mensaje' => $this->get('msg')
        ]);
    }
    
    // Mostrar formulario de creación
    public function create() {
        $this->view('clientes/create');
    }
    
    // Guardar nuevo cliente
    public function store() {
        // Validar datos
        $errors = $this->validate($this->post(), [
            'nombre' => 'required|min:4',
            'dni_ruc' => 'required'
        ]);
        
        if (!empty($errors)) {
            return $this->redirect('/clientes/create', [
                'error' => 'Por favor completa los campos requeridos'
            ]);
        }
        
        $data = [
            'nombre' => $this->post('nombre'),
            'dni_ruc' => $this->post('dni_ruc'),
            'direccion' => $this->post('direccion'),
            'telefono' => $this->post('telefono_full'),
            'email' => $this->post('email'),
            'latitud' => $this->post('latitud'),
            'longitud' => $this->post('longitud'),
            'protegido' => $this->post('protegido') ? 1 : 0
        ];
        
        // Procesar foto si existe
        if ($this->post('foto_base64')) {
            $data['foto_url'] = $this->imageService->saveBase64Image(
                $this->post('foto_base64'),
                'clientes',
                $data['nombre']
            );
        }
        
        try {
            $this->clienteModel->createCliente($data);
            $this->redirect('/clientes', ['msg' => 'created']);
        } catch (\Exception $e) {
            $this->redirect('/clientes/create', ['error' => $e->getMessage()]);
        }
    }
    
    // Mostrar formulario de edición
    public function edit($id) {
        $cliente = $this->clienteModel->find($id);
        
        if (!$cliente) {
            $this->redirect('/clientes', ['msg' => 'not_found']);
        }
        
        $this->view('clientes/edit', [
            'cliente' => $cliente
        ]);
    }
    
    // Actualizar cliente
    public function update($id) {
        $cliente = $this->clienteModel->find($id);
        
        if (!$cliente) {
            return $this->json(['error' => 'Cliente no encontrado'], 404);
        }
        
        
        // Verificar si está intentando desbloquear un cliente protegido
        $protegidoActual = (bool)$cliente['protegido'];
        $protegidoNuevo = $this->post('protegido') ? true : false;

        // Si está desbloqueando (estaba protegido y ahora no)
        if ($protegidoActual && !$protegidoNuevo) {
            $adminPin = $this->post('admin_pin');

            // Obtener PIN desde la base de datos
            $correctPin = SettingsHelper::getPinCode();

            // Validar PIN
            if ($adminPin !== $correctPin) {
                $this->redirect("/clientes/edit/{$id}", ['error' => 'PIN incorrecto']);
                return;
            }
        }

        $data = [
            'nombre' => $this->post('nombre'),
            'dni_ruc' => $this->post('dni_ruc'),
            'direccion' => $this->post('direccion'),
            'telefono' => $this->post('telefono_full'),
            'email' => $this->post('email'),
            'latitud' => $this->post('latitud'),
            'longitud' => $this->post('longitud'),
            'protegido' => $protegidoNuevo ? 1 : 0
        ];
        
        // Manejar foto
        $fotoActual = $cliente['foto_url'];
        
        // Si piden eliminar foto
        if ($this->post('eliminar_foto') == '1') {
            if ($fotoActual && file_exists(__DIR__ . '/../../public/' . $fotoActual)) {
                unlink(__DIR__ . '/../../public/' . $fotoActual);
            }
            $data['foto_url'] = null;
        }
        // Si suben nueva foto
        else if ($this->post('foto_base64')) {
            // Eliminar foto anterior
            if ($fotoActual && file_exists(__DIR__ . '/../../public/' . $fotoActual)) {
                unlink(__DIR__ . '/../../public/' . $fotoActual);
            }
            
            $data['foto_url'] = $this->imageService->saveBase64Image(
                $this->post('foto_base64'),
                'clientes',
                $data['nombre']
            );
        } else {
            // Mantener foto actual
            $data['foto_url'] = $fotoActual;
        }
        
        try {
            $this->clienteModel->updateCliente($id, $data);
            $this->redirect('/clientes', ['msg' => 'updated']);
        } catch (\Exception $e) {
            $this->redirect("/clientes/edit/{$id}", ['error' => $e->getMessage()]);
        }
    }
    
    

    // Eliminar cliente
    public function delete($id) {
        $result = $this->clienteModel->deleteIfNotProtected($id);

        if ($result['success']) {
            $this->redirect('/clientes', ['msg' => 'deleted']);
        } else {
            $this->redirect('/clientes', ['msg' => 'locked']);
        }
    }
    
    // Eliminar múltiples
    public function deleteMultiple() {
        $ids = $this->post('ids', []);
        
        if (empty($ids)) {
            return $this->redirect('/clientes');
        }
        
        $deleted = $this->clienteModel->deleteMultiple($ids);
        
        $this->redirect('/clientes', [
            'msg' => 'deleted_multiple',
            'count' => $deleted
        ]);
    }
    
    // Buscar clientes (AJAX)
    public function search() {
        $term = $this->get('q', '');
        $clientes = $this->clienteModel->search($term);
        
        return $this->json($clientes);
    }

        // Verificar PIN de seguridad (AJAX)
    public function verificarPin() {
        // Establecer header JSON
        header('Content-Type: application/json');

        // Obtener el JSON del body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $pin = $data['pin'] ?? '';

        // Obtener PIN desde la base de datos
        $correctPin = SettingsHelper::getPinCode();

        $valid = ($pin === $correctPin);

        // Devolver respuesta JSON
        echo json_encode([
            'valid' => $valid
        ]);
        exit;
    }

    /**
     * Consultar DNI/RUC - Router que delega a los servicios de API
     */
    public function consultarDni() {
        header('Content-Type: application/json');

        // Verificar si la búsqueda está habilitada
        if (!SettingsHelper::isDniSearchEnabled()) {
            echo json_encode([
                'success' => false,
                'message' => 'Búsqueda de DNI/RUC deshabilitada'
            ]);
            exit;
        }

        // Obtener documento del request
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $documento = $data['dni'] ?? '';

        if (empty($documento)) {
            echo json_encode([
                'success' => false,
                'message' => 'Documento no proporcionado'
            ]);
            exit;
        }

        // Obtener proveedor configurado
        $provider = SettingsHelper::getApiProvider();

        // Delegar a los servicios según el proveedor
        if ($provider === 'apiperu') {
            $service = new \App\Services\ApiDniRuc\ApiPeruService();
            echo $service->consultar($documento);
        } elseif ($provider === 'decolecta') {
            $service = new \App\Services\ApiDniRuc\DecolectaService();
            echo $service->consultar($documento);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Proveedor de API no configurado'
            ]);
        }

        exit;
    }

    // ========================================
    // NOTA: Los métodos consultarDniApiPeru() y consultarDniDecolecta()
    // fueron movidos a app/Services/ApiDniRuc/
    // - ApiPeruService.php
    // - DecolectaService.php
    // ========================================
    // Actualizar solo el teléfono (AJAX)
    public function updatePhone() {
        header('Content-Type: application/json');

        $id = $this->post('id');
        $telefono = $this->post('telefono');

        if (!$id || !$telefono) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos']);
            exit;
        }

        try {
            // Reusamos updateCliente del modelo pero solo con teléfono
            // Como el modelo seguro espera todo, mejor hacemos una consulta directa o un método helper
            // Pero para ser limpios, obtengamos el cliente actual y actualicemos solo teléfono
            $cliente = $this->clienteModel->find($id);
            if (!$cliente) {
                echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
                exit;
            }

            // Datos existentes
            $data = [
                'nombre' => $cliente['nombre'],
                'dni_ruc' => $cliente['dni_ruc'],
                'direccion' => $cliente['direccion'] ?? '',
                'telefono' => $telefono, // UPDATE
                'email' => $cliente['email'] ?? '',
                'latitud' => !empty($cliente['latitud']) ? $cliente['latitud'] : null,
                'longitud' => !empty($cliente['longitud']) ? $cliente['longitud'] : null,
                'protegido' => $cliente['protegido'] ?? 0,
                'foto_url' => $cliente['foto_url']
            ];

            // NOTA: El modelo suele requerir ciertos campos, para evitar borrar datos,
            // lo ideal sería tener un método updatePhone en el modelo.
            // Por ahora usaremos updateCliente pasando todos los datos anteriores.
            
            $this->clienteModel->updateCliente($id, $data);

            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ========================================
    // IMPORTACIÓN / EXPORTACIÓN
    // ========================================

    // Exportar todos los clientes a JSON
    public function export() {
        $clientes = $this->clienteModel->getAllOrdered(); // Obtiene todos
        
        // Limpiamos datos sensibles o innecesarios si fuera el caso
        // En este caso exportamos todo para poder restaurar
        
        $filename = 'clientes_backup_' . date('Y-m-d_H-i') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo json_encode($clientes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Importar clientes desde JSON
    public function import() {
        header('Content-Type: application/json');
        
        $json = file_get_contents('php://input');
        $clientes = json_decode($json, true);
        
        if (!is_array($clientes)) {
            echo json_encode(['success' => false, 'message' => 'Formato JSON inválido']);
            exit;
        }

        $countCreated = 0;
        $countUpdated = 0;
        $errors = 0;

        foreach ($clientes as $c) {
            try {
                // Mínima validación
                if (empty($c['nombre'])) continue;

                // Datos a guardar
                $data = [
                    'nombre'    => $c['nombre'],
                    'dni_ruc'   => $c['dni_ruc'] ?? '',
                    'direccion' => $c['direccion'] ?? '',
                    'telefono'  => $c['telefono'] ?? '',
                    'email'     => $c['email'] ?? '',
                    'latitud'   => $c['latitud'] ?? null,
                    'longitud'  => $c['longitud'] ?? null,
                    'protegido' => isset($c['protegido']) ? $c['protegido'] : 0,
                    'foto_url'  => $c['foto_url'] ?? null
                ];

                // Verificar si existe por ID
                $existe = false;
                if (!empty($c['id'])) {
                    $existingClient = $this->clienteModel->find($c['id']);
                    if ($existingClient) {
                        $existe = true;
                    }
                }

                if ($existe) {
                    $this->clienteModel->updateCliente($c['id'], $data);
                    $countUpdated++;
                } else {
                    $this->clienteModel->createCliente($data);
                    $countCreated++;
                }

            } catch (\Exception $e) {
                $errors++;
            }
        }

        echo json_encode([
            'success' => true,
            'summary' => "Importados: $countCreated nuevos, $countUpdated actualizados. Errores: $errors"
        ]);
        exit;
    }
}
