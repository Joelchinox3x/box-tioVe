<?php
// tools/migration_token.php

define('BASE_PATH', __DIR__ . '/../');

// Copiamos lógica simple de conexión para no depender de todo el framework si no carga bien
$config = require BASE_PATH . 'config/config.php';
$dbConfig = $config['db'];

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conectado a la base de datos...\n";
    
    // 1. Agregar columna token
    try {
        $pdo->exec("ALTER TABLE productos ADD COLUMN token VARCHAR(64) NULL DEFAULT NULL AFTER id");
        echo "Columna 'token' agregada exitosamente.\n";
        
        // Agregar índice
        $pdo->exec("ALTER TABLE productos ADD UNIQUE INDEX idx_token (token)");
        echo "Índice 'idx_token' agregado.\n";
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "La columna 'token' ya existe.\n";
        } else {
            throw $e;
        }
    }
    
    // 2. Poblar tokens faltantes
    echo "Poblando tokens para productos existentes...\n";
    
    $stmt = $pdo->query("SELECT id FROM productos WHERE token IS NULL OR token = ''");
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $updated = 0;
    $updateStmt = $pdo->prepare("UPDATE productos SET token = ? WHERE id = ?");
    
    foreach ($ids as $id) {
        // Generar token seguro (32 chars)
        $token = bin2hex(random_bytes(16));
        $updateStmt->execute([$token, $id]);
        $updated++;
    }
    
    echo "Actualizados $updated productos con nuevos tokens.\n";
    echo "Migración completada.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
