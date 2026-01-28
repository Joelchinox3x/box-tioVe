<?php
require_once __DIR__ . '/../config/Database.php';

$database = new Database();
$db = $database->getConnection();

$sql = file_get_contents(__DIR__ . '/create_table_banners.sql');

try {
    $db->exec($sql);
    echo "Tabla 'banners' creada o verificada correctamente.\n";
} catch (PDOException $e) {
    echo "Error al crear tabla: " . $e->getMessage() . "\n";
}
