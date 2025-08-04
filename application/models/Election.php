<?php

class Election {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get all active elections
     * @return array
     */
    public function getActiveElections() {
        try {
            $this->db->query("
                SELECT * FROM elections 
                WHERE status = 'active' 
                AND start_date <= NOW() 
                AND end_date >= NOW() 
                ORDER BY created_at DESC
            ");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('Error getting active elections: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all elections
     * @param array $filters
     * @return array
     */
    public function getAllElections($filters = []) {
        try {
            $sql = "SELECT * FROM elections";
            $conditions = [];
            $params = [];
            
            if (isset($filters['status'])) {
                $conditions[] = "status = :status";
                $params[':status'] = $filters['status'];
            }
            
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(' AND ', $conditions);
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $this->db->query($sql);
            foreach ($params as $key => $value) {
                $this->db->bind($key, $value);
            }
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('Error getting elections: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get election by ID
     * @param int $id
     * @return array|false
     */
    public function getElectionById($id) {
        try {
            $this->db->query('SELECT * FROM elections WHERE id = :id LIMIT 1');
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (Exception $e) {
            error_log('Error getting election by ID: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new election
     * @param array $data
     * @return int|false Election ID if successful, false otherwise
     */
    public function createElection($data) {
        try {
            $this->db->query("
                INSERT INTO elections (title, name, description, start_date, end_date, status, created_by) 
                VALUES (:title, :name, :description, :start_date, :end_date, :status, :created_by)
            ");
            
            $this->db->bind(':title', $data['title']);
            $this->db->bind(':name', $data['name'] ?? $data['title']);
            $this->db->bind(':description', $data['description']);
            $this->db->bind(':start_date', $data['start_date']);
            $this->db->bind(':end_date', $data['end_date']);
            $this->db->bind(':status', $data['status'] ?? 'draft');
            $this->db->bind(':created_by', $data['created_by']);
            
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log('Error creating election: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update election
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateElection($id, $data) {
        try {
            $this->db->query("
                UPDATE elections 
                SET title = :title, name = :name, description = :description, 
                    start_date = :start_date, end_date = :end_date, status = :status,
                    updated_at = NOW()
                WHERE id = :id
            ");
            
            $this->db->bind(':id', $id);
            $this->db->bind(':title', $data['title']);
            $this->db->bind(':name', $data['name'] ?? $data['title']);
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
    
    /**
     * Update election status
     * @param int $id
     * @param string $status
     * @return bool
     */
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
    
    /**
     * Delete election
     * @param int $id
     * @return bool
     */
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
    
    /**
     * Get election statistics
     * @param int $electionId
     * @return array
     */
    public function getElectionStats($electionId) {
        try {
            // Get total votes
            $this->db->query('SELECT COUNT(*) as total_votes FROM votes WHERE election_id = :id');
            $this->db->bind(':id', $electionId);
            $totalVotes = $this->db->single()['total_votes'];
            
            // Get total candidates
            $this->db->query('SELECT COUNT(*) as total_candidates FROM candidates WHERE election_id = :id');
            $this->db->bind(':id', $electionId);
            $totalCandidates = $this->db->single()['total_candidates'];
            
            // Get votes by candidate
            $this->db->query("
                SELECT c.name, c.id, COUNT(v.id) as vote_count
                FROM candidates c
                LEFT JOIN votes v ON c.id = v.candidate_id
                WHERE c.election_id = :id
                GROUP BY c.id, c.name
                ORDER BY vote_count DESC
            ");
            $this->db->bind(':id', $electionId);
            $candidateVotes = $this->db->resultSet();
            
            return [
                'total_votes' => $totalVotes,
                'total_candidates' => $totalCandidates,
                'candidate_votes' => $candidateVotes
            ];
        } catch (Exception $e) {
            error_log('Error getting election stats: ' . $e->getMessage());
            return [
                'total_votes' => 0,
                'total_candidates' => 0,
                'candidate_votes' => []
            ];
        }
    }
    
    /**
     * Check if election is active
     * @param int $electionId
     * @return bool
     */
    public function isElectionActive($electionId) {
        try {
            $this->db->query("
                SELECT COUNT(*) as count FROM elections 
                WHERE id = :id AND status = 'active' 
                AND start_date <= NOW() AND end_date >= NOW()
            ");
            $this->db->bind(':id', $electionId);
            $result = $this->db->single();
            return $result['count'] > 0;
        } catch (Exception $e) {
            error_log('Error checking if election is active: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get elections with candidate count
     * @return array
     */
    public function getElectionsWithCandidateCount() {
        try {
            $this->db->query("
                SELECT e.*, COUNT(c.id) as candidate_count
                FROM elections e
                LEFT JOIN candidates c ON e.id = c.election_id
                GROUP BY e.id
                ORDER BY e.created_at DESC
            ");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('Error getting elections with candidate count: ' . $e->getMessage());
            return [];
        }
    }
}
