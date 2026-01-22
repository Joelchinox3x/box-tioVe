<?php
// guardar_proforma.php
require 'vendor/autoload.php'; 
require 'config.php'; 

use App\Services\ProformaService;
use App\Pdf\PdfEngine;
 
$template_elegido = $_POST['design_template'] ?? 'orange';

// Guardar en BD (Agrega el campo design_template en tu INSERT)
$sql = "INSERT INTO proformas (..., design_template) VALUES (..., ?)";
 

try {
    // 1. Inicializar Servicios
    $service = new ProformaService($pdo);
    
    // 2. Procesar Input
    $input = $service->validateInput($_POST);
    $calculation = $service->processItems($input['items']); 
    
    // 3. Guardar en BD
    $proforma_id = $service->saveToDatabase($input['cliente_id'], $calculation['totals'], $calculation['items']);
    
    // 4. Preparar Datos para PDF
    $fullData = $service->getFullData($input['cliente_id'], $calculation['items']);
    $fullData['proforma_id'] = $proforma_id; 
    $fullData['fecha'] = date('Y-m-d');
    // 5. Generar PDF 
  // AL INSTANCIAR EL ENGINE, LE PASAMOS EL TEMPLATE
    $engine = new PdfEngine($template_elegido);
    
    // Crear carpeta si no existe
    if (!is_dir('uploads/pdfs')) mkdir('uploads/pdfs', 0777, true);
    $fileName = "uploads/pdfs/proforma_{$proforma_id}.pdf";
    
    $engine->generate($fullData, $calculation['totals'], $fileName);
    
    // 6. Actualizar ruta en BD
    $service->updatePdfPath($proforma_id, $fileName);

    // 7. Redirigir
    header("Location: proformas.php");
    exit;

} catch (Exception $e) {
    // Tip Pro: Mostrar el error en pantalla para depurar si falla
    die("Error Fatal: " . $e->getMessage());
}