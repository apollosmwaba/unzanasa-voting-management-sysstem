<?php

class Admin {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Authenticate admin user
     * @param string $username
     * @param string $password
     * @return array|false User data if successful, false otherwise
     */
    public function authenticate($username, $password) {
        try {
            // Get admin by username
            $this->db->query('SELECT * FROM admins WHERE username = :username LIMIT 1');
            $this->db->bind(':username', $username);
            $user = $this->db->single();
            
            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                $this->updateLastLogin($user['id']);
                return $user;
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Admin authentication error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create admin session
     * @param int $adminId
     */
    public function createSession($adminId) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $adminId;
        $_SESSION['admin_login_time'] = time();
        
        // Store session in database
        $sessionId = session_id();
        $this->db->query('INSERT INTO admin_sessions (admin_id, session_id, created_at) VALUES (:admin_id, :session_id, NOW()) ON DUPLICATE KEY UPDATE created_at = NOW()');
        $this->db->bind(':admin_id', $adminId);
        $this->db->bind(':session_id', $sessionId);
        $this->db->execute();
    }
    
    /**
     * Update last login timestamp
     * @param int $adminId
     */
    private function updateLastLogin($adminId) {
        $this->db->query('UPDATE admins SET last_login = NOW() WHERE id = :id');
        $this->db->bind(':id', $adminId);
        $this->db->execute();
    }
    
    /**
     * Get admin by ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        $this->db->query('SELECT * FROM admins WHERE id = :id LIMIT 1');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    /**
     * Get admin by username
     * @param string $username
     * @return array|false
     */
    public function getByUsername($username) {
        $this->db->query('SELECT * FROM admins WHERE username = :username LIMIT 1');
        $this->db->bind(':username', $username);
        return $this->db->single();
    }
    

    
    /**
     * Update admin password
     * @param int $adminId
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword($adminId, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $this->db->query('UPDATE admins SET password = :password WHERE id = :id');
            $this->db->bind(':password', $hashedPassword);
            $this->db->bind(':id', $adminId);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Admin password update error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Logout admin
     * @param int $adminId
     */
    public function logout($adminId = null) {
        // Remove session from database
        if ($adminId) {
            $this->db->query('DELETE FROM admin_sessions WHERE admin_id = :admin_id');
            $this->db->bind(':admin_id', $adminId);
            $this->db->execute();
        }
        
        // Clear session variables
        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_login_time']);
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Check if admin session is valid
     * @return bool
     */
    public function isValidSession() {
        if (!isset($_SESSION['admin_logged_in']) || !isset($_SESSION['admin_id'])) {
            return false;
        }
        
        // Check if session exists in database
        $sessionId = session_id();
        $this->db->query('SELECT COUNT(*) as count FROM admin_sessions WHERE admin_id = :admin_id AND session_id = :session_id');
        $this->db->bind(':admin_id', $_SESSION['admin_id']);
        $this->db->bind(':session_id', $sessionId);
        $result = $this->db->single();
        
        return $result['count'] > 0;
    }
    
    /**
     * Get all admins
     * @return array
     */
    public function getAll() {
        $this->db->query('SELECT id, username, email, full_name, created_at, last_login FROM admins ORDER BY created_at DESC');
        return $this->db->resultSet();
    }
    
    /**
     * Delete admin
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        try {
            // Don't allow deleting the last admin
            $this->db->query('SELECT COUNT(*) as count FROM admins');
            $result = $this->db->single();
            
            if ($result['count'] <= 1) {
                return false; // Can't delete the last admin
            }
            
            // Delete admin sessions first
            $this->db->query('DELETE FROM admin_sessions WHERE admin_id = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();
            
            // Delete admin
            $this->db->query('DELETE FROM admins WHERE id = :id');
            $this->db->bind(':id', $id);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Admin deletion error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a new admin user
     * @param array $data Admin data (username, email, password, full_name)
     * @return bool
     */
    public function create($data) {
        try {
            // Validate required fields
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                return false;
            }
            
            // Check if username already exists
            $this->db->query('SELECT id FROM admins WHERE username = :username LIMIT 1');
            $this->db->bind(':username', $data['username']);
            if ($this->db->single()) {
                return false; // Username already exists
            }
            
            // Check if email already exists
            $this->db->query('SELECT id FROM admins WHERE email = :email LIMIT 1');
            $this->db->bind(':email', $data['email']);
            if ($this->db->single()) {
                return false; // Email already exists
            }
            
            // Hash the password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Insert new admin
            $this->db->query('INSERT INTO admins (username, email, password, full_name, created_at) VALUES (:username, :email, :password, :full_name, NOW())');
            $this->db->bind(':username', $data['username']);
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':password', $hashedPassword);
            $this->db->bind(':full_name', $data['full_name'] ?? '');
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Admin creation error: ' . $e->getMessage());
            return false;
        }
    }
}
