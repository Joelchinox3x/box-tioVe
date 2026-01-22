<?php
// core/helpers.php - Funciones Helper Globales

/**
 * Generar URL correcta con el base path
 */
function url($path = '') {
    $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
    $basePath = rtrim($basePath, '/');
    $path = ltrim($path, '/');

    return $basePath . '/' . $path;
}

/**
 * Generar URL absoluta
 */
function fullUrl($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];

    return $protocol . '://' . $host . url($path);
}

/**
 * Obtener el base path actual
 */
function basePath() {
    $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
    return rtrim($basePath, '/');
}

/**
 * Asset URL (para CSS, JS, imágenes en public)
 */
function asset($path) {
    return basePath() . '/' . ltrim($path, '/');
}
