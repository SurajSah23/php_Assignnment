<?php
require_once 'models/User.php';
require_once 'utils/JwtHelper.php';

class AuthMiddleware {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    // Handle authentication middleware
    public function handle($next) {
        // Check if Authorization header exists
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $this->sendResponse(401, ['error' => 'Authorization token required']);
            return;
        }
        
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        $token = null;
        
        // Extract token from Bearer header
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }
        
        if (!$token) {
            $this->sendResponse(401, ['error' => 'Invalid token format']);
            return;
        }
        
        // Verify token
        try {
            $payload = JwtHelper::validateToken($token);
            
            if (!$payload) {
                $this->sendResponse(401, ['error' => 'Invalid or expired token']);
                return;
            }
            
            // Get user from database
            $user = $this->userModel->findById($payload['sub']);
            
            if (!$user) {
                $this->sendResponse(401, ['error' => 'User not found']);
                return;
            }
            
            // Add user to request for controllers to use
            $_REQUEST['user'] = $user;
            
            // Continue to the next handler
            $next();
            
        } catch (Exception $e) {
            $this->sendResponse(401, ['error' => 'Invalid token: ' . $e->getMessage()]);
        }
    }
    
    // Helper to send JSON response
    private function sendResponse($statusCode, $data) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }
}