<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/application/config/config.php';

// Session helper class
class Session {
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function delete($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    public static function destroy() {
        session_destroy();
    }
}

// Flash message helper
class Flash {
    public static function set($type, $message) {
        Session::set('flash', [
            'type' => $type,
            'message' => $message
        ]);
    }
    
    public static function get() {
        $flash = Session::get('flash');
        if ($flash) {
            Session::delete('flash');
            return $flash;
        }
        return null;
    }
}

// Authentication class
class Auth {
    public static function check() {
        return Session::get('admin_logged_in', false);
    }
    
    public static function user() {
        return Session::get('admin_user');
    }
    
    public static function requireAuth() {
        if (!self::check()) {
            Flash::set('error', 'Please log in to access this page');
            header('Location: admin-login.php');
            exit;
        }
    }
}

// Utility class for common functions
class Utils {
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    public static function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    public static function flashMessage($message, $type = 'success') {
        Flash::set($type, $message);
    }
    
    public static function getFlashMessage() {
        return Flash::get();
    }
    
    public static function validateComputerNumber($number) {
        return preg_match('/^\d{10}$/', $number);
    }
    
    public static function getClientIP() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    public static function formatDateTime($datetime) {
        return date('M j, Y g:i A', strtotime($datetime));
    }
}

// Admin class for authentication and management
class Admin {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function authenticate($username, $password) {
        $this->db->query('SELECT * FROM admins WHERE username = :username');
        $this->db->bind(':username', $username);
        $admin = $this->db->single();
        
        if ($admin && password_verify($password, $admin->password)) {
            unset($admin->password); // Remove password before storing in session
            return (array)$admin;
        }
        
        return false;
    }
    
    public function createSession($adminId) {
        $this->db->query('SELECT id, username, email, full_name FROM admins WHERE id = :id');
        $this->db->bind(':id', $adminId);
        $admin = $this->db->single();
        
        if ($admin) {
            Session::set('admin_logged_in', true);
            Session::set('admin_user', (array)$admin);
            return true;
        }
        
        return false;
    }
    
    public static function logout() {
        Session::delete('admin_logged_in');
        Session::delete('admin_user');
        Session::destroy();
    }
}

// Database connection class
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    private $dbh;
    private $stmt;
    private $error;
    
    public function __construct() {
        // Check if port is specified in the host
        $hostParts = explode(':', $this->host);
        $host = $hostParts[0];
        $port = isset($hostParts[1]) ? ';port=' . $hostParts[1] : '';
        
        // Set DSN with port if specified
        $dsn = 'mysql:host=' . $host . $port . ';dbname=' . $this->dbname;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        // Create PDO instance
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            echo $this->error;
        }
    }
    
    // Prepare statement with query
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }
    
    // Bind values
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        
        $this->stmt->bindValue($param, $value, $type);
    }
    
    // Execute the prepared statement
    public function execute() {
        return $this->stmt->execute();
    }
    
    // Get result set as array of objects
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    // Get single record as object
    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }
    
    // Get row count
    public function rowCount() {
        return $this->stmt->rowCount();
    }
}


// Election class
class Election {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getActiveElections() {
        $this->db->query('SELECT * FROM elections WHERE start_date <= NOW() AND end_date >= NOW()');
        return $this->db->resultSet();
    }

    public function getElectionById($id) {
        $this->db->query('SELECT * FROM elections WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    public function getElectionStats() {
        $stats = [
            'total_elections' => 0,
            'active_elections' => 0,
            'completed_elections' => 0
        ];
        
        try {
            // Get total elections
            $this->db->query('SELECT COUNT(*) as count FROM elections');
            $result = $this->db->single();
            $stats['total_elections'] = $result->count ?? 0;
            
            // Get active elections (current date between start_date and end_date)
            $this->db->query('SELECT COUNT(*) as count FROM elections WHERE start_date <= NOW() AND end_date >= NOW()');
            $result = $this->db->single();
            $stats['active_elections'] = $result->count ?? 0;
            
            // Get completed elections (end_date < current date)
            $this->db->query('SELECT COUNT(*) as count FROM elections WHERE end_date < NOW()');
            $result = $this->db->single();
            $stats['completed_elections'] = $result->count ?? 0;
            
        } catch (Exception $e) {
            error_log('Error getting election stats: ' . $e->getMessage());
        }
        
        return $stats;
    }
}

// Candidate class
class Candidate {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getCandidatesByElection($electionId) {
        $this->db->query('SELECT * FROM candidates WHERE election_id = :election_id');
        $this->db->bind(':election_id', $electionId);
        return $this->db->resultSet();
    }

    public function getCandidateById($id) {
        $this->db->query('SELECT * FROM candidates WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
}

// Vote class
class Vote {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function castVote($electionId, $candidateId, $computerNumber, $ipAddress, $userAgent) {
        // Check if this computer number has already voted in this election
        $this->db->query('SELECT id FROM votes WHERE election_id = :election_id AND computer_number = :computer_number');
        $this->db->bind(':election_id', $electionId);
        $this->db->bind(':computer_number', $computerNumber);
        $this->db->execute();
        
        if ($this->db->rowCount() > 0) {
            return false; // Already voted
        }

        // Record the vote
        $this->db->query('INSERT INTO votes (election_id, candidate_id, computer_number, ip_address, user_agent) 
                         VALUES (:election_id, :candidate_id, :computer_number, :ip_address, :user_agent)');
        
        $this->db->bind(':election_id', $electionId);
        $this->db->bind(':candidate_id', $candidateId);
        $this->db->bind(':computer_number', $computerNumber);
        $this->db->bind(':ip_address', $ipAddress);
        $this->db->bind(':user_agent', $userAgent);
        
        return $this->db->execute();
    }
}
