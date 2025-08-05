<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/application/config/config.php';

// Autoload model classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/application/models/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

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

// Admin class is now loaded via autoloader from /application/models/Admin.php

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
    
    // Get result set as array of associative arrays
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get single record as associative array
    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get row count
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    // Get last insert ID
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
    
    // Begin transaction
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }
    
    // Commit transaction
    public function commit() {
        return $this->dbh->commit();
    }
    
    // Rollback transaction
    public function rollBack() {
        return $this->dbh->rollBack();
    }
    
    // Get error information
    public function getErrorInfo() {
        return $this->dbh->errorInfo();
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
    
    public function getAllElections($filters = []) {
        $sql = 'SELECT * FROM elections WHERE 1=1';
        
        // Add status filter if provided
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $sql .= ' AND start_date <= NOW() AND end_date >= NOW() AND status = "active"';
            } else {
                $sql .= ' AND status = :status';
            }
        }
        
        $sql .= ' ORDER BY start_date DESC';
        
        $this->db->query($sql);
        
        // Bind status parameter if it's not 'active' (for active, we use direct SQL for date comparison)
        if (!empty($filters['status']) && $filters['status'] !== 'active') {
            $this->db->bind(':status', $filters['status']);
        }
        
        $result = $this->db->resultSet();
        
        // Debug: Log the query and results
        error_log('Election Query: ' . $sql);
        error_log('Election Results: ' . print_r($result, true));
        
        return $result;
    }
    
    public function createElection($data) {
        // Debug: Log the incoming data
        error_log('Election data: ' . print_r($data, true));
        
        // Map form field names to database column names
        $mappedData = [
            'title' => $data['title'] ?? ($data['name'] ?? ''), // Handle both 'title' and 'name' for backward compatibility
            'description' => $data['description'] ?? '',
            'position' => $data['position'] ?? 'General', // Use provided position or default to 'General'
            'start_date' => $data['start_date'] ?? date('Y-m-d H:i:s'),
            'end_date' => $data['end_date'] ?? date('Y-m-d H:i:s', strtotime('+1 day')),
            'status' => $data['status'] ?? 'draft',
            'created_by' => $_SESSION['admin_id'] ?? 1
        ];
        
        // Debug: Log the mapped data
        error_log('Mapped data: ' . print_r($mappedData, true));
        
        // Build the SQL query with the correct column names
        $sql = 'INSERT INTO elections (title, description, position, start_date, end_date, status, created_by) 
                VALUES (:title, :description, :position, :start_date, :end_date, :status, :created_by)';
        
        $this->db->query($sql);
        
        // Bind values using the mapped data
        foreach ($mappedData as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }
        
        // Execute
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            throw new Exception('Failed to create election');
        }
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
            $stats['total_elections'] = $result['count'] ?? 0;
            
            // Get active elections (current date between start_date and end_date)
            $this->db->query('SELECT COUNT(*) as count FROM elections WHERE start_date <= NOW() AND end_date >= NOW()');
            $result = $this->db->single();
            $stats['active_elections'] = $result['count'] ?? 0;
            
            // Get completed elections (end_date < current date)
            $this->db->query('SELECT COUNT(*) as count FROM elections WHERE end_date < NOW()');
            $result = $this->db->single();
            $stats['completed_elections'] = $result['count'] ?? 0;
            
        } catch (Exception $e) {
            error_log('Error getting election stats: ' . $e->getMessage());
        }
        
        return $stats;
    }
    
    public function updateElection($id, $data) {
        try {
            $this->db->query("
                UPDATE elections 
                SET title = :title, description = :description, 
                    start_date = :start_date, end_date = :end_date, status = :status,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $this->db->bind(':id', $id);
            $this->db->bind(':title', $data['title']);
            $this->db->bind(':description', $data['description']);
            $this->db->bind(':start_date', $data['start_date']);
            $this->db->bind(':end_date', $data['end_date']);
            $this->db->bind(':status', $data['status']);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Error updating election: ' . $e->getMessage());
            return false;
        }
    }
    
    public function updateElectionStatus($id, $status) {
        try {
            $this->db->query("UPDATE elections SET status = :status, updated_at = NOW() WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->bind(':status', $status);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Error updating election status: ' . $e->getMessage());
            return false;
        }
    }
    
    public function deleteElection($id) {
        try {
            // Delete related votes first
            $this->db->query('DELETE FROM votes WHERE election_id = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();
            
            // Delete related candidates
            $this->db->query('DELETE FROM candidates WHERE election_id = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();
            
            // Delete related positions
            $this->db->query('DELETE FROM positions WHERE election_id = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();
            
            // Delete election
            $this->db->query('DELETE FROM elections WHERE id = :id');
            $this->db->bind(':id', $id);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Error deleting election: ' . $e->getMessage());
            return false;
        }
    }
}

// Position class
class Position {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function addPosition($data) {
        try {
            $this->db->query("
                INSERT INTO positions (election_id, title, name, description, max_vote, display_order, priority) 
                VALUES (:election_id, :title, :name, :description, :max_vote, :display_order, :priority)
            ");
            
            $this->db->bind(':election_id', $data['election_id'] ?? 0);
            $this->db->bind(':title', $data['title'] ?? $data['name'] ?? '');
            $this->db->bind(':name', $data['name'] ?? '');
            $this->db->bind(':description', $data['description'] ?? '');
            $this->db->bind(':max_vote', $data['max_vote'] ?? 1);
            $this->db->bind(':display_order', $data['display_order'] ?? 0);
            $this->db->bind(':priority', $data['priority'] ?? 0);
            
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log('Error adding position: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getAllPositions($filters = []) {
        try {
            $sql = "SELECT * FROM positions WHERE 1=1";
            $params = [];
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = :status";
                $params[':status'] = $filters['status'];
            }
            
            if (!empty($filters['election_id'])) {
                $sql .= " AND election_id = :election_id";
                $params[':election_id'] = $filters['election_id'];
            }
            
            $sql .= " ORDER BY priority ASC, name ASC";
            
            $this->db->query($sql);
            
            // Bind parameters if any
            foreach ($params as $param => $value) {
                $this->db->bind($param, $value);
            }
            
            $this->db->execute();
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("Error getting all positions: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPositionsByElection($electionId) {
        try {
            $this->db->query('SELECT * FROM positions WHERE election_id = :election_id ORDER BY display_order ASC');
            $this->db->bind(':election_id', $electionId);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('Error getting positions: ' . $e->getMessage());
            return [];
        }
    }
}

// Candidate class
class Candidate {
    private $db;
    private $table = 'candidates';

    public function __construct() {
        $this->db = new Database();
    }

    public function getCandidatesByElection($electionId) {
        $this->db->query('SELECT c.*, p.name as position_name 
                         FROM ' . $this->table . ' c 
                         LEFT JOIN positions p ON c.position_id = p.id 
                         WHERE c.election_id = :election_id 
                         ORDER BY p.priority, c.lastname, c.firstname');
        $this->db->bind(':election_id', $electionId);
        return $this->db->resultSet();
    }

    public function getCandidateById($id) {
        $this->db->query('SELECT * FROM ' . $this->table . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    public function getAllCandidatesWithElections($activeOnly = true) {
        $sql = 'SELECT c.*, e.title as election_title, p.name as position_name, 
                e.status as election_status, e.start_date, e.end_date
                FROM ' . $this->table . ' c 
                LEFT JOIN elections e ON c.election_id = e.id 
                LEFT JOIN positions p ON c.position_id = p.id ';
        
        if ($activeOnly) {
            $sql .= ' WHERE e.status = "active" AND e.start_date <= NOW() AND e.end_date >= NOW() ';
        }
        
        $sql .= ' ORDER BY e.title, p.priority, c.lastname, c.firstname';
        
        $this->db->query($sql);
        return $this->db->resultSet();
    }
    
    public function addCandidate($data) {
        try {
            $this->db->query('INSERT INTO ' . $this->table . ' 
                            (firstname, lastname, name, position_id, election_id, platform, bio, photo, status) 
                            VALUES (:firstname, :lastname, :name, :position_id, :election_id, :platform, :bio, :photo, :status)');
            
            // Bind values
            $this->db->bind(':firstname', $data['firstname']);
            $this->db->bind(':lastname', $data['lastname']);
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':position_id', $data['position_id'], PDO::PARAM_INT);
            $this->db->bind(':election_id', $data['election_id'], PDO::PARAM_INT);
            $this->db->bind(':platform', $data['platform']);
            $this->db->bind(':bio', $data['bio']);
            $this->db->bind(':photo', $data['photo'] ?? null);
            $this->db->bind(':status', $data['status'] ? 1 : 0, PDO::PARAM_INT);
            
            // Execute
            $this->db->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('Error adding candidate: ' . $e->getMessage());
            return false;
        }
    }
    
    public function updateCandidate($id, $data) {
        try {
            $sql = 'UPDATE ' . $this->table . ' SET 
                    firstname = :firstname,
                    lastname = :lastname,
                    name = :name,
                    position_id = :position_id,
                    election_id = :election_id,
                    platform = :platform,
                    bio = :bio,' . 
                    (isset($data['photo']) ? ' photo = :photo,' : '') . '
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id';
            
            $this->db->query($sql);
            
            // Bind values
            $this->db->bind(':firstname', $data['firstname']);
            $this->db->bind(':lastname', $data['lastname']);
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':position_id', $data['position_id'], PDO::PARAM_INT);
            $this->db->bind(':election_id', $data['election_id'], PDO::PARAM_INT);
            $this->db->bind(':platform', $data['platform']);
            $this->db->bind(':bio', $data['bio']);
            if (isset($data['photo'])) {
                $this->db->bind(':photo', $data['photo']);
            }
            $this->db->bind(':status', $data['status'] ? 1 : 0, PDO::PARAM_INT);
            $this->db->bind(':id', $id, PDO::PARAM_INT);
            
            // Execute
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Error updating candidate: ' . $e->getMessage());
            return false;
        }
    }
    
    public function deleteCandidate($id) {
        try {
            $this->db->query('DELETE FROM ' . $this->table . ' WHERE id = :id');
            $this->db->bind(':id', $id, PDO::PARAM_INT);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log('Error deleting candidate: ' . $e->getMessage());
            return false;
        }
    }
}

// Vote class
class Vote {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function castVote($electionId, $candidateId, $computerNumber, $ipAddress, $userAgent) {
        try {
            // Check if this computer number has already voted in this election
            $this->db->query('SELECT COUNT(*) as count FROM votes v INNER JOIN voters vr ON v.voter_id = vr.id WHERE v.election_id = :election_id AND vr.voter_id = :computer_number');
            $this->db->bind(':election_id', $electionId);
            $this->db->bind(':computer_number', $computerNumber);
            $result = $this->db->single();
            
            if ($result['count'] > 0) {
                return false; // Already voted
            }
            
            // Get or create voter record
            $voterId = $this->getOrCreateVoter($computerNumber);
            if (!$voterId) {
                return false;
            }
            
            // Get candidate info to get position_id
            $this->db->query('SELECT position_id FROM candidates WHERE id = :id');
            $this->db->bind(':id', $candidateId);
            $candidate = $this->db->single();
            if (!$candidate) {
                return false;
            }

            // Record the vote
            $this->db->query('INSERT INTO votes (voter_id, election_id, position_id, candidate_id, computer_number, ip_address, user_agent, voted_at) 
                             VALUES (:voter_id, :election_id, :position_id, :candidate_id, :computer_number, :ip_address, :user_agent, NOW())');
            
            $this->db->bind(':voter_id', $voterId);
            $this->db->bind(':election_id', $electionId);
            $this->db->bind(':position_id', $candidate['position_id']);
            $this->db->bind(':candidate_id', $candidateId);
            $this->db->bind(':computer_number', $computerNumber);
            $this->db->bind(':ip_address', $ipAddress);
            $this->db->bind(':user_agent', $userAgent);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Error casting vote: ' . $e->getMessage());
            return false;
        }
    }
    
    private function getOrCreateVoter($computerNumber) {
        try {
            // First check if voter already exists
            $this->db->query('SELECT id FROM voters WHERE voter_id = :computer_number');
            $this->db->bind(':computer_number', $computerNumber);
            $existingVoter = $this->db->single();
            
            if ($existingVoter) {
                return $existingVoter['id'];
            }
            
            // Create new voter record
            $this->db->query('INSERT INTO voters (voter_id, firstname, lastname, password, status) VALUES (:voter_id, :firstname, :lastname, :password, 1)');
            
            $this->db->bind(':voter_id', $computerNumber);
            $this->db->bind(':firstname', 'Student');
            $this->db->bind(':lastname', substr($computerNumber, -4));
            $this->db->bind(':password', password_hash($computerNumber, PASSWORD_DEFAULT));
            
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Error getting/creating voter: ' . $e->getMessage());
            return false;
        }
    }
}

// Position class is now loaded via autoloader from /application/models/Position.php
