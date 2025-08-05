<?php

class Candidate {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get all candidates
     * @return array
     */
    public function getAllCandidates() {
        try {
            $this->db->query("
                SELECT c.*, e.title as election_title, p.name as position_name
                FROM candidates c
                LEFT JOIN elections e ON c.election_id = e.id
                LEFT JOIN positions p ON c.position_id = p.id
                ORDER BY c.created_at DESC
            ");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('Error getting all candidates: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all candidates with election information
     * @return array
     */
    public function getAllCandidatesWithElections() {
        try {
            $this->db->query("
                SELECT c.*, e.title as election_title, e.name as election_name, 
                       p.name as position_name, p.title as position_title
                FROM candidates c
                LEFT JOIN elections e ON c.election_id = e.id
                LEFT JOIN positions p ON c.position_id = p.id
                ORDER BY c.created_at DESC
            ");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('Error getting candidates with elections: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get candidates by election ID
     * @param int $electionId
     * @return array
     */
    public function getCandidatesByElection($electionId) {
        try {
            $this->db->query("
                SELECT c.*, p.name as position_name, p.title as position_title
                FROM candidates c
                LEFT JOIN positions p ON c.position_id = p.id
                WHERE c.election_id = :election_id AND c.status = 1
                ORDER BY p.display_order ASC, c.name ASC
            ");
            $this->db->bind(':election_id', $electionId);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('Error getting candidates by election: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get candidate by ID
     * @param int $id
     * @return array|false
     */
    public function getCandidateById($id) {
        try {
            $this->db->query("
                SELECT c.*, e.title as election_title, p.name as position_name
                FROM candidates c
                LEFT JOIN elections e ON c.election_id = e.id
                LEFT JOIN positions p ON c.position_id = p.id
                WHERE c.id = :id
                LIMIT 1
            ");
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (Exception $e) {
            error_log('Error getting candidate by ID: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create new candidate
     * @param array $data
     * @return int|false Candidate ID if successful, false otherwise
     */
    public function createCandidate($data) {
        try {
            $this->db->query("
                INSERT INTO candidates (firstname, lastname, name, position_id, election_id, photo, platform, bio, status) 
                VALUES (:firstname, :lastname, :name, :position_id, :election_id, :photo, :platform, :bio, :status)
            ");
            
            $this->db->bind(':firstname', $data['firstname']);
            $this->db->bind(':lastname', $data['lastname']);
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':position_id', $data['position_id']);
            $this->db->bind(':election_id', $data['election_id']);
            $this->db->bind(':photo', $data['photo'] ?? null);
            $this->db->bind(':platform', $data['platform']);
            $this->db->bind(':bio', $data['bio'] ?? null);
            $this->db->bind(':status', $data['status'] ?? 1);
            
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log('Error creating candidate: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add new candidate (alias for createCandidate)
     * @param array $data
     * @return int|false Candidate ID if successful, false otherwise
     */
    public function addCandidate($data) {
        return $this->createCandidate($data);
    }
    
    /**
     * Update candidate
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCandidate($id, $data) {
        try {
            $this->db->query("
                UPDATE candidates 
                SET firstname = :firstname, lastname = :lastname, name = :name, 
                    position_id = :position_id, election_id = :election_id, 
                    photo = :photo, platform = :platform, bio = :bio, 
                    status = :status, updated_at = NOW()
                WHERE id = :id
            ");
            
            $this->db->bind(':id', $id);
            $this->db->bind(':firstname', $data['firstname']);
            $this->db->bind(':lastname', $data['lastname']);
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':position_id', $data['position_id']);
            $this->db->bind(':election_id', $data['election_id']);
            $this->db->bind(':photo', $data['photo']);
            $this->db->bind(':platform', $data['platform']);
            $this->db->bind(':bio', $data['bio']);
            $this->db->bind(':status', $data['status'] ?? 1);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Error updating candidate: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete candidate
     * @param int $id
     * @return bool
     */
    public function deleteCandidate($id) {
        try {
            // Delete related votes first
            $this->db->query('DELETE FROM votes WHERE candidate_id = :id');
            $this->db->bind(':id', $id);
            $this->db->execute();
            
            // Delete candidate
            $this->db->query('DELETE FROM candidates WHERE id = :id');
            $this->db->bind(':id', $id);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Error deleting candidate: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get candidates by position
     * @param int $positionId
     * @return array
     */
    public function getCandidatesByPosition($positionId) {
        try {
            $this->db->query("
                SELECT c.*, e.title as election_title
                FROM candidates c
                LEFT JOIN elections e ON c.election_id = e.id
                WHERE c.position_id = :position_id AND c.status = 1
                ORDER BY c.name ASC
            ");
            $this->db->bind(':position_id', $positionId);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('Error getting candidates by position: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get active candidates for an election
     * @param int $electionId
     * @return array
     */
    public function getActiveCandidatesByElection($electionId) {
        try {
            $this->db->query("
                SELECT c.*, p.name as position_name, p.title as position_title,
                       p.display_order, p.id as position_id
                FROM candidates c
                INNER JOIN positions p ON c.position_id = p.id
                WHERE c.election_id = :election_id AND c.status = 1
                ORDER BY p.display_order ASC, c.name ASC
            ");
            $this->db->bind(':election_id', $electionId);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('Error getting active candidates by election: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get candidate vote count
     * @param int $candidateId
     * @return int
     */
    public function getCandidateVoteCount($candidateId) {
        try {
            $this->db->query('SELECT COUNT(*) as vote_count FROM votes WHERE candidate_id = :id');
            $this->db->bind(':id', $candidateId);
            $result = $this->db->single();
            return $result['vote_count'] ?? 0;
        } catch (Exception $e) {
            error_log('Error getting candidate vote count: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Search candidates
     * @param string $searchTerm
     * @return array
     */
    public function searchCandidates($searchTerm) {
        try {
            $this->db->query("
                SELECT c.*, e.title as election_title, p.name as position_name
                FROM candidates c
                LEFT JOIN elections e ON c.election_id = e.id
                LEFT JOIN positions p ON c.position_id = p.id
                WHERE c.firstname LIKE :search OR c.lastname LIKE :search 
                      OR c.name LIKE :search OR c.platform LIKE :search
                ORDER BY c.name ASC
            ");
            $searchPattern = '%' . $searchTerm . '%';
            $this->db->bind(':search', $searchPattern);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('Error searching candidates: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Toggle candidate status
     * @param int $id
     * @return bool
     */
    public function toggleCandidateStatus($id) {
        try {
            $this->db->query("
                UPDATE candidates 
                SET status = CASE WHEN status = 1 THEN 0 ELSE 1 END,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Error toggling candidate status: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get candidates count by election
     * @param int $electionId
     * @return int
     */
    public function getCandidatesCountByElection($electionId) {
        try {
            $this->db->query('SELECT COUNT(*) as count FROM candidates WHERE election_id = :id');
            $this->db->bind(':id', $electionId);
            $result = $this->db->single();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log('Error getting candidates count: ' . $e->getMessage());
            return 0;
        }
    }
}
