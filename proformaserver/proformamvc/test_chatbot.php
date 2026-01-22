<?php
// test_chatbot.php

// 1. Simular entorno (Autoloader de public/index.php)
require_once __DIR__ . '/vendor/autoload.php';

spl_autoload_register(function ($class) {
    echo "Autoloading: $class\n";
    $base_dir = __DIR__ . '/'; // Root
    
    // Normalizar namespace
    // App\Config\Chatbot -> app/Config/Chatbot.php
    $parts = explode('\\', $class);
    
    // Mapeo especial para 'App' -> 'app'
    if ($parts[0] === 'App') {
        $parts[0] = 'app';
    } 
    // Mapeo especial para 'Core' -> 'core' (según index.php)
    elseif ($parts[0] === 'Core') {
        $parts[0] = 'core';
    }

    // Convertir todo a ruta
    $file = $base_dir . implode('/', $parts) . '.php';
    
    echo "Looking for: $file\n";

    if (file_exists($file)) {
        require $file;
        echo "Found!\n";
    } else {
        echo "NOT FOUND!\n";
    }
});

// 2. Instanciar Servicio
try {
    echo "Instantiating GeminiService...\n";
    $service = new \App\Services\GeminiService();
    echo "GeminiService Instantiated OK.\n";
    
    // 3. Probar Configuración
    $ref = new ReflectionClass($service);
    $prop = $ref->getProperty('apiKey');
    $prop->setAccessible(true);
    echo "API Key Length: " . strlen($prop->getValue($service)) . "\n";
    
} catch (Throwable $e) {
    echo "\n\nCRITICAL ERROR:\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
