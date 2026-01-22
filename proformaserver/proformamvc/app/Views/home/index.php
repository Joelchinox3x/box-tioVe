<?php
/**
 * Vista principal del Home con sistema de temas
 * Este archivo detecta el tema actual y carga la vista correspondiente
 */

// Cargar el helper de temas
require_once __DIR__ . '/../../Helpers/ThemeHelper.php';

// Obtener el tema actual
$currentTheme = ThemeHelper::getCurrentTheme();

// Determinar qué vista cargar
$themedViewPath = __DIR__ . "/index_{$currentTheme}.php";

// Si la vista del tema existe, cargarla
if (file_exists($themedViewPath)) {
    include $themedViewPath;
} else {
    // Si no existe, cargar el tema corporativo por defecto
    include __DIR__ . '/index_corporate.php';
}

// Inyectar Widget de Chatbot
include __DIR__ . '/../partials/chatbot_widget.php';