<?php
require_once 'models/ActivityLog.php';

class ActivityLogController {
    private $logModel;
    
    public function __construct() {
        $this->logModel = new ActivityLog();
    }
    
    // Get activity logs for the user
    public function getLogs() {
        $user = $this->getAuthUser();
        
        // Admin can see all logs, regular users only see their own
        if ($user['role'] === 'admin') {
            $logs = $this->logModel->getAll();
        } else {
            $logs = $this->logModel->getAllByUserId($user['id']);
        }
        
        $this->sendResponse(200, ['activity_logs' => $logs]);
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