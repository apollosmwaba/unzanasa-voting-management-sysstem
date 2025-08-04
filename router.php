<?php
/**
 * Simple MVC Router for UNZANASA Voting System
 */

class Router {
    private $routes = [];
    private $basePath = '';
    
    public function __construct($basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }
    
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }
    
    private function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    public function dispatch() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $_SERVER['REQUEST_URI'];
        
        // Remove query string and base path
        $requestUri = strtok($requestUri, '?');
        if ($this->basePath) {
            $requestUri = substr($requestUri, strlen($this->basePath));
        }
        $requestUri = rtrim($requestUri, '/') ?: '/';
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestUri)) {
                return $this->callHandler($route['handler'], $requestUri);
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        echo "404 - Page Not Found";
    }
    
    private function matchPath($routePath, $requestUri) {
        // Simple exact match for now
        return $routePath === $requestUri;
    }
    
    private function callHandler($handler, $uri) {
        if (is_callable($handler)) {
            return call_user_func($handler);
        }
        
        if (is_string($handler)) {
            // Format: "ControllerName@methodName"
            if (strpos($handler, '@') !== false) {
                list($controller, $method) = explode('@', $handler);
                $controllerClass = $controller . 'Controller';
                
                if (class_exists($controllerClass)) {
                    $instance = new $controllerClass();
                    if (method_exists($instance, $method)) {
                        return $instance->$method();
                    }
                }
            }
        }
        
        throw new Exception("Handler not found: " . $handler);
    }
}

// Base Controller
abstract class BaseController {
    protected function view($viewName, $data = []) {
        extract($data);
        $viewPath = __DIR__ . '/app/views/' . $viewName . '.php';
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception("View not found: " . $viewName);
        }
    }
    
    protected function redirect($url) {
        header("Location: " . $url);
        exit;
    }
    
    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
