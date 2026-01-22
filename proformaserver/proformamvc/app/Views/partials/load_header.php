<?php
/**
 * CARGADOR DINÁMICO DE HEADER
 * Carga el header según la preferencia guardada en sesión
 * NOTA: La sesión ya está iniciada en index.php
 */

// Obtener el header preferido del usuario
$header_style = $_SESSION['header_style'] ?? 'header';

// Validar que sea un header válido
$valid_headers = ['header', 'header_v2', 'header_v3'];
if (!in_array($header_style, $valid_headers)) {
    $header_style = 'header'; // Fallback al default
}

// Construir la ruta del header
$header_file = __DIR__ . "/{$header_style}.php";

// Verificar que el archivo existe
if (file_exists($header_file)) {
    include $header_file;
} else {
    // Si no existe, cargar el header por defecto
    include __DIR__ . '/header.php';
}
