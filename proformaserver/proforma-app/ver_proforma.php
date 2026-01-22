<?php
// ver_proforma.php
require 'config.php';

// 1. Validar ID
$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    die("Error: ID de proforma inválido.");
}

try {
    // 2. Buscar la ruta del PDF en la Base de Datos
    $stmt = $pdo->prepare("SELECT pdf_path FROM proformas WHERE id = ?");
    $stmt->execute([$id]);
    $proforma = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Validaciones
    if (!$proforma) {
        die("Error: No se encontró la proforma en la base de datos.");
    }

    $ruta_archivo = $proforma['pdf_path'];

    // Verificar que el archivo físico exista en la carpeta 'pdfs/'
    if (empty($ruta_archivo) || !file_exists($ruta_archivo)) {
        echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
        echo "<h2 style='color:red;'>⚠️ Archivo no encontrado</h2>";
        echo "<p>El registro existe, pero el archivo PDF físico no está en: <code>$ruta_archivo</code></p>";
        echo "<p>Asegúrate de que la carpeta <b>pdfs/</b> tenga permisos de escritura.</p>";
        echo "<a href='proformas.php'>Volver al listado</a>";
        echo "</div>";
        exit;
    }

    // 4. Mostrar el PDF en el navegador
    // Usamos 'Content-Disposition: inline' para que se abra ahí mismo y no se descargue forzosamente
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="Proforma_' . $id . '.pdf"');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($ruta_archivo));
    header('Accept-Ranges: bytes');

    @readfile($ruta_archivo);

} catch (PDOException $e) {
    die("Error de Base de Datos: " . $e->getMessage());
}
?>