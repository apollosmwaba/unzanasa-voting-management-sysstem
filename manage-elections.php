<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Require admin authentication
Auth::requireAuth();

// Initialize variables
$electionModel = new Election();
$message = '';
$messageType = '';
$elections = []; // Initialize as empty array by default

// Handle form submission for adding/editing an election
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $status = $_POST['status'] ?? 'inactive';
    $maxVotes = (int)($_POST['max_votes'] ?? 1);
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Election name is required';
    }
    
    if (empty($startDate)) {
        $errors[] = 'Start date is required';
    }
    
    if (empty($endDate)) {
        $errors[] = 'End date is required';
    }
    
    if (!empty($startDate) && !empty($endDate) && $endDate <= $startDate) {
        $errors[] = 'End date must be after start date';
    }
    
    if ($maxVotes < 1) {
        $errors[] = 'Maximum votes per voter must be at least 1';
    }
    
    // If no validation errors, save the election
    if (empty($errors)) {
        $electionData = [
            'name' => $name,
            'description' => $description,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'max_votes' => $maxVotes
        ];
        
        try {
            if ($id) {
                // Update existing election
                $electionModel->updateElection($id, $electionData);
                $message = 'Election updated successfully';
            } else {
                // Create new election
                $electionModel->createElection($electionData);
                $message = 'Election created successfully';
            }
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Error saving election: ' . $e->getMessage();
            $messageType = 'danger';
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'danger';
    }
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $electionModel->deleteElection($_GET['id']);
        $message = 'Election deleted successfully';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Error deleting election: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Handle status toggle
if (isset($_GET['action']) && isset($_GET['id']) && ($_GET['action'] === 'activate' || $_GET['action'] === 'deactivate')) {
    $newStatus = $_GET['action'] === 'activate' ? 'active' : 'inactive';
    try {
        $electionModel->updateElectionStatus($_GET['id'], $newStatus);
        $message = 'Election status updated successfully';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Error updating election status: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Get all elections
try {
    $elections = $electionModel->getAllElections() ?? [];
} catch (Exception $e) {
    $elections = []; // Ensure it's still an array on error
    $message = 'Error loading elections: ' . $e->getMessage();
    $messageType = 'danger';
    error_log($message);
}

// Get election for editing if ID is provided
$editElection = null;
if (isset($_GET['edit'])) {
    try {
        $editElection = $electionModel->getElectionById($_GET['edit']);
    } catch (Exception $e) {
        $message = 'Error loading election for editing: ' . $e->getMessage();
        $messageType = 'danger';
        error_log($message);
    }
}

// Include the view
include __DIR__ . '/application/views/manage-elections.php';
?>
