<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
    // 1. INICIAR TRANSACCIÓN (Para que todo se guarde o nada se guarde)
    $pdo->beginTransaction();

    // --- A. RECIBIR DATOS BÁSICOS ---
    $nombre      = $_POST['nombre'] ?? 'Sin Nombre';
    $modelo      = $_POST['modelo'] ?? '';
    $sku         = $_POST['sku'] ?? '';
    $precio      = $_POST['precio'] ?? 0;
    $moneda      = $_POST['moneda'] ?? 'PEN';
    $descripcion = $_POST['descripcion'] ?? '';

    // --- B. PROCESAR IMÁGENES (Se guardan como JSON en la tabla productos) ---
    // Si prefieres tabla 'producto_images', avísame. Por ahora asumo columna 'imagenes' en tabla 'productos'.
    $directorio_subida = 'uploads/equipos/';
    if (!is_dir($directorio_subida)) {
        mkdir($directorio_subida, 0777, true);
    }

    $rutas_imagenes = [];
    if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
        $total_archivos = count($_FILES['imagenes']['name']);
        for ($i = 0; $i < $total_archivos; $i++) {
            $nombre_archivo = $_FILES['imagenes']['name'][$i];
            $tmp_name       = $_FILES['imagenes']['tmp_name'][$i];
            $error          = $_FILES['imagenes']['error'][$i];

            if ($error === UPLOAD_ERR_OK) {
                $nuevo_nombre = time() . '_' . rand(100, 999) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $nombre_archivo);
                $ruta_destino = $directorio_subida . $nuevo_nombre;
                if (move_uploaded_file($tmp_name, $ruta_destino)) {
                    $rutas_imagenes[] = $ruta_destino;
                }
            }
        }
    }
    $imagenes_json = json_encode($rutas_imagenes);

    // --- C. INSERTAR EL PRODUCTO PRINCIPAL ---
    // Nota: Eliminé 'attributes' de aquí porque ahora van en su propia tabla.
    $sqlProducto = "INSERT INTO productos (nombre, modelo, sku, precio, moneda, descripcion, imagenes, created_at) 
                    VALUES (:nombre, :modelo, :sku, :precio, :moneda, :desc, :imgs, NOW())";
    
    $stmt = $pdo->prepare($sqlProducto);
    $stmt->execute([
        ':nombre' => $nombre,
        ':modelo' => $modelo,
        ':sku'    => $sku,
        ':precio' => $precio,
        ':moneda' => $moneda,
        ':desc'   => $descripcion,
        ':imgs'   => $imagenes_json
    ]);

    // OBTENER EL ID DEL PRODUCTO RECIÉN CREADO
    $producto_id = $pdo->lastInsertId();

    // --- D. INSERTAR LOS ATRIBUTOS EN 'producto_specs' ---
    if (isset($_POST['specs']) && is_array($_POST['specs'])) {
        
        $sqlSpec = "INSERT INTO producto_specs (producto_id, atributo, valor, orden) 
                    VALUES (:pid, :attr, :val, :orden)";
        $stmtSpec = $pdo->prepare($sqlSpec);

        $orden = 0; // Para mantener el orden visual
        foreach ($_POST['specs'] as $spec) {
            $attr = trim($spec['attr']);
            $val  = trim($spec['val']);

            if (!empty($attr) && !empty($val)) {
                $stmtSpec->execute([
                    ':pid'   => $producto_id,
                    ':attr'  => $attr,
                    ':val'   => $val,
                    ':orden' => $orden
                ]);
                $orden++;
            }
        }
    }

    // 2. CONFIRMAR TRANSACCIÓN
    $pdo->commit();

    // --- E. REDIRECCIONAR ---
    //header('Location: agregar_equipos.php?msg=created');
    header('Location: inventario.php');
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error crítico al guardar: " . $e->getMessage());
} 
} 
    else {
    header("Location: agregar_equipos.php");
    exit;
}
?>