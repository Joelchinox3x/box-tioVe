<?php
require_once __DIR__ . '/config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "--- TABLE: usuarios ---\n";
    $stmt = $db->query("DESCRIBE usuarios");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "{$col['Field']} - {$col['Type']}\n";
    }

    echo "\n--- TABLE: boletos_vendidos ---\n";
    $stmt = $db->query("DESCRIBE boletos_vendidos");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "{$col['Field']} - {$col['Type']}\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
