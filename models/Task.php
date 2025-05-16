<?php
require_once 'config/database.php';

class Task {
    private $conn;
    private $table = 'tasks';
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    
    // Create a new task
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (title, description, status, user_id) 
                  VALUES 
                  (:title, :description, :status, :user_id)";
                  
        $stmt = $this->conn->prepare($query);
        
        // Clean and bind data
        $title = htmlspecialchars(strip_tags($data['title']));
        $description = isset($data['description']) ? htmlspecialchars(strip_tags($data['description'])) : '';
        $status = isset($data['status']) ? htmlspecialchars(strip_tags($data['status'])) : 'pending';
        $user_id = $data['user_id'];
        
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    // Get all tasks (for admin)
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " WHERE is_deleted = 0 ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Get all tasks for a specific user
    public function getAllByUserId($userId) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id AND is_deleted = 0 
                  ORDER BY created_at DESC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Get task by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Update task
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        // Build dynamic query based on provided fields
        foreach ($data as $key => $value) {
            if ($key != 'id' && $key != 'user_id' && $key != 'created_at' && $key != 'updated_at' && $key != 'is_deleted') {
                $fields[] = "$key = :$key";
                $values[":$key"] = htmlspecialchars(strip_tags($value));
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id AND is_deleted = 0";
        $stmt = $this->conn->prepare($query);
        
        $values[':id'] = $id;
        
        foreach ($values as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        return $stmt->execute();
    }
    
    // Soft delete a task
    public function softDelete($id) {
        $query = "UPDATE " . $this->table . " SET is_deleted = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}