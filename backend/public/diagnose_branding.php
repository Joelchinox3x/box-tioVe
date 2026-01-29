<?php
require_once __DIR__ . '/../config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h3>Check Database</h3>";
    
    $stmt = $db->query("SHOW TABLES LIKE 'logos'");
    $tableExists = $stmt->rowCount() > 0;
    echo "Table 'logos' exists: " . ($tableExists ? "YES" : "NO") . "<br>";
    
    if (!$tableExists) {
        echo "Creating table 'logos'...<br>";
        $sql = "CREATE TABLE `logos` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `nombre_archivo` VARCHAR(255) NOT NULL,
            `tipo_mime` VARCHAR(50) DEFAULT NULL,
            `tipo` VARCHAR(20) NOT NULL,
            `etiqueta` VARCHAR(100) DEFAULT NULL,
            `activo` BOOLEAN DEFAULT 0,
            `dimensiones` VARCHAR(20) DEFAULT NULL,
            `peso` INT DEFAULT NULL,
            `config_json` TEXT DEFAULT NULL,
            `fecha_subida` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $db->exec($sql);
        echo "Table 'logos' created successfully.<br>";
    } else {
        $stmt = $db->query("DESCRIBE logos");
        echo "<pre>";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM logos");
        echo "Total records in 'logos': " . $stmt->fetch(PDO::FETCH_ASSOC)['total'] . "<br>";
        
        $stmt = $db->query("SELECT * FROM logos WHERE activo = 1");
        echo "Active logos:<br>";
        echo "<pre>";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
    }
    
    echo "<h3>Check Filesystem</h3>";
    $uploadDir = __DIR__ . '/uploads/branding/';
    echo "Upload dir: $uploadDir<br>";
    echo "Exists: " . (file_exists($uploadDir) ? "YES" : "NO") . "<br>";
    if (file_exists($uploadDir)) {
        echo "Writable: " . (is_writable($uploadDir) ? "YES" : "NO") . "<br>";
        echo "Files:<br>";
        echo "<pre>";
        print_r(scandir($uploadDir));
        echo "</pre>";
    } else {
        echo "Creating dir...<br>";
        if (mkdir($uploadDir, 0777, true)) {
            echo "Dir created successfully.<br>";
        } else {
            echo "Failed to create dir.<br>";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
