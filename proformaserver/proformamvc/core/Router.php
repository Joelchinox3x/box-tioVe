<?php
// core/Router.php - Sistema de Enrutamiento

namespace Core;

class Router {
    private $routes = [];
    
    // Registrar ruta GET
    public function get($uri, $controller, $method) {
        $this->routes['GET'][$uri] = ['controller' => $controller, 'method' => $method];
    }
    
    // Registrar ruta POST
    public function post($uri, $controller, $method) {
        $this->routes['POST'][$uri] = ['controller' => $controller, 'method' => $method];
    }
    
    // Resolver ruta actual
    public function resolve() {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // Eliminar el prefijo de la carpeta si existe (ej: /proforma-app/)
        $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);

        // Usar str_ireplace para hacer reemplazo case-insensitive (Windows)
        $requestUri = str_ireplace($basePath, '', $requestUri);

        // Si está vacío, es la raíz
        if (empty($requestUri) || $requestUri === '/') {
            $requestUri = '/';
        }
        
        // Buscar coincidencia exacta
        if (isset($this->routes[$requestMethod][$requestUri])) {
            return $this->callAction(
                $this->routes[$requestMethod][$requestUri]['controller'],
                $this->routes[$requestMethod][$requestUri]['method']
            );
        }
        
        // Buscar coincidencia con parámetros dinámicos
        foreach ($this->routes[$requestMethod] ?? [] as $route => $action) {
            $pattern = $this->convertToRegex($route);
            
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remover la coincidencia completa
                return $this->callAction(
                    $action['controller'],
                    $action['method'],
                    $matches
                );
            }
        }
        
        // Ruta no encontrada
        $this->notFound();
    }
    
    // Convertir ruta con parámetros a regex
    private function convertToRegex($route) {
        // Ejemplo: /producto/{id} -> /producto/([0-9]+)
        $route = preg_replace('/\{([a-zA-Z]+)\}/', '([^/]+)', $route);
        return '#^' . $route . '$#';
    }
    
    // Llamar al controlador y método
    private function callAction($controller, $method, $params = []) {
        $controllerClass = "App\\Controllers\\{$controller}";
        
        if (!class_exists($controllerClass)) {
            die("Controlador no encontrado: {$controllerClass}");
        }
        
        $controllerInstance = new $controllerClass();
        
        if (!method_exists($controllerInstance, $method)) {
            die("Método no encontrado: {$method}");
        }
        
        return call_user_func_array([$controllerInstance, $method], $params);
    }
    
    // Página 404
    private function notFound() {
        http_response_code(404);
        
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, '/api/') !== false) {
             header('Content-Type: application/json');
             echo json_encode([
                 'error' => '404 - Not Found', 
                 'debug_uri' => $uri,
                 'resolved_method' => $_SERVER['REQUEST_METHOD']
             ]);
        } else {
             echo "404 - Página no encontrada <br> URI: " . htmlspecialchars($uri);
        }
        exit;
    }
}