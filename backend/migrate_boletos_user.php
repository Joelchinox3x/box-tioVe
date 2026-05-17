<?php
require_once __DIR__ . '/config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "--- MIGRANDO boletos_vendidos ---\n";
    
    // 1. Verificar si la columna ya existe
    $stmt = $db->query("DESCRIBE boletos_vendidos");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('usuario_id', $columns)) {
        echo "✅ La columna usuario_id ya existe.\n";
    } else {
        // 2. Agregar columna
        $sql = "ALTER TABLE boletos_vendidos ADD COLUMN usuario_id INT DEFAULT NULL AFTER vendedor_id";
        $db->exec($sql);
        echo "✅ Columna usuario_id agregada correctamente.\n";
        
        // 3. Agregar Foreign Key (opcional pero recomendada)
        try {
            $sqlFK = "ALTER TABLE boletos_vendidos ADD CONSTRAINT fk_boleto_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL";
            $db->exec($sqlFK);
            echo "✅ Foreign Key creada.\n";
        } catch (Exception $ex) {
            echo "⚠️ No se pudo crear FK (quizás ya existe o error de datos): " . $ex->getMessage() . "\n";
        }
    }
    
    echo "--- ESTRUCTURA ACTUALIZADA ---\n";
    $stmt = $db->query("DESCRIBE boletos_vendidos");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "{$col['Field']} - {$col['Type']}\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
