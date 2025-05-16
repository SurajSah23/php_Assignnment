<?php
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        // Database configuration
        $host = 'localhost';
        $db_name = 'task_api';
        $username = 'root';
        $password = '';

        try {
            $this->conn = new PDO(
                "mysql:host=$host;dbname=$db_name",
                $username,
                $password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
            die();
        }
    }

    // Singleton pattern to ensure only one database connection
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}