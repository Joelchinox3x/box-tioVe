<?php
/**
 * Script de migración para crear la tabla de tarjetas de peleador
 * Ejecutar desde línea de comandos: php run_migration_cards.php
 */

require_once __DIR__ . '/../config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "✓ Conexión exitosa a la base de datos\n";
    echo "Ejecutando migración de la tabla fighter_cards...\n\n";

    // Leer el archivo SQL
    $sql = file_get_contents(__DIR__ . '/create_fighter_cards.sql');
    
    // Remover comando USE si existe para evitar conflictos de nombres de BD
    $sql = preg_replace('/USE\s+\w+;/i', '', $sql);

    // Ejecutar el SQL
    $db->exec($sql);

    echo "✓ Tabla 'fighter_cards' creada exitosamente.\n";
    echo "✓ Migración completada con éxito.\n";

} catch (Exception $e) {
    echo "✗ Error en la migración: " . $e->getMessage() . "\n";
    exit(1);
}
?>
