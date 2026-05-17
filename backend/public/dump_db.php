<?php
require_once __DIR__ . '/../config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $data = [];
    
    // 1. Eventos
    $stmt = $db->query("SELECT id, nombre, estado FROM eventos");
    $data['eventos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 2. Tipos de Boleto
    $stmt = $db->query("SELECT id, evento_id, nombre, precio, activo, cantidad_total, cantidad_vendida FROM tipos_boleto");
    $data['tipos_boleto'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Vista
    try {
        $stmt = $db->query("SELECT * FROM vista_boletos_disponibles");
        $data['vista'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $data['vista_error'] = $e->getMessage();
    }
    
    file_put_contents(__DIR__ . '/db_dump.json', json_encode($data, JSON_PRETTY_PRINT));
    echo "✅ Dump completed to db_dump.json";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
