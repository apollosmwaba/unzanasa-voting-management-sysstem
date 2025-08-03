<?php
// Create remember_tokens table for persistent login functionality
$migration = new class {
    public function up($db) {
        $query = "CREATE TABLE IF NOT EXISTS remember_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
            UNIQUE KEY unique_token (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $db->query($query);
        return $db->execute();
    }
    
    public function down($db) {
        $query = "DROP TABLE IF EXISTS remember_tokens";
        $db->query($query);
        return $db->execute();
    }
};

return $migration;
