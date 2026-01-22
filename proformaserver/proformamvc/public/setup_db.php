<?php
// public/setup_db.php
// Script de migración web (ya que no hay acceso CLI)

// Configuración básica
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Actualización de Base de Datos</h1>";
echo "<pre>";

$configPath = __DIR__ . '/../config/config.php';

if (!file_exists($configPath)) {
    die("Error: No se encuentra config.php");
}

$config = require $configPath;
$dbConfig = $config['db'];

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Conectado a la BD.\n";
    
    // 1. Agregar Columna Token
    // Verificar si existe
    $stmt = $pdo->prepare("SHOW COLUMNS FROM productos LIKE 'token'");
    $stmt->execute();
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo "Agregando columna 'token'...\n";
        $pdo->exec("ALTER TABLE productos ADD COLUMN token VARCHAR(64) NULL DEFAULT NULL AFTER id");
        $pdo->exec("ALTER TABLE productos ADD UNIQUE INDEX idx_token (token)");
        echo "Columna agregada.\n";
    } else {
        echo "La columna 'token' ya existe.\n";
    }
    
    // 2. Poblar tokens vacíos
    $stmt = $pdo->query("SELECT id FROM productos WHERE token IS NULL OR token = ''");
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($ids) > 0) {
        echo "Generando tokens para " . count($ids) . " productos...\n";
        $updateStmt = $pdo->prepare("UPDATE productos SET token = ? WHERE id = ?");
        
        foreach ($ids as $id) {
            $token = bin2hex(random_bytes(16));
            $updateStmt->execute([$token, $id]);
        }
        echo "Tokens generados.\n";
    } else {
        echo "Todos los productos ya tienen token.\n";
    }
    
    // 3. Crear tabla clientes_pendientes
    $pdo->exec("CREATE TABLE IF NOT EXISTS clientes_pendientes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        dni_ruc VARCHAR(20) NOT NULL,
        telefono VARCHAR(50) NULL,
        origen VARCHAR(255) NULL,
        estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Tabla 'clientes_pendientes' verificada.\n";
    
    echo "\n\nLISTO! Base de datos actualizada.";
    
} catch (Exception $e) {
    echo "ERROR CRÍTICO: " . $e->getMessage();
}

echo "</pre>";
?>
