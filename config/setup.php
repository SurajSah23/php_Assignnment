<?php
require_once 'database.php';

// Script to set up the database tables
function createTables() {
    $db = Database::getInstance()->getConnection();
    
    // Create users table
    $users_table = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // Create tasks table
    $tasks_table = "CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
        user_id INT NOT NULL,
        is_deleted TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    
    // Create activity_logs table
    $activity_logs_table = "CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        task_id INT NOT NULL,
        action VARCHAR(50) NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (task_id) REFERENCES tasks(id)
    )";
    
    try {
        $db->exec($users_table);
        $db->exec($tasks_table);
        $db->exec($activity_logs_table);
        echo "Database tables created successfully.\n";
        
        // Create admin user if not exists
        $check_admin = $db->prepare("SELECT * FROM users WHERE username = 'admin'");
        $check_admin->execute();
        
        if ($check_admin->rowCount() === 0) {
            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
            $create_admin = $db->prepare("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@example.com', :password, 'admin')");
            $create_admin->bindParam(':password', $admin_password);
            $create_admin->execute();
            echo "Admin user created successfully.\n";
        }
    } catch(PDOException $e) {
        echo $e->getMessage();
    }
}

// Run setup
createTables();

echo "Setup completed successfully.\n";