<?php
require_once __DIR__ . '/config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $tables = ['usuarios', 'peleadores', 'clubs', 'fighter_cards'];
    echo "--- DATABASE DIAGNOSTIC ---\n";
    
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table' exists.\n";
            
            // Check columns for fighter_cards if it exists
            if ($table === 'fighter_cards') {
                $cols = $db->query("DESCRIBE fighter_cards")->fetchAll(PDO::FETCH_ASSOC);
                echo "   Columns: " . implode(', ', array_column($cols, 'Field')) . "\n";
            }
        } else {
            echo "❌ Table '$table' MISSING!\n";
            
            // Try to create it if it's fighter_cards
            if ($table === 'fighter_cards') {
                echo "   Attempting to create fighter_cards...\n";
                $sql = file_get_contents(__DIR__ . '/database/create_fighter_cards.sql');
                $db->exec($sql);
                echo "   ✅ Created fighter_cards table.\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
