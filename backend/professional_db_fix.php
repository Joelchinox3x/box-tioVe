<?php
require_once __DIR__ . '/config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Starting professional DB normalization...\n";
    
    // 1. Ensure metodo_pago is VARCHAR (not ENUM)
    $db->exec("ALTER TABLE boletos_vendidos MODIFY COLUMN metodo_pago VARCHAR(50) DEFAULT 'yape'");
    echo "✅ metodo_pago converted to VARCHAR.\n";
    
    // 2. Add metodo_pago_id column if not exists
    $cols = $db->query("DESCRIBE boletos_vendidos")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('metodo_pago_id', $cols)) {
        $db->exec("ALTER TABLE boletos_vendidos ADD COLUMN metodo_pago_id INT NULL AFTER metodo_pago");
        echo "✅ Column metodo_pago_id added.\n";
    }
    
    // 3. Add foreign key if not exists
    try {
        $db->exec("ALTER TABLE boletos_vendidos ADD CONSTRAINT fk_boleto_metodo_pago 
                   FOREIGN KEY (metodo_pago_id) REFERENCES metodos_pago(id) ON DELETE SET NULL");
        echo "✅ Foreign key fk_boleto_metodo_pago added.\n";
    } catch (Exception $fkEx) {
        echo "ℹ️ Foreign key might already exist: " . $fkEx->getMessage() . "\n";
    }
    
    // 4. Populate metodo_pago_id based on string codes
    $db->exec("
        UPDATE boletos_vendidos bv
        INNER JOIN metodos_pago mp ON bv.metodo_pago = mp.codigo
        SET bv.metodo_pago_id = mp.id
        WHERE bv.metodo_pago_id IS NULL
    ");
    echo "✅ Synced metodo_pago_id with existing string values.\n";
    
    echo "🏁 Normalization completed successfully.\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
