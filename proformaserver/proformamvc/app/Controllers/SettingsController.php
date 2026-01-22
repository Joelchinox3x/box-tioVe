<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Setting;

class SettingsController extends Controller
{
    private $settingModel;

    public function __construct() {
        $this->settingModel = new Setting();
    }
    /**
     * Muestra la página de configuración
     */
    public function index()
    {
        // La sesión ya está iniciada en index.php

        // Cargar el helper de temas
        require_once __DIR__ . '/../Helpers/ThemeHelper.php';

        // Obtener los temas disponibles
        $availableThemes = \ThemeHelper::getAvailableThemes();
        $currentTheme = \ThemeHelper::getCurrentTheme();

        // Obtener navbar actual (mantener en sesión por compatibilidad)
        $currentNavbar = $_SESSION['navbar_style'] ?? 'navbar';

        // Obtener header actual (mantener en sesión por compatibilidad)
        $currentHeader = $_SESSION['header_style'] ?? 'header';

        // Obtener el PIN actual guardado en la base de datos
        $currentPin = $this->settingModel->get('pin_code', '1234');

        // Obtener configuración de registro
        $registrationEnabled = $this->settingModel->get('enable_registration', '1');

        // Obtener estilo de toast actual
        $currentToastStyle = $this->settingModel->get('toast_style', 'classic');

        // Cargar la vista de configuración
        $this->view('settings/index', [
            'availableThemes' => $availableThemes,
            'currentTheme' => $currentTheme,
            'currentNavbar' => $currentNavbar,
            'currentHeader' => $currentHeader,
            'currentPin' => $currentPin,
            'registrationEnabled' => $registrationEnabled,
            'currentToastStyle' => $currentToastStyle,
            'mensaje' => $this->get('msg')
        ]);
    }

    /**
     * Cambia el tema de la aplicación
     */
    public function changeTheme()
    {
        // La sesión ya está iniciada en index.php

        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/settings'));
            exit;
        }

        // Cargar el helper de temas
        require_once __DIR__ . '/../Helpers/ThemeHelper.php';

        // Obtener el tema del formulario
        $theme = $_POST['theme'] ?? '';

        // Validar y cambiar el tema
        if (\ThemeHelper::setTheme($theme)) {
            // Redirigir con mensaje de éxito
            header('Location: ' . url('/settings?msg=updated'));
        } else {
            // Redirigir con mensaje de error
            header('Location: ' . url('/settings?msg=error'));
        }

        exit;
    }

    /**
     * Cambia el estilo del navbar
     */
    public function changeNavbar()
    {
        // La sesión ya está iniciada en index.php

        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/settings'));
            exit;
        }

        // Obtener el navbar del formulario
        $navbar = $_POST['navbar'] ?? 'navbar';

        // Validar que sea un navbar válido
        $validNavbars = ['navbar', 'navbar_v2', 'navbar_v3'];
        if (in_array($navbar, $validNavbars)) {
            $_SESSION['navbar_style'] = $navbar;
            header('Location: ' . url('/settings?msg=navbar_updated'));
        } else {
            header('Location: ' . url('/settings?msg=error'));
        }

        exit;
    }

    /**
     * Cambia el estilo del header
     */
    public function changeHeader()
    {
        // La sesión ya está iniciada en index.php

        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/settings'));
            exit;
        }

        // Obtener el header del formulario
        $header = $_POST['header'] ?? 'header';

        // Validar que sea un header válido
        $validHeaders = ['header', 'header_v2', 'header_v3'];
        if (in_array($header, $validHeaders)) {
            $_SESSION['header_style'] = $header;
            header('Location: ' . url('/settings?msg=header_updated'));
        } else {
            header('Location: ' . url('/settings?msg=error'));
        }

        exit;
    }

    /**
     * Cambia el nombre de la aplicación
     */
    public function changeAppName()
    {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/settings'));
            exit;
        }

        // Obtener el nombre de la app del formulario
        $appName = $_POST['app_name'] ?? 'Tradimacova';

        // Limpiar y validar el nombre (eliminar espacios extra, máximo 50 caracteres)
        $appName = trim($appName);
        $appName = mb_substr($appName, 0, 50);

        // Si está vacío, usar el default
        if (empty($appName)) {
            $appName = 'Tradimacova';
        }

        // Guardar en base de datos
        $this->settingModel->set('app_name', $appName, 'Nombre de la aplicación que aparece en el home');
        header('Location: ' . url('/settings?msg=app_name_updated'));

        exit;
    }

    /**
     * Cambia la información del Manager (Nombre y WhatsApp)
     */
    public function changeManagerInfo()
    {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/settings'));
            exit;
        }

        // Obtener valores
        $managerName = $_POST['manager_name'] ?? '';
        $managerWhatsapp = $_POST['manager_whatsapp'] ?? '';

        // Limpiar y validar
        $managerName = trim($managerName);
        $managerName = mb_substr($managerName, 0, 100);
        
        $managerWhatsapp = preg_replace('/[^0-9+]/', '', $managerWhatsapp); // Solo números y +
        $managerWhatsapp = mb_substr($managerWhatsapp, 0, 20);

        // Guardar en base de datos
        $this->settingModel->set('manager_name', $managerName, 'Nombre del Manager para la firma');
        $this->settingModel->set('manager_whatsapp', $managerWhatsapp, 'Número de WhatsApp del Manager');

        header('Location: ' . url('/settings?msg=manager_info_updated'));
        exit;
    }

    /**
     * Cambia la configuración del Chatbot de IA
     */
    public function changeChatbotEnabled()
    {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/settings'));
            exit;
        }

        // Obtener el valor del Chatbot del formulario
        $chatbotEnabled = $_POST['enable_chatbot'] ?? '0';

        // Guardar en base de datos
        $this->settingModel->set('enable_chatbot', $chatbotEnabled, '1 = Chatbot habilitado, 0 = Chatbot deshabilitado');

        header('Location: ' . url('/settings?msg=chatbot_updated'));

        exit;
    }

    /**
     * Cambia la configuración de GPS en clientes
     */
    public function changeGpsEnabled()
    {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/settings'));
            exit;
        }

        // Obtener el valor del GPS del formulario
        $gpsEnabled = $_POST['gps_enabled'] ?? '0';

        // Guardar en base de datos
        $this->settingModel->set('enable_gps', $gpsEnabled, '1 = GPS habilitado, 0 = GPS deshabilitado');

        header('Location: ' . url('/settings?msg=gps_updated'));

        exit;
    }

    /**
     * Cambia el logo de la aplicación
     */
    public function changeAppLogo()
    {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('changeAppLogo: No es POST');
            http_response_code(405);
            exit;
        }

        // Debug: Log FILES array
        error_log('changeAppLogo: FILES = ' . print_r($_FILES, true));

        // Verificar que se haya subido un archivo
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            error_log('changeAppLogo: No hay archivo o error en upload. Error code: ' . ($_FILES['logo']['error'] ?? 'N/A'));
            http_response_code(400);
            exit;
        }

        $file = $_FILES['logo'];

        // Validar tamaño (máximo 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            http_response_code(400);
            exit;
        }

        // Validar tipo de archivo
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            http_response_code(400);
            exit;
        }

        // Eliminar logo anterior si existe
        $oldLogo = $this->settingModel->get('app_logo', '');
        if (!empty($oldLogo)) {
            $oldLogoPath = __DIR__ . '/../../public/' . $oldLogo;
            if (file_exists($oldLogoPath)) {
                unlink($oldLogoPath);
            }
        }

        // Obtener extensión
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Generar nombre único
        $fileName = 'logo_' . time() . '.' . $extension;

        // Definir ruta de destino
        $uploadDir = __DIR__ . '/../../public/assets/img/';
        $uploadPath = $uploadDir . $fileName;

        // Crear directorio si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Mover el archivo
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Establecer permisos correctos para que el archivo sea accesible públicamente
            chmod($uploadPath, 0644);

            // Guardar la ruta en base de datos
            $this->settingModel->set('app_logo', 'assets/img/' . $fileName, 'URL del logo de la aplicación');

            // Limpiar caché del helper para que se refleje el cambio
            require_once __DIR__ . '/../Helpers/SettingsHelper.php';
            \SettingsHelper::clearCache();

            http_response_code(200);
        } else {
            http_response_code(500);
        }

        exit;
    }

    /**
     * Elimina el logo de la aplicación
     */
    public function deleteAppLogo()
    {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        // Obtener logo actual
        $currentLogo = $this->settingModel->get('app_logo', '');

        // Eliminar archivo físico si existe
        if (!empty($currentLogo)) {
            $logoPath = __DIR__ . '/../../public/' . $currentLogo;
            if (file_exists($logoPath)) {
                unlink($logoPath);
            }
        }

        // Limpiar la base de datos
        $this->settingModel->set('app_logo', '', 'URL del logo de la aplicación');

        // Limpiar caché del helper para que se refleje el cambio
        require_once __DIR__ . '/../Helpers/SettingsHelper.php';
        \SettingsHelper::clearCache();

        http_response_code(200);
        exit;
    }

    /**
     * Cambia la configuración de IGV
     */
    public function changeIgv()
    {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/settings'));
            exit;
        }

        // Obtener el porcentaje de IGV del formulario
        $igvPercent = $_POST['igv_percent'] ?? '18';

        // Validar que sea un número entre 0 y 100
        $igvPercent = floatval($igvPercent);
        if ($igvPercent < 0 || $igvPercent > 100) {
            $igvPercent = 18; // Default
        }

        // Obtener si los precios incluyen IGV
        $pricesIncludeIgv = $_POST['prices_include_igv'] ?? '0';

        // Guardar en base de datos
        $this->settingModel->set('igv_percent', $igvPercent, 'Porcentaje de IGV aplicable');
        $this->settingModel->set('prices_include_igv', $pricesIncludeIgv, '1 = Precios incluyen IGV, 0 = Precios sin IGV');

        header('Location: ' . url('/settings?msg=igv_updated'));

        exit;
    }

    /**
     * Cambia el PIN de desbloqueo para clientes protegidos
     */
    public function changePIN()
    {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/settings'));
            exit;
        }

        // Obtener el PIN actual almacenado de la base de datos
        $storedPin = $this->settingModel->get('pin_code', '1234');

        // Obtener el PIN actual ingresado por el usuario
        $currentPin = $_POST['current_pin'] ?? '';

        // Obtener el nuevo PIN
        $newPin = $_POST['admin_pin'] ?? '';

        // Si ambos campos están vacíos, no hacer nada (no se intenta cambiar el PIN)
        if (empty($currentPin) && empty($newPin)) {
            header('Location: ' . url('/settings?msg=updated'));
            exit;
        }

        // Si se ingresó uno pero no el otro, mostrar error
        if (empty($currentPin) || empty($newPin)) {
            header('Location: ' . url('/settings?msg=pin_incomplete'));
            exit;
        }

        // Verificar que el PIN actual sea correcto
        if ($currentPin !== $storedPin) {
            header('Location: ' . url('/settings?msg=pin_incorrect'));
            exit;
        }

        // Limpiar y validar el nuevo PIN (solo números, máximo 6 dígitos)
        $newPin = preg_replace('/[^0-9]/', '', $newPin);
        $newPin = substr($newPin, 0, 6);

        // Validar que el nuevo PIN tenga al menos 3 dígitos
        if (strlen($newPin) < 3) {
            header('Location: ' . url('/settings?msg=pin_too_short'));
            exit;
        }

        // Guardar el nuevo PIN en base de datos
        $this->settingModel->set('pin_code', $newPin, 'PIN de seguridad para la aplicación');
        header('Location: ' . url('/settings?msg=pin_updated'));

        exit;
    }

    /**
     * Cambia la configuración de API DNI/RUC
     */
    public function changeApiDni()
    {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/settings'));
            exit;
        }

        // Obtener los valores del formulario
        $enableDniRuc = $_POST['enable_dni_ruc'] ?? '0';
        $dniRucProvider = $_POST['dni_ruc_provider'] ?? 'apiperu';
        $apiPeruToken = $_POST['apiperu_token'] ?? '';
        $decolectaToken = $_POST['decolecta_token'] ?? '';

        // Validar proveedor
        $validProviders = ['apiperu', 'decolecta'];
        if (!in_array($dniRucProvider, $validProviders)) {
            $dniRucProvider = 'apiperu';
        }

        // Guardar en base de datos
        $this->settingModel->set('enable_dni_ruc', $enableDniRuc, '1 = Búsqueda DNI/RUC habilitada, 0 = deshabilitada');
        $this->settingModel->set('dni_ruc_provider', $dniRucProvider, 'Proveedor de API DNI/RUC: apiperu | decolecta');
        $this->settingModel->set('apiperu_token', $apiPeruToken, 'Token API para consultas DNI y RUC desde apiperu.dev');
        $this->settingModel->set('decolecta_token', $decolectaToken, 'Token API para consultas DNI y RUC desde Decolecta');

        // Limpiar caché del helper
        require_once __DIR__ . '/../Helpers/SettingsHelper.php';
        \App\Helpers\SettingsHelper::clearCache();

        header('Location: ' . url('/settings?msg=api_dni_updated'));
        exit;
    }

    /**
     * Cambia la configuración de registro de usuarios
     */
    public function changeRegistration()
    {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/settings'));
            exit;
        }

        // Obtener el valor del registro del formulario
        $registrationEnabled = $_POST['enable_registration'] ?? '0';

        // Guardar en base de datos
        $this->settingModel->set('enable_registration', $registrationEnabled, '1 = Registro habilitado, 0 = Registro deshabilitado');

        header('Location: ' . url('/settings?msg=registration_updated'));

        exit;
    }

    /**
     * Cambia el estilo de notificaciones toast
     */
    public function changeToastStyle()
    {
        // Verificar que sea una petición POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/settings'));
            exit;
        }

        // Obtener el estilo de toast del formulario
        $toastStyle = $_POST['toast_style'] ?? 'classic';

        // Validar que sea un estilo válido
        $validStyles = ['classic', 'modern'];
        if (!in_array($toastStyle, $validStyles)) {
            $toastStyle = 'classic';
        }

        // Guardar en base de datos
        $this->settingModel->set('toast_style', $toastStyle, 'Estilo de notificaciones toast: classic | modern');

        // Limpiar caché del helper
        require_once __DIR__ . '/../Helpers/SettingsHelper.php';
        \App\Helpers\SettingsHelper::clearCache();

        header('Location: ' . url('/settings?msg=toast_style_updated'));
        exit;
    }
}