<?php
/**
 * Script para insertar datos de ejemplo en el sistema de boletos
 * Ejecutar después de run_migration_tickets.php
 */

$host = 'localhost';
$dbname = 'boxtiove_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Insertando datos de ejemplo...\n\n";

    // 1. CREAR EVENTO DE EJEMPLO
    $stmt = $pdo->prepare("
        INSERT INTO eventos (nombre, descripcion, fecha, hora, direccion, imagen_banner, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        'El Jab Dorado',
        'Campeonato de Box Amateur - Presenta: El Jab Dorado',
        '2026-02-22',
        '20:00:00',
        'Asoc. Los Llanos - Parque Los Llanos, Santa Clara - Ate Vitarte',
        'https://boxtiove.com/assets/eventos/el-jab-dorado.png',
        'activo'
    ]);

    $eventoId = $pdo->lastInsertId();
    echo "✓ Evento creado: El Jab Dorado (ID: $eventoId)\n";

    // 2. CREAR TIPOS DE BOLETO
    $tiposBoleto = [
        ['General', 5.00, 100, '#FFD700', 'Entrada general al evento'],
        ['VIP', 15.00, 50, '#FF1493', 'Zona VIP + bebida gratis'],
        ['Ringside', 30.00, 30, '#FF4500', 'Primera fila junto al ring'],
        ['Mesa VIP', 100.00, 10, '#8B0000', 'Mesa con 4 personas + servicio de mesero']
    ];

    $stmt = $pdo->prepare("
        INSERT INTO tipos_boleto (evento_id, nombre, precio, cantidad_total, color_hex, descripcion, orden, activo)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    ");

    foreach ($tiposBoleto as $index => $tipo) {
        $stmt->execute([
            $eventoId,
            $tipo[0], // nombre
            $tipo[1], // precio
            $tipo[2], // cantidad_total
            $tipo[3], // color_hex
            $tipo[4], // descripcion
            $index + 1 // orden
        ]);
        echo "✓ Tipo de boleto creado: {$tipo[0]} - S/{$tipo[1]}\n";
    }

    // 3. CREAR VENDEDORES DE EJEMPLO
    $vendedores = [
        ['Tienda "Los Campeones"', 'tienda', 'TIENDA001', '934567890', 'loscampeones@email.com', 10.00],
        ['Tienda "Ring Store"', 'tienda', 'TIENDA002', '987654321', 'ringstore@email.com', 10.00],
        ['Juan Pérez', 'vendedor_individual', 'VEND001', '912345678', 'juan@email.com', 15.00]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO vendedores (nombre, tipo, codigo_vendedor, telefono, email, comision_porcentaje, estado)
        VALUES (?, ?, ?, ?, ?, ?, 'activo')
    ");

    foreach ($vendedores as $vendedor) {
        $stmt->execute($vendedor);
        echo "✓ Vendedor creado: {$vendedor[0]} ({$vendedor[2]})\n";
    }

    echo "\n✓ Datos de ejemplo insertados exitosamente\n";
    echo "\nAhora puedes:\n";
    echo "1. Ver los tipos de boleto disponibles\n";
    echo "2. Crear ventas de prueba\n";
    echo "3. Probar el sistema de QR\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
