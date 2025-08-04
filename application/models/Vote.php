<?php

class Vote {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Cast a vote
     * @param int $electionId
     * @param int $candidateId
     * @param string $computerNumber
     * @param string $ipAddress
     * @param string $userAgent
     * @return bool
     */
    public function castVote($electionId, $candidateId, $computerNumber, $ipAddress, $userAgent = null) {
        try {
            // Check if computer number has already voted for this election
            if ($this->hasVoted($electionId, $computerNumber)) {
                return false;
            }
            
            // Validate computer number
            if (!$this->isValidComputerNumber($computerNumber)) {
                return false;
            }
            
            // Get candidate and position information
            $candidate = $this->getCandidateInfo($candidateId);
            if (!$candidate) {
                return false;
            }
            
            // Get or create voter record
            $voterId = $this->getOrCreateVoter($computerNumber);
            if (!$voterId) {
                return false;
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Insert vote record
            $this->db->query("
                INSERT INTO votes (voter_id, election_id, position_id, candidate_id, voted_at) 
                VALUES (:voter_id, :election_id, :position_id, :candidate_id, NOW())
            ");
            
            $this->db->bind(':voter_id', $voterId);
            $this->db->bind(':election_id', $electionId);
            $this->db->bind(':position_id', $candidate['position_id']);
            $this->db->bind(':candidate_id', $candidateId);
            
            if (!$this->db->execute()) {
                $this->db->rollBack();
                return false;
            }
            
            // Log the voting action
            $this->logVotingAction($computerNumber, $electionId, 'vote_cast', 
                'Vote cast for candidate ID: ' . $candidateId, $ipAddress);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error casting vote: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a computer number has already voted in an election
     * @param int $electionId
     * @param string $computerNumber
     * @return bool
     */
    public function hasVoted($electionId, $computerNumber) {
        try {
            $this->db->query("
                SELECT COUNT(*) as count FROM votes v 
                INNER JOIN voters vr ON v.voter_id = vr.id 
                WHERE v.election_id = :election_id AND vr.voter_id = :computer_number
            ");
            $this->db->bind(':election_id', $electionId);
            $this->db->bind(':computer_number', $computerNumber);
            
            $result = $this->db->single();
            return $result['count'] > 0;
        } catch (Exception $e) {
            error_log('Error checking if voted: ' . $e->getMessage());
            return true; // Assume voted to prevent duplicate voting
        }
    }
    
    /**
     * Validate computer number
     * @param string $computerNumber
     * @return bool
     */
    private function isValidComputerNumber($computerNumber) {
        try {
            // Check if computer number exists in valid_computer_numbers table
            $this->db->query("
                SELECT COUNT(*) as count FROM valid_computer_numbers 
                WHERE computer_number = :computer_number AND is_active = 1
            ");
            $this->db->bind(':computer_number', $computerNumber);
            
            $result = $this->db->single();
            return $result['count'] > 0;
        } catch (Exception $e) {
            error_log('Error validating computer number: ' . $e->getMessage());
            // If validation table doesn't exist or error occurs, allow basic validation
            return preg_match('/^[0-9]{10}$/', $computerNumber);
        }
    }
    
    /**
     * Get candidate information
     * @param int $candidateId
     * @return array|false
     */
    private function getCandidateInfo($candidateId) {
        try {
            $this->db->query("
                SELECT c.*, c.position_id 
                FROM candidates c 
                WHERE c.id = :id
            ");
            $this->db->bind(':id', $candidateId);
            return $this->db->single();
        } catch (Exception $e) {
            error_log('Error getting candidate info: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get or create voter record for computer number
     * @param string $computerNumber
     * @return int|false voter ID or false on failure
     */
    private function getOrCreateVoter($computerNumber) {
        try {
            // First check if voter already exists
            $this->db->query("
                SELECT id FROM voters WHERE voter_id = :computer_number
            ");
            $this->db->bind(':computer_number', $computerNumber);
            $existingVoter = $this->db->single();
            
            if ($existingVoter) {
                return $existingVoter['id'];
            }
            
            // Create new voter record
            $this->db->query("
                INSERT INTO voters (voter_id, firstname, lastname, password, status) 
                VALUES (:voter_id, :firstname, :lastname, :password, 1)
            ");
            
            $this->db->bind(':voter_id', $computerNumber);
            $this->db->bind(':firstname', 'Student');
            $this->db->bind(':lastname', substr($computerNumber, -4)); // Use last 4 digits as lastname
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
    
    /**
     * Log voting action
     * @param string $computerNumber
     * @param int $electionId
     * @param string $action
     * @param string $details
     * @param string $ipAddress
     */
    private function logVotingAction($computerNumber, $electionId, $action, $details, $ipAddress) {
        try {
            $this->db->query("
                INSERT INTO voting_logs (computer_number, election_id, action, details, ip_address, timestamp) 
                VALUES (:computer_number, :election_id, :action, :details, :ip_address, NOW())
            ");
            
            $this->db->bind(':computer_number', $computerNumber);
            $this->db->bind(':election_id', $electionId);
            $this->db->bind(':action', $action);
            $this->db->bind(':details', $details);
            $this->db->bind(':ip_address', $ipAddress);
            
            $this->db->execute();
        } catch (Exception $e) {
            error_log('Error logging voting action: ' . $e->getMessage());
        }
    }
    
    /**
     * Get vote results for an election
     * @param int $electionId
     * @return array
     */
    public function getElectionResults($electionId) {
        try {
            $this->db->query("
                SELECT c.id, c.name, c.photo, p.name as position_name, 
                       COUNT(v.id) as vote_count
                FROM candidates c
                INNER JOIN positions p ON c.position_id = p.id
                LEFT JOIN votes v ON c.id = v.candidate_id
                WHERE c.election_id = :election_id
                GROUP BY c.id, c.name, c.photo, p.name
                ORDER BY p.display_order ASC, vote_count DESC
            ");
            
            $this->db->bind(':election_id', $electionId);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('Error getting election results: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get total votes for an election
     * @param int $electionId
     * @return int
     */
    public function getTotalVotes($electionId) {
        try {
            $this->db->query("
                SELECT COUNT(*) as total FROM votes WHERE election_id = :election_id
            ");
            $this->db->bind(':election_id', $electionId);
            
            $result = $this->db->single();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log('Error getting total votes: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get votes by position for an election
     * @param int $electionId
     * @param int $positionId
     * @return array
     */
    public function getVotesByPosition($electionId, $positionId) {
        try {
            $this->db->query("
                SELECT c.id, c.name, c.photo, COUNT(v.id) as vote_count
                FROM candidates c
                LEFT JOIN votes v ON c.id = v.candidate_id
                WHERE c.election_id = :election_id AND c.position_id = :position_id
                GROUP BY c.id, c.name, c.photo
                ORDER BY vote_count DESC
            ");
            
            $this->db->bind(':election_id', $electionId);
            $this->db->bind(':position_id', $positionId);
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('Error getting votes by position: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get voting statistics
     * @param int $electionId
     * @return array
     */
    public function getVotingStats($electionId) {
        try {
            // Total votes
            $totalVotes = $this->getTotalVotes($electionId);
            
            // Unique voters
            $this->db->query("
                SELECT COUNT(DISTINCT computer_number) as unique_voters 
                FROM votes WHERE election_id = :election_id
            ");
            $this->db->bind(':election_id', $electionId);
            $uniqueVoters = $this->db->single()['unique_voters'] ?? 0;
            
            // Votes by hour (last 24 hours)
            $this->db->query("
                SELECT HOUR(voted_at) as hour, COUNT(*) as count
                FROM votes 
                WHERE election_id = :election_id 
                AND voted_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY HOUR(voted_at)
                ORDER BY hour
            ");
            $this->db->bind(':election_id', $electionId);
            $hourlyVotes = $this->db->resultSet();
            
            return [
                'total_votes' => $totalVotes,
                'unique_voters' => $uniqueVoters,
                'hourly_votes' => $hourlyVotes
            ];
        } catch (Exception $e) {
            error_log('Error getting voting stats: ' . $e->getMessage());
            return [
                'total_votes' => 0,
                'unique_voters' => 0,
                'hourly_votes' => []
            ];
        }
    }
    
    /**
     * Get voting logs
     * @param int $electionId
     * @param int $limit
     * @return array
     */
    public function getVotingLogs($electionId, $limit = 100) {
        try {
            $this->db->query("
                SELECT * FROM voting_logs 
                WHERE election_id = :election_id 
                ORDER BY timestamp DESC 
                LIMIT :limit
            ");
            
            $this->db->bind(':election_id', $electionId);
            $this->db->bind(':limit', $limit);
            
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('Error getting voting logs: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete all votes for an election (admin only)
     * @param int $electionId
     * @return bool
     */
    public function deleteElectionVotes($electionId) {
        try {
            $this->db->query('DELETE FROM votes WHERE election_id = :election_id');
            $this->db->bind(':election_id', $electionId);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log('Error deleting election votes: ' . $e->getMessage());
            return false;
        }
    }
}
