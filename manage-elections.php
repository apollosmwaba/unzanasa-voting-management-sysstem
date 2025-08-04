<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Require admin authentication
Auth::requireAuth();

// Initialize variables
$electionModel = new Election();
$positionModel = new Position();
$message = '';
$messageType = '';
$elections = []; // Initialize as empty array by default

// Handle flash messages
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $messageType = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}

// Handle form submission for adding/editing an election
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $status = $_POST['status'] ?? 'inactive';
    $maxVotes = (int)($_POST['max_votes'] ?? 1);
    
    // Validate input
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Election title is required';
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
    
    // Validate positions
    $positions = $_POST['positions'] ?? [];
    if (empty($positions)) {
        $errors[] = 'At least one position is required';
    } else {
        foreach ($positions as $index => $position) {
            if (empty(trim($position['title'] ?? ''))) {
                $errors[] = "Position #" . ($index + 1) . " title is required";
            }
        }
    }
    
    // If no validation errors, save the election
    if (empty($errors)) {
        $electionData = [
            'title' => $title,
            'name' => $title, // Use title as name
            'description' => $description,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'created_by' => $_SESSION['admin_user']['id'] ?? 1
        ];
        
        try {
            if ($id) {
                // Update existing election
                $electionModel->updateElection($id, $electionData);
                
                // For now, just update the election. Position management can be added later
                // TODO: Add position update functionality
                
                $message = 'Election updated successfully';
            } else {
                // Create new election
                $electionId = $electionModel->createElection($electionData);
                
                // Create positions
                foreach ($positions as $position) {
                    $positionData = [
                        'election_id' => $electionId,
                        'title' => trim($position['title']),
                        'name' => trim($position['title']),
                        'description' => trim($position['description'] ?? ''),
                        'max_vote' => (int)($position['max_vote'] ?? 1),
                        'display_order' => (int)($position['display_order'] ?? 1),
                        'priority' => (int)($position['display_order'] ?? 1)
                    ];
                    $positionModel->addPosition($positionData);
                }
                
                $message = 'Election and positions created successfully';
            }
            $messageType = 'success';
            // Redirect to prevent form resubmission
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $messageType;
            header('Location: manage-elections.php');
            exit;
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
        $_SESSION['flash_message'] = 'Election deleted successfully';
        $_SESSION['flash_type'] = 'success';
    } catch (Exception $e) {
        $_SESSION['flash_message'] = 'Error deleting election: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'danger';
    }
    header('Location: manage-elections.php');
    exit;
}

// Handle status toggle
if (isset($_GET['action']) && isset($_GET['id']) && ($_GET['action'] === 'activate' || $_GET['action'] === 'deactivate')) {
    $newStatus = $_GET['action'] === 'activate' ? 'active' : 'inactive';
    try {
        $electionModel->updateElectionStatus($_GET['id'], $newStatus);
        $_SESSION['flash_message'] = 'Election status updated successfully';
        $_SESSION['flash_type'] = 'success';
    } catch (Exception $e) {
        $_SESSION['flash_message'] = 'Error updating election status: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'danger';
    }
    header('Location: manage-elections.php');
    exit;
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
