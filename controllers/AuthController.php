<?php
require_once 'models/User.php';
require_once 'utils/JwtHelper.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    // Register a new user
    public function register() {
        // Get request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
            $this->sendResponse(400, ['error' => 'Username, email and password are required']);
            return;
        }
        
        if (strlen($data['password']) < 6) {
            $this->sendResponse(400, ['error' => 'Password must be at least 6 characters']);
            return;
        }
        
        // Check if username or email already exists
        if ($this->userModel->findByUsername($data['username'])) {
            $this->sendResponse(400, ['error' => 'Username already exists']);
            return;
        }
        
        if ($this->userModel->findByEmail($data['email'])) {
            $this->sendResponse(400, ['error' => 'Email already exists']);
            return;
        }
        
        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Create user
        if ($this->userModel->create($data)) {
            $this->sendResponse(201, ['message' => 'User registered successfully']);
        } else {
            $this->sendResponse(500, ['error' => 'Failed to register user']);
        }
    }
    
    // Login user
    public function login() {
        // Get request body
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if (!isset($data['username']) || !isset($data['password'])) {
            $this->sendResponse(400, ['error' => 'Username and password are required']);
            return;
        }
        
        // Find user by username
        $user = $this->userModel->findByUsername($data['username']);
        
        if (!$user) {
            $this->sendResponse(401, ['error' => 'Invalid credentials']);
            return;
        }
        
        // Verify password
        if (!password_verify($data['password'], $user['password'])) {
            $this->sendResponse(401, ['error' => 'Invalid credentials']);
            return;
        }
        
        // Generate JWT token
        $jwt = JwtHelper::generateToken([
            'sub' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ]);
        
        // Send response with token
        $this->sendResponse(200, [
            'message' => 'Login successful',
            'token' => $jwt,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    }
    
    // Helper to send JSON response
    private function sendResponse($statusCode, $data) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }
}