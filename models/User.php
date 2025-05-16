<?php
require_once 'config/database.php';

class User {
    private $conn;
    private $table = 'users';
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    
    // Create a new user
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (username, email, password, role) 
                  VALUES 
                  (:username, :email, :password, :role)";
                  
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $username = htmlspecialchars(strip_tags($data['username']));
        $email = htmlspecialchars(strip_tags($data['email']));
        $password = $data['password']; // Already hashed
        $role = isset($data['role']) ? htmlspecialchars(strip_tags($data['role'])) : 'user';
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    // Find user by ID
    public function findById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Find user by username
    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table . " WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Find user by email
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Get all users
    public function getAll() {
        $query = "SELECT id, username, email, role, created_at, updated_at FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Update user
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        // Build dynamic query based on provided fields
        foreach ($data as $key => $value) {
            if ($key != 'id' && $key != 'created_at' && $key != 'updated_at') {
                $fields[] = "$key = :$key";
                $values[":$key"] = htmlspecialchars(strip_tags($value));
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $values[':id'] = $id;
        
        foreach ($values as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        return $stmt->execute();
    }
}