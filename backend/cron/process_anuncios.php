<?php
/**
 * Cron Job: Procesar anuncios programados y expirados
 * Ejecutar cada minuto: * * * * * docker exec mi_php_backend php /var/www/html/cron/process_anuncios.php
 */
require_once __DIR__ . '/../config/Database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // 1. Activar anuncios programados cuya fecha de publicacion ya llego
    $query = "UPDATE anuncios
              SET activo = 1
              WHERE activo = 0
              AND fecha_publicacion IS NOT NULL
              AND fecha_publicacion <= NOW()
              AND (fecha_expiracion IS NULL OR fecha_expiracion > NOW())";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $activated = $stmt->rowCount();

    // 2. Desactivar anuncios expirados
    $query = "UPDATE anuncios
              SET activo = 0
              WHERE activo = 1
              AND fecha_expiracion IS NOT NULL
              AND fecha_expiracion <= NOW()";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $deactivated = $stmt->rowCount();

    // Log solo si hubo cambios
    if ($activated > 0 || $deactivated > 0) {
        $log = "[" . date('Y-m-d H:i:s') . "] Cron anuncios: $activated activados, $deactivated desactivados\n";
        file_put_contents(__DIR__ . '/../files/cron_anuncios.log', $log, FILE_APPEND);
    }

} catch (PDOException $e) {
    error_log("Cron anuncios error: " . $e->getMessage());
}
