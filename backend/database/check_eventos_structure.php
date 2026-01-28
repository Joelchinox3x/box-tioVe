<?php
/**
 * Script para verificar la estructura y datos de la tabla eventos
 */

$host = '127.0.0.1';
$dbname = 'eventobox_db';
$username = 'server_admin';
$password = 'Cocacola123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "========================================\n";
    echo "VERIFICACIÓN DE TABLA EVENTOS\n";
    echo "========================================\n\n";

    // 1. Verificar estructura de la tabla
    echo "1. ESTRUCTURA DE LA TABLA:\n";
    echo "----------------------------\n";
    $stmt = $pdo->query("DESCRIBE eventos");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $hasFecha = false;
    $hasHora = false;
    $hasFechaEvento = false;

    foreach ($columns as $column) {
        echo sprintf("%-25s %-20s %-10s\n",
            $column['Field'],
            $column['Type'],
            $column['Null'] === 'NO' ? 'NOT NULL' : 'NULL'
        );

        if ($column['Field'] === 'fecha') $hasFecha = true;
        if ($column['Field'] === 'hora') $hasHora = true;
        if ($column['Field'] === 'fecha_evento') $hasFechaEvento = true;
    }

    echo "\n";
    echo "2. ANÁLISIS DE CAMPOS:\n";
    echo "----------------------------\n";
    echo "✓ Campo 'fecha' existe: " . ($hasFecha ? "SÍ ✅" : "NO ❌") . "\n";
    echo "✓ Campo 'hora' existe: " . ($hasHora ? "SÍ ✅" : "NO ❌") . "\n";
    echo "✓ Campo 'fecha_evento' existe: " . ($hasFechaEvento ? "SÍ" : "NO") . "\n";

    echo "\n";
    echo "3. DATOS ACTUALES:\n";
    echo "----------------------------\n";
    $stmt = $pdo->query("SELECT id, nombre, fecha_evento, fecha, hora, direccion FROM eventos LIMIT 5");
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($eventos)) {
        echo "⚠️  No hay eventos en la tabla\n";
    } else {
        foreach ($eventos as $evento) {
            echo "ID: {$evento['id']}\n";
            echo "Nombre: {$evento['nombre']}\n";

            if (isset($evento['fecha_evento'])) {
                echo "fecha_evento: {$evento['fecha_evento']}\n";
            }

            if (isset($evento['fecha'])) {
                echo "fecha: {$evento['fecha']}\n";
            } else {
                echo "fecha: [campo no existe]\n";
            }

            if (isset($evento['hora'])) {
                echo "hora: {$evento['hora']}\n";
            } else {
                echo "hora: [campo no existe]\n";
            }

            echo "direccion: " . ($evento['direccion'] ?? '[no existe]') . "\n";
            echo "---\n";
        }
    }

    echo "\n";
    echo "4. RECOMENDACIÓN:\n";
    echo "----------------------------\n";

    if (!$hasFecha && !$hasHora && $hasFechaEvento) {
        echo "⚠️  La tabla usa el formato ANTIGUO (fecha_evento DATETIME)\n";
        echo "📝 Necesitas ejecutar esta migración:\n\n";
        echo "ALTER TABLE eventos \n";
        echo "  ADD COLUMN fecha DATE NOT NULL AFTER descripcion,\n";
        echo "  ADD COLUMN hora TIME NOT NULL AFTER fecha;\n\n";
        echo "UPDATE eventos \n";
        echo "  SET fecha = DATE(fecha_evento),\n";
        echo "      hora = TIME(fecha_evento);\n\n";
        echo "-- Opcional: eliminar columna vieja\n";
        echo "-- ALTER TABLE eventos DROP COLUMN fecha_evento;\n";
    } elseif ($hasFecha && $hasHora) {
        echo "✅ La tabla usa el formato NUEVO (fecha DATE + hora TIME)\n";
        echo "Todo está correcto!\n";
    }

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
