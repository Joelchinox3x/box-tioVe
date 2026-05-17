<?php
require_once __DIR__ . '/config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Expanding metodo_pago ENUM in boletos_vendidos table...\n";
    
    // We change from ENUM to VARCHAR to allow any method defined in the metodos_pago table
    // and avoid "Data truncated" errors in the future.
    $sql = "ALTER TABLE boletos_vendidos MODIFY COLUMN metodo_pago VARCHAR(50) DEFAULT 'yape'";
    
    $db->exec($sql);
    
    echo "✅ Successfully expanded metodo_pago ENUM.\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
