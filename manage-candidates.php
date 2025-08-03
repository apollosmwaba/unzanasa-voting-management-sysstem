<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Require admin authentication
Auth::requireAuth();

// Initialize variables
$candidateModel = new Candidate();
$electionModel = new Election();
$message = '';
$messageType = '';
$candidates = [];
$elections = [];

// Get all active elections for the dropdown
try {
    $elections = $electionModel->getAllElections(['status' => 'active']);
} catch (Exception $e) {
    $message = 'Error loading elections: ' . $e->getMessage();
    $messageType = 'danger';
    error_log($message);
}

// Handle form submission for adding/editing a candidate
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $electionId = (int)($_POST['election_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $photo = $_FILES['photo'] ?? null;
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Candidate name is required';
    }
    
    if (empty($position)) {
        $errors[] = 'Position is required';
    }
    
    if ($electionId <= 0) {
        $errors[] = 'Please select an election';
    }
    
    // Handle file upload if a new photo is provided
    $photoPath = null;
    if ($photo && $photo['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($photo['type'], $allowedTypes)) {
            $errors[] = 'Invalid file type. Only JPG, PNG, and GIF are allowed.';
        } elseif ($photo['size'] > $maxFileSize) {
            $errors[] = 'File is too large. Maximum size is 2MB.';
        } else {
            $uploadDir = __DIR__ . '/uploads/candidates/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExt = pathinfo($photo['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('candidate_') . '.' . $fileExt;
            $photoPath = 'uploads/candidates/' . $fileName;
            
            if (!move_uploaded_file($photo['tmp_name'], __DIR__ . '/' . $photoPath)) {
                $errors[] = 'Failed to upload photo';
                $photoPath = null;
            }
        }
    } elseif ($id && empty($photoPath)) {
        // Keep existing photo if editing and no new photo is uploaded
        try {
            $existingCandidate = $candidateModel->getCandidateById($id);
            $photoPath = $existingCandidate['photo_path'] ?? null;
        } catch (Exception $e) {
            error_log('Error getting existing candidate photo: ' . $e->getMessage());
        }
    }
    
    // If no validation errors, save the candidate
    if (empty($errors)) {
        $candidateData = [
            'name' => $name,
            'position' => $position,
            'election_id' => $electionId,
            'description' => $description,
            'photo_path' => $photoPath
        ];
        
        try {
            if ($id) {
                // Update existing candidate
                $candidateModel->updateCandidate($id, $candidateData);
                $message = 'Candidate updated successfully';
            } else {
                // Create new candidate
                $candidateModel->createCandidate($candidateData);
                $message = 'Candidate created successfully';
            }
            $messageType = 'success';
            
            // Redirect to prevent form resubmission
            header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
            exit();
        } catch (Exception $e) {
            $message = 'Error saving candidate: ' . $e->getMessage();
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
        $candidateModel->deleteCandidate($_GET['id']);
        $message = 'Candidate deleted successfully';
        $messageType = 'success';
        
        // Redirect to prevent refresh from resubmitting the delete
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit();
    } catch (Exception $e) {
        $message = 'Error deleting candidate: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Get all candidates with their election names
try {
    $candidates = $candidateModel->getAllCandidatesWithElections();
} catch (Exception $e) {
    $message = 'Error loading candidates: ' . $e->getMessage();
    $messageType = 'danger';
    error_log($message);
}

// Get candidate for editing if ID is provided
$editCandidate = null;
if (isset($_GET['edit'])) {
    try {
        $editCandidate = $candidateModel->getCandidateById($_GET['edit']);
    } catch (Exception $e) {
        $message = 'Error loading candidate for editing: ' . $e->getMessage();
        $messageType = 'danger';
        error_log($message);
    }
}

// Include the view
include __DIR__ . '/application/views/manage-candidates.php';
?>
