<?php
// app/Helpers/SettingsHelper.php - Helper para acceder a configuraciones

namespace App\Helpers;

use App\Models\Setting;

class SettingsHelper {
    private static $cache = null;
    private static $settingModel = null;

    /**
     * Obtiene el modelo de configuración (singleton)
     */
    private static function getModel() {
        if (self::$settingModel === null) {
            self::$settingModel = new Setting();
        }
        return self::$settingModel;
    }

    /**
     * Carga todas las configuraciones en cache
     */
    private static function loadCache() {
        if (self::$cache === null) {
            self::$cache = self::getModel()->getAll();
        }
    }

    /**
     * Obtiene una configuración
     * @param string $key Clave de configuración
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public static function get($key, $default = null) {
        self::loadCache();
        return self::$cache[$key] ?? $default;
    }

    /**
     * Obtiene el valor de IGV como porcentaje
     * @return float
     */
    public static function getIgvPercent() {
        return floatval(self::get('igv_percent', 18));
    }

    /**
     * Verifica si los precios incluyen IGV
     * @return bool
     */
    public static function pricesIncludeIgv() {
        return self::get('prices_include_igv', '1') === '1';
    }

    /**
     * Obtiene el nombre de la aplicación
     * @return string
     */
    public static function getAppName() {
        return self::get('app_name', 'Tradimacova');
    }

    /**
     * Obtiene la URL del logo
     * @return string
     */
    public static function getAppLogo() {
        return self::get('app_logo', '');
    }

    /**
     * Obtiene el nombre del Manager
     * @return string
     */
    public static function getManagerName() {
        return self::get('manager_name', '');
    }

    /**
     * Obtiene el WhatsApp del Manager
     * @return string
     */
    public static function getManagerWhatsapp() {
        return self::get('manager_whatsapp', '');
    }

    /**
     * Verifica si el GPS está habilitado
     * @return bool
     */
    public static function isGpsEnabled() {
        return self::get('enable_gps', '0') === '1';
    }

    /**
     * Obtiene el PIN de seguridad
     * @return string
     */
    public static function getPinCode() {
        return self::get('pin_code', '1234');
    }

    /**
     * Verifica si se debe mostrar el header
     * @return bool
     */
    public static function showHeader() {
        return self::get('show_header', '1') === '1';
    }

    /**
     * Verifica si se debe mostrar el navbar
     * @return bool
     */
    public static function showNavbar() {
        return self::get('show_navbar', '1') === '1';
    }

    /**
     * Verifica si la búsqueda DNI/RUC está habilitada
     * @return bool
     */
    public static function isDniSearchEnabled() {
        return self::get('enable_dni_ruc', '0') === '1';
    }

    /**
     * Obtiene el proveedor de API seleccionado
     * @return string 'apiperu' o 'decolecta'
     */
    public static function getApiProvider() {
        return self::get('dni_ruc_provider', 'apiperu');
    }

    /**
     * Obtiene el token de ApiPeru.dev
     * @return string
     */
    public static function getApiPeruToken() {
        return self::get('apiperu_token', '');
    }

    /**
     * Obtiene el token de Decolecta
     * @return string
     */
    public static function getDecolectaToken() {
        return self::get('decolecta_token', '');
    }

    /**
     * Obtiene el estilo de toast configurado
     * @return string 'classic' o 'modern'
     */
    public static function getToastStyle() {
        return self::get('toast_style', 'classic');
    }

    /**
     * Verifica si el chatbot de IA está habilitado
     * @return bool
     */
    public static function isChatbotEnabled() {
        return self::get('enable_chatbot', '1') === '1';
    }

    /**
     * Limpia el cache (útil después de actualizar configuraciones)
     */
    public static function clearCache() {
        self::$cache = null;
    }
}
