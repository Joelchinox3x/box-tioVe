<?php
// app/Middleware/AuthMiddleware.php

namespace App\Middleware;

class AuthMiddleware {
    
    public static function check() {
        // La sesión ya está iniciada en index.php, solo verificar autenticación
        if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
            // Redirigir al login
            header('Location: ' . url('/login'));
            exit;
        }
    }

    public static function isGuest() {
        // La sesión ya está iniciada en index.php, verificar si NO está autenticado
        if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
            // Ya está autenticado, redirigir al home
            header('Location: ' . url('/'));
            exit;
        }
    }
}
