<?php
require_once 'config/database.php';

class ActivityLog {
    private $conn;
    private $table = 'activity_logs';
    
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }
    
    // Create a new activity log
    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, task_id, action) 
                  VALUES 
                  (:user_id, :task_id, :action)";
                  
        $stmt = $this->conn->prepare($query);
        
        // Bind data
        $stmt->bindParam(':user_id', $data['user_id']);
        $stmt->bindParam(':task_id', $data['task_id']);
        $stmt->bindParam(':action', $data['action']);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    // Get all logs (for admin)
    public function getAll() {
        $query = "SELECT al.*, u.username as username, t.title as task_title 
                  FROM " . $this->table . " al
                  JOIN users u ON al.user_id = u.id
                  JOIN tasks t ON al.task_id = t.id
                  ORDER BY al.timestamp DESC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Get all logs for a specific user
    public function getAllByUserId($userId) {
        $query = "SELECT al.*, u.username as username, t.title as task_title 
                  FROM " . $this->table . " al
                  JOIN users u ON al.user_id = u.id
                  JOIN tasks t ON al.task_id = t.id
                  WHERE al.user_id = :user_id
                  ORDER BY al.timestamp DESC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Get all logs for a specific task
    public function getAllByTaskId($taskId) {
        $query = "SELECT al.*, u.username as username, t.title as task_title 
                  FROM " . $this->table . " al
                  JOIN users u ON al.user_id = u.id
                  JOIN tasks t ON al.task_id = t.id
                  WHERE al.task_id = :task_id
                  ORDER BY al.timestamp DESC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':task_id', $taskId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}