<?php
require_once __DIR__ . '/../config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "--- EVENTO PROXIMAMENTE ---\n";
    $stmt = $db->query("SELECT id, nombre, estado FROM eventos WHERE estado = 'proximamente'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "\n--- TIPOS BOLETO (RAW) ---\n";
    $stmt = $db->query("SELECT id, evento_id, nombre, activo FROM tipos_boleto");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "\n--- VISTA BOLETOS DISPONIBLES ---\n";
    $stmt = $db->query("SELECT * FROM vista_boletos_disponibles");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
