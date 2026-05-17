<?php
require_once __DIR__ . '/config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $tables = ['boletos_vendidos', 'tipos_boleto'];
    echo "--- TICKETS DATABASE DIAGNOSTIC ---\n";
    
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists.\n";
            $cols = $db->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
            echo "   Columns: " . implode(', ', array_column($cols, 'Field')) . "\n";
        } else {
            echo "❌ Table '$table' MISSING!\n";
        }
    }
    
    // Check for last orders
    $stmt = $db->query("SELECT id, comprador_nombres_apellidos, precio_total FROM boletos_vendidos ORDER BY id DESC LIMIT 5");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\n--- LAST 5 ORDERS ---\n";
    print_r($orders);

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
