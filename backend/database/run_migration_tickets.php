<?php
/**
 * Script de migración para crear las tablas del sistema de boletos
 * Ejecutar desde línea de comandos: php run_migration_tickets.php
 */

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'boxtiove_db';
$username = 'root';
$password = '';

try {
    // Conectar a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✓ Conexión exitosa a la base de datos\n";
    echo "Ejecutando migración del sistema de boletos...\n\n";

    // Leer el archivo SQL
    $sql = file_get_contents(__DIR__ . '/create_tables_tickets.sql');

    // Ejecutar el SQL
    $pdo->exec($sql);

    echo "✓ Tablas creadas exitosamente:\n";
    echo "  - eventos\n";
    echo "  - tipos_boleto\n";
    echo "  - vendedores\n";
    echo "  - boletos_vendidos\n";
    echo "  - ventas_vendedor\n";
    echo "  - vista_boletos_disponibles (vista)\n";

    echo "\n✓ Migración completada con éxito\n";

} catch (PDOException $e) {
    echo "✗ Error en la migración: " . $e->getMessage() . "\n";
    exit(1);
}
?>
