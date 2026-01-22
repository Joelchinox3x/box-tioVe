<?php
// public/save_lead.php
// Script directo para guardar leads evitando problemas de enrutamiento

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    // 1. Cargar Configuración
    $configPath = __DIR__ . '/../config/config.php';
    if (!file_exists($configPath)) {
        throw new Exception("Config no encontrado");
    }
    $config = require $configPath;
    $db = $config['db'];

    // 2. Conectar BD
    $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}";
    $pdo = new PDO($dsn, $db['user'], $db['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 3. Leer Input JSON
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Method not allowed");
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['nombre']) || empty($input['dni_ruc'])) {
         throw new Exception("Datos incompletos");
    }

    // 4. Insertar
    $stmt = $pdo->prepare("INSERT INTO clientes_pendientes (nombre, dni_ruc, origen, estado, fecha_creacion) VALUES (?, ?, ?, 'pendiente', NOW())");
    $stmt->execute([
        $input['nombre'],
        $input['dni_ruc'],
        $input['origen'] ?? 'Web Public Direct'
    ]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'msg' => 'Guardado directo']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
