<?php
// Part 1: Database schema and setup
// Add your database connection and table creation code here?>

<?php
/**
 * UNZANASA Voting Management System - Database Setup
 * Part 1: Database Schema and Initial Setup
 */

// Database configuration
class DatabaseConfig {
    const DB_HOST = 'localhost:8082';
    const DB_NAME = 'unzanasa_voting';
    const DB_USER = 'root';
    const DB_PASS = '';
}

/**
 * SQL Script to create all necessary tables
 */
$sql_setup = "
-- Create database
CREATE DATABASE IF NOT EXISTS unzanasa_voting;
USE unzanasa_voting;

-- Admin table
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Elections table
CREATE TABLE IF NOT EXISTS elections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    position VARCHAR(100) NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id)
);

-- Candidates table
CREATE TABLE IF NOT EXISTS candidates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    election_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    photo VARCHAR(255),
    bio TEXT,
    manifesto TEXT,
    vote_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
);

-- Valid computer numbers table
CREATE TABLE IF NOT EXISTS valid_computer_numbers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    computer_number VARCHAR(10) UNIQUE NOT NULL,
    student_name VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by INT NOT NULL,
    FOREIGN KEY (uploaded_by) REFERENCES admins(id)
);

-- Votes table
CREATE TABLE IF NOT EXISTS votes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    election_id INT NOT NULL,
    candidate_id INT NOT NULL,
    computer_number VARCHAR(10) NOT NULL,
    vote_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote_per_election (election_id, computer_number)
);

-- Admin sessions table
CREATE TABLE IF NOT EXISTS admin_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Voting activity log table
CREATE TABLE IF NOT EXISTS voting_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    computer_number VARCHAR(10) NOT NULL,
    election_id INT NOT NULL,
    action ENUM('vote_cast', 'invalid_attempt', 'duplicate_attempt') NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id)
);

-- Insert default admin account
INSERT INTO admins (username, password, email, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@unzanasa.edu.zm', 'System Administrator')
ON DUPLICATE KEY UPDATE username=username;

-- Create indexes for better performance
CREATE INDEX idx_elections_status ON elections(status);
CREATE INDEX idx_elections_dates ON elections(start_date, end_date);
CREATE INDEX idx_votes_election ON votes(election_id);
CREATE INDEX idx_votes_computer_number ON votes(computer_number);
CREATE INDEX idx_candidates_election ON candidates(election_id);
CREATE INDEX idx_voting_logs_computer ON voting_logs(computer_number);
CREATE INDEX idx_voting_logs_timestamp ON voting_logs(timestamp);
";

/**
 * Database Connection Class
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DatabaseConfig::DB_HOST . ";dbname=" . DatabaseConfig::DB_NAME,
                DatabaseConfig::DB_USER,
                DatabaseConfig::DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function setupDatabase() {
        global $sql_setup;
        try {
            $statements = explode(';', $sql_setup);
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    $this->connection->exec($statement);
                }
            }
            return true;
        } catch (PDOException $e) {
            error_log("Database setup error: " . $e->getMessage());
            return false;
        }
    }
}

/**
 * Installation Script
 * Run this file once to set up the database
 */
if (basename($_SERVER['PHP_SELF']) === 'database_setup.php') {
    echo "<h2>UNZANASA Voting System - Database Setup</h2>";
    
    $db = Database::getInstance();
    if ($db->setupDatabase()) {
        echo "<p style='color: green;'>✅ Database setup completed successfully!</p>";
        echo "<p><strong>Default Admin Login:</strong></p>";
        echo "<ul>";
        echo "<li>Username: admin</li>";
        echo "<li>Password: password</li>";
        echo "</ul>";
        echo "<p style='color: orange;'>⚠️ Please change the default password after first login!</p>";
    } else {
        echo "<p style='color: red;'>❌ Database setup failed. Check error logs.</p>";
    }
}
?>

?>