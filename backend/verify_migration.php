<?php
/**
 * Script temporal para verificar si las migraciones se ejecutaron
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();

    // Verificar estructura de peleadores
    $stmt = $db->query("DESCRIBE peleadores");
    $peleadores_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar estructura de usuarios
    $stmt = $db->query("DESCRIBE usuarios");
    $usuarios_columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Buscar las columnas específicas
    $genero_existe = false;
    $apellidos_existe = false;

    foreach ($peleadores_columns as $col) {
        if ($col['Field'] === 'genero') {
            $genero_existe = $col;
        }
    }

    foreach ($usuarios_columns as $col) {
        if ($col['Field'] === 'apellidos') {
            $apellidos_existe = $col;
        }
    }

    echo json_encode([
        'success' => true,
        'genero_column' => $genero_existe ?: 'NO EXISTE',
        'apellidos_column' => $apellidos_existe ?: 'NO EXISTE',
        'peleadores_all_columns' => array_column($peleadores_columns, 'Field'),
        'usuarios_all_columns' => array_column($usuarios_columns, 'Field')
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
