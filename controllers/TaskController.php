<?php
require_once 'models/Task.php';
require_once 'models/ActivityLog.php';

class TaskController {
    private $taskModel;
    private $logModel;
    
    public function __construct() {
        $this->taskModel = new Task();
        $this->logModel = new ActivityLog();
    }
    
    // Get all tasks for the user
    public function getAllTasks() {
        $user = $this->getAuthUser();
        
        // Admin can see all tasks, regular users only see their own
        if ($user['role'] === 'admin') {
            $tasks = $this->taskModel->getAll();
        } else {
            $tasks = $this->taskModel->getAllByUserId($user['id']);
        }
        
        $this->sendResponse(200, ['tasks' => $tasks]);
    }
    
    // Get a single task
    public function getTask($id) {
        $user = $this->getAuthUser();
        $task = $this->taskModel->getById($id);
        
        if (!$task) {
            $this->sendResponse(404, ['error' => 'Task not found']);
            return;
        }
        
        // Check if user has access to this task
        if ($user['role'] !== 'admin' && $task['user_id'] != $user['id']) {
            $this->sendResponse(403, ['error' => 'Access denied']);
            return;
        }
        
        $this->sendResponse(200, ['task' => $task]);
    }
    
    // Create a new task
    public function createTask() {
        $user = $this->getAuthUser();
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if (!isset($data['title'])) {
            $this->sendResponse(400, ['error' => 'Title is required']);
            return;
        }
        
        // Add user_id to task data
        $data['user_id'] = $user['id'];
        
        // Create task
        $taskId = $this->taskModel->create($data);
        
        if ($taskId) {
            // Log activity
            $this->logModel->create([
                'user_id' => $user['id'],
                'task_id' => $taskId,
                'action' => 'create'
            ]);
            
            $task = $this->taskModel->getById($taskId);
            $this->sendResponse(201, ['message' => 'Task created successfully', 'task' => $task]);
        } else {
            $this->sendResponse(500, ['error' => 'Failed to create task']);
        }
    }
    
    // Update a task
    public function updateTask($id) {
        $user = $this->getAuthUser();
        $task = $this->taskModel->getById($id);
        
        if (!$task) {
            $this->sendResponse(404, ['error' => 'Task not found']);
            return;
        }
        
        // Check if user has access to this task
        if ($user['role'] !== 'admin' && $task['user_id'] != $user['id']) {
            $this->sendResponse(403, ['error' => 'Access denied']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate input
        if (empty($data)) {
            $this->sendResponse(400, ['error' => 'No data provided']);
            return;
        }
        
        // Update task
        if ($this->taskModel->update($id, $data)) {
            // Log activity
            $this->logModel->create([
                'user_id' => $user['id'],
                'task_id' => $id,
                'action' => 'update'
            ]);
            
            $updatedTask = $this->taskModel->getById($id);
            $this->sendResponse(200, ['message' => 'Task updated successfully', 'task' => $updatedTask]);
        } else {
            $this->sendResponse(500, ['error' => 'Failed to update task']);
        }
    }
    
    // Soft delete a task
    public function deleteTask($id) {
        $user = $this->getAuthUser();
        $task = $this->taskModel->getById($id);
        
        if (!$task) {
            $this->sendResponse(404, ['error' => 'Task not found']);
            return;
        }
        
        // Check if user has access to this task
        if ($user['role'] !== 'admin' && $task['user_id'] != $user['id']) {
            $this->sendResponse(403, ['error' => 'Access denied']);
            return;
        }
        
        // Soft delete the task
        if ($this->taskModel->softDelete($id)) {
            // Log activity
            $this->logModel->create([
                'user_id' => $user['id'],
                'task_id' => $id,
                'action' => 'delete'
            ]);
            
            $this->sendResponse(200, ['message' => 'Task deleted successfully']);
        } else {
            $this->sendResponse(500, ['error' => 'Failed to delete task']);
        }
    }
    
    // Get authenticated user from request
    private function getAuthUser() {
        return $_REQUEST['user'];
    }
    
    // Helper to send JSON response
    private function sendResponse($statusCode, $data) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }
}