<?php

class ThemeHelper
{
    private static $currentTheme = null;

    /**
     * Obtiene el tema actual del usuario
     * Primero intenta desde la sesión, si no existe usa el default
     */
    public static function getCurrentTheme()
    {
        if (self::$currentTheme !== null) {
            return self::$currentTheme;
        }

        // Obtener de la sesión (ya iniciada en index.php)
        if (isset($_SESSION['app_theme'])) {
            self::$currentTheme = $_SESSION['app_theme'];
        } else {
            // Tema por defecto
            self::$currentTheme = 'corporate';
        }

        return self::$currentTheme;
    }

    /**
     * Cambia el tema del usuario
     */
    public static function setTheme($theme)
    {
        $allowedThemes = self::getAvailableThemes();

        if (!in_array($theme, array_keys($allowedThemes))) {
            return false;
        }

        // La sesión ya está iniciada en index.php
        $_SESSION['app_theme'] = $theme;
        self::$currentTheme = $theme;

        return true;
    }

    /**
     * Obtiene todos los temas disponibles
     */
    public static function getAvailableThemes()
    {
        return [
            'vibrant' => [
                'name' => 'Premiun',
                'description' => 'Colorido con efectos modernos',
                'preview_color' => '#3b82f6',
                'icon' => 'ph-palette'
            ],
            'compact' => [
                'name' => 'Minimalista',
                'description' => 'Diseño minimalista y compacto',
                'preview_color' => '#475569',
                'icon' => 'ph-squares-four'
            ],
            'corporate' => [
                'name' => 'Corporativo',
                'description' => 'Diseño profesional y discreto',
                'preview_color' => '#334155',
                'icon' => 'ph-briefcase'
            ]
        
        ];
    }

    /**
     * Obtiene la ruta de la vista según el tema
     */
    public static function getThemedView($viewName)
    {
        $theme = self::getCurrentTheme();
        $themedPath = APP_PATH . "/Views/{$viewName}_{$theme}.php";
        $defaultPath = APP_PATH . "/Views/{$viewName}.php";

        // Si existe la vista con el tema, usarla
        if (file_exists($themedPath)) {
            return $themedPath;
        }

        // Si no, usar la vista por defecto
        return $defaultPath;
    }

    /**
     * Carga una vista con el tema actual
     */
    public static function loadThemedView($viewName, $data = [])
    {
        $viewPath = self::getThemedView($viewName);

        // Extraer datos para usar en la vista
        extract($data);

        // Incluir la vista
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception("Vista no encontrada: {$viewPath}");
        }
    }
}