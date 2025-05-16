<?php
require_once 'controllers/AuthController.php';
require_once 'controllers/TaskController.php';
require_once 'controllers/ActivityLogController.php';
require_once 'middleware/AuthMiddleware.php';

class Route {
    private static $routes = [];
    
    // Define routes
    public static function init() {
        // Auth routes
        self::$routes = [
            // Auth routes
            '/api/register' => [
                'POST' => ['AuthController', 'register']
            ],
            '/api/login' => [
                'POST' => ['AuthController', 'login']
            ],
            
            // Task routes - all protected by auth middleware
            '/api/tasks' => [
                'GET' => ['AuthMiddleware', 'handle', 'TaskController', 'getAllTasks'],
                'POST' => ['AuthMiddleware', 'handle', 'TaskController', 'createTask']
            ],
            
            // Single task routes with dynamic ID
            '/api/tasks/(\d+)' => [
                'GET' => ['AuthMiddleware', 'handle', 'TaskController', 'getTask'],
                'PUT' => ['AuthMiddleware', 'handle', 'TaskController', 'updateTask'],
                'DELETE' => ['AuthMiddleware', 'handle', 'TaskController', 'deleteTask']
            ],
            
            // Activity logs routes
            '/api/activity-logs' => [
                'GET' => ['AuthMiddleware', 'handle', 'ActivityLogController', 'getLogs']
            ]
        ];
    }
    
    // Handle incoming requests
    public static function handleRequest($uri, $method) {
        self::init();
        
        $matched = false;
        
        foreach (self::$routes as $route => $handlers) {
            // Check if we have a pattern with parameters
            if (strpos($route, '(') !== false) {
                $pattern = '#^' . $route . '$#';
                
                if (preg_match($pattern, $uri, $matches)) {
                    if (isset($handlers[$method])) {
                        $matched = true;
                        array_shift($matches); // Remove the full match
                        self::callHandler($handlers[$method], $matches);
                        break;
                    }
                }
            } 
            // Exact route match
            else if ($route === $uri) {
                if (isset($handlers[$method])) {
                    $matched = true;
                    self::callHandler($handlers[$method], []);
                    break;
                }
            }
        }
        
        if (!$matched) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
        }
    }
    
    // Call the appropriate handler/middleware chain
    private static function callHandler($handler, $params) {
        // If we have a middleware chain
        if (count($handler) > 2) {
            $middleware = new $handler[0]();
            
            // The next handler after middleware
            $controller = new $handler[2]();
            $method = $handler[3];
            
            // Call middleware with controller as next
            $middleware->{$handler[1]}(function() use ($controller, $method, $params) {
                call_user_func_array([$controller, $method], $params);
            });
        } else {
            // Direct controller call without middleware
            $controller = new $handler[0]();
            $method = $handler[1];
            call_user_func_array([$controller, $method], $params);
        }
    }
}