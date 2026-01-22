<?php
// app/Controllers/HomeController.php

namespace App\Controllers;

use Core\Controller;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\ClientePendiente;

class HomeController extends Controller {

    public function index() {
        // La sesión ya está iniciada en index.php

        // Nota: Las configuraciones ahora se leen desde la base de datos
        // usando SettingsHelper en las vistas que las necesiten

        // Obtener estadísticas para el dashboard
        try {
            $clienteModel = new Cliente();
            $productoModel = new Producto();
            $proformaModel = new Proforma();
            $pendienteModel = new ClientePendiente();

            $stats = [
                'total_clientes' => $clienteModel->getTotalClientes(),
                'total_productos' => $productoModel->getTotalProductos(),
                'total_proformas' => $proformaModel->getTotalProformas(),
                'total_pendientes' => $pendienteModel->countPending(),
                'proformas_recientes' => $proformaModel->getRecientes(3)
            ];

            // Obtener productos para el carrusel (Todos)
            // Se asume que getAllOrdered devuelve los más recientes primero
            $productos = $productoModel->getAllOrdered();
            $productos_carousel = $productos;

            // Cargar la vista (el index.php se encarga de detectar el tema)
            $this->view("home/index", [
                'stats' => $stats,
                'productos_carousel' => $productos_carousel,
                'mensaje' => $this->get('msg')
            ]);

        } catch (\Exception $e) {
            // Si hay error (por ejemplo, tablas no existen), mostrar página simple
            $this->view("home/index", [
                'stats' => [
                    'total_clientes' => 0,
                    'total_productos' => 0,
                    'total_proformas' => 0,
                    'proformas_recientes' => []
                ],
                'error' => $e->getMessage()
            ]);
        }
    }
}
