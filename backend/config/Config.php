<?php
/**
 * Configuración centralizada de URLs y dominios
 *
 * Para cambiar el dominio en el futuro, solo modifica la variable BASE_URL
 */
class Config {
    // URL base del dominio (sin trailing slash)
    const BASE_URL = 'https://boxtiove.com';

    // URL de la API
    const API_URL = self::BASE_URL . '/api';

    // Rutas de archivos públicos
    const STORAGE_PATH = self::BASE_URL . '/storage/';
    const FILES_PATH = self::BASE_URL . '/files/';
    const UPLOADS_PATH = self::BASE_URL . '/uploads/';
    const ASSETS_PATH = self::BASE_URL . '/assets/';

    // Rutas específicas
    const PELEADORES_FILES_PATH = self::FILES_PATH . 'peleadores/';
    const CARDS_PATH = self::FILES_PATH . 'cards/';
    const COMPROBANTES_PATH = self::FILES_PATH . 'comprobantes/';
    const EVENTOS_ASSETS_PATH = self::ASSETS_PATH . 'eventos/';
    const ANUNCIOS_FILES_PATH = self::FILES_PATH . 'anuncios/';

    /**
     * Obtener la URL base
     */
    public static function getBaseUrl() {
        return self::BASE_URL;
    }

    /**
     * Obtener la URL de la API
     */
    public static function getApiUrl() {
        return self::API_URL;
    }

    /**
     * Obtener la URL completa de un archivo
     */
    public static function getFileUrl($path, $type = 'storage') {
        switch ($type) {
            case 'peleadores':
                return self::PELEADORES_FILES_PATH . $path;
            case 'cards':
                return self::CARDS_PATH . $path;
            case 'comprobantes':
                return self::COMPROBANTES_PATH . $path;
            case 'anuncios':
                return self::ANUNCIOS_FILES_PATH . $path;
            case 'eventos':
                return self::EVENTOS_ASSETS_PATH . $path;
            case 'files':
                return self::FILES_PATH . $path;
            case 'uploads':
                return self::UPLOADS_PATH . $path;
            case 'assets':
                return self::ASSETS_PATH . $path;
            default:
                return self::STORAGE_PATH . $path;
        }
    }
}
