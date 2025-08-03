<?php
class Position {
    private $db;
    private $table = 'positions';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all positions
     * @param array $filters Optional filters
     * @return array List of positions
     */
    public function getAllPositions($filters = []) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE 1=1";
            $params = [];
            
            if (!empty($filters['status'])) {
                $sql .= " AND status = :status";
                $params[':status'] = $filters['status'];
            }
            
            $sql .= " ORDER BY priority ASC, name ASC";
            
            $this->db->query($sql);
            
            // Bind parameters if any
            foreach ($params as $param => $value) {
                $this->db->bind($param, $value);
            }
            
            $this->db->execute();
            return $this->db->resultSet();
        } catch (PDOException $e) {
            error_log("Error getting positions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get position by ID
     * @param int $id Position ID
     * @return array|null Position data or null if not found
     */
    public function getPositionById($id) {
        try {
            $sql = "SELECT * FROM " . $this->table . " WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':id', $id);
            
            $row = $this->db->single();
            return $row;
        } catch (PDOException $e) {
            error_log("Error getting position by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add a new position
     * @param array $data Position data
     * @return int|bool New position ID or false on failure
     */
    public function addPosition($data) {
        try {
            $sql = "INSERT INTO " . $this->table . " (name, description, max_vote, priority, status) 
                    VALUES (:name, :description, :max_vote, :priority, :status)";
            
            $this->db->query($sql);
            
            // Bind values
            $this->db->bind(':name', $data['name'] ?? '');
            $this->db->bind(':description', $data['description'] ?? '');
            $this->db->bind(':max_vote', $data['max_vote'] ?? 1, PDO::PARAM_INT);
            $this->db->bind(':priority', $data['priority'] ?? 0, PDO::PARAM_INT);
            $this->db->bind(':status', $data['status'] ?? 1, PDO::PARAM_INT);
            
            // Execute
            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error adding position: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a position
     * @param int $id Position ID
     * @param array $data Position data
     * @return bool True on success, false on failure
     */
    public function updatePosition($id, $data) {
        try {
            $sql = "UPDATE " . $this->table . " SET 
                    name = :name, 
                    description = :description, 
                    max_vote = :max_vote, 
                    priority = :priority, 
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP 
                    WHERE id = :id";
            
            $this->db->query($sql);
            
            // Bind values
            $this->db->bind(':name', $data['name'] ?? '');
            $this->db->bind(':description', $data['description'] ?? '');
            $this->db->bind(':max_vote', $data['max_vote'] ?? 1, PDO::PARAM_INT);
            $this->db->bind(':priority', $data['priority'] ?? 0, PDO::PARAM_INT);
            $this->db->bind(':status', $data['status'] ?? 1, PDO::PARAM_INT);
            $this->db->bind(':id', $id, PDO::PARAM_INT);
            
            // Execute
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Error updating position: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a position
     * @param int $id Position ID
     * @return bool True on success, false on failure
     */
    public function deletePosition($id) {
        try {
            // First, check if there are any candidates associated with this position
            $candidateModel = new Candidate();
            $candidates = $candidateModel->getCandidatesByPosition($id);
            
            if (!empty($candidates)) {
                error_log("Cannot delete position: There are candidates associated with this position");
                return false;
            }
            
            $sql = "DELETE FROM " . $this->table . " WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':id', $id, PDO::PARAM_INT);
            return $this->db->execute();
        } catch (PDOException $e) {
            error_log("Error deleting position: " . $e->getMessage());
            return false;
        }
    }
}
?>
