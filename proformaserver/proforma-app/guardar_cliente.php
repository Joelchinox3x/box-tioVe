<?php
// guardar_cliente.php - PROCESADOR DE DATOS
require 'config.php';

// --- FUNCIÓN AUXILIAR: GUARDAR IMAGEN ---
function guardarImagenBase64($base64_string, $nombre_cliente) {
    if (empty($base64_string)) return null;
    $target_dir = "uploads/clientes/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $data = explode(',', $base64_string);
    $content = base64_decode(count($data) > 1 ? $data[1] : $data[0]);
    
    $image = imagecreatefromstring($content);
    if (!$image) return null;
    
    $clean_name = preg_replace('/[^a-zA-Z0-9]/', '', substr($nombre_cliente, 0, 10));
    $filename = strtolower($clean_name) . '_' . time() . '.webp';
    $filepath = $target_dir . $filename;
    
    imagewebp($image, $filepath, 80);
    imagedestroy($image);
    return $filepath;
}

// --- CASO 1: ELIMINAR CLIENTE INDIVIDUAL ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        // 1. Verificar protección
        $stmt = $pdo->prepare("SELECT protegido, foto_url FROM clientes WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cliente && $cliente['protegido'] == 1) {
            header("Location: clientes.php?msg=locked");
            exit;
        }

        // 2. Borrar foto física
        if ($cliente && !empty($cliente['foto_url']) && file_exists($cliente['foto_url'])) { 
            unlink($cliente['foto_url']); 
        }

        // 3. Borrar de BD
        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        
        header("Location: clientes.php?msg=deleted");
        exit;

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

// --- CASO 2: BORRADO MASIVO ---
if (isset($_POST['action']) && $_POST['action'] == 'delete_multiple' && !empty($_POST['ids'])) {
    try {
        $ids = implode(',', array_map('intval', $_POST['ids'])); 
        // Solo borramos los NO protegidos
        $pdo->query("DELETE FROM clientes WHERE id IN ($ids) AND protegido = 0");
        
        header("Location: clientes.php?msg=deleted_multiple");
        exit;
    } catch (Exception $e) {
        die("Error masivo: " . $e->getMessage());
    }
}

// --- CASO 3: GUARDAR NUEVO CLIENTE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'crear') {
    try {
        $foto_path = null;
        if (!empty($_POST['foto_base64'])) {
            $foto_path = guardarImagenBase64($_POST['foto_base64'], $_POST['nombre']);
        }

        $es_protegido = isset($_POST['protegido']) ? 1 : 0;

        $sql = "INSERT INTO clientes (nombre, dni_ruc, direccion, telefono, email, foto_url, protegido, fecha_modificacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['nombre'],
            $_POST['dni_ruc'],
            $_POST['direccion'],
            $_POST['telefono_full'], // Usamos el número completo procesado por JS
            $_POST['email'],
            $foto_path,
            $es_protegido
        ]);

        header("Location: clientes.php?msg=created");
        exit;

    } catch (Exception $e) {
        // En caso de error, podrías redirigir con el mensaje
        header("Location: agregar_cliente.php?error=" . urlencode($e->getMessage()));
        exit;
    }
}

// Si llegan aquí sin acción, regresar al inicio
header("Location: clientes.php");
exit;
?>