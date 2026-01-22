<?php
// core/Controller.php - Controlador Base

namespace Core;

class Controller {

    // Renderizar vista
    protected function view($view, $data = [], $layout = true) {
        extract($data);

        // Ruta de la vista
        $viewPath = __DIR__ . "/../app/Views/{$view}.php";

        if (!file_exists($viewPath)) {
            die("Vista no encontrada: {$view}");
        }

        // Capturar contenido de la vista
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Renderizar con layout (si está habilitado y existe)
        $layoutPath = __DIR__ . "/../app/Views/layouts/main.php";
        if ($layout && file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    // Obtener datos POST
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    // Obtener datos GET
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    // Redireccionar
    protected function redirect($url, $params = []) {
        $queryString = !empty($params) ? '?' . http_build_query($params) : '';
        $fullUrl = url($url) . $queryString;
        header("Location: {$fullUrl}");
        exit;
    }

    // Respuesta JSON
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Validación simple
    protected function validate($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $ruleArray = explode('|', $ruleString);

            foreach ($ruleArray as $rule) {
                // Required
                if ($rule === 'required' && empty($data[$field])) {
                    $errors[$field][] = "El campo {$field} es requerido";
                }

                // Min length
                if (strpos($rule, 'min:') === 0) {
                    $min = (int)substr($rule, 4);
                    if (isset($data[$field]) && strlen($data[$field]) < $min) {
                        $errors[$field][] = "El campo {$field} debe tener al menos {$min} caracteres";
                    }
                }

                // Numeric
                if ($rule === 'numeric' && isset($data[$field]) && !is_numeric($data[$field])) {
                    $errors[$field][] = "El campo {$field} debe ser numérico";
                }

                // Email
                if ($rule === 'email' && isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "El campo {$field} debe ser un email válido";
                }
            }
        }

        return $errors;
    }

    // Obtener sesión
    protected function session($key = null, $default = null) {
        if ($key === null) {
            return $_SESSION;
        }
        return $_SESSION[$key] ?? $default;
    }

    // Establecer sesión
    protected function setSession($key, $value) {
        $_SESSION[$key] = $value;
    }

    // Flash message
    protected function flash($key, $value = null) {
        if ($value === null) {
            $val = $_SESSION["flash_{$key}"] ?? null;
            unset($_SESSION["flash_{$key}"]);
            return $val;
        }
        $_SESSION["flash_{$key}"] = $value;
    }
}
