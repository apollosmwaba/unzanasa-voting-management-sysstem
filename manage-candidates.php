<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Require admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$candidateModel = new Candidate();
$electionModel = new Election();
$positionModel = new Position();
$message = '';
$messageType = 'success';
$candidates = [];
$positions = [];
$elections = [];
$editCandidate = null;
$photoPath = null;
$errors = [];

// Load common data
try {
    // Get all elections (not just active ones) for candidate management
    $elections = $electionModel->getAllElections();
    
    // Get all positions (positions table doesn't have status field)
    $positions = $positionModel->getAllPositions();
    
    // Store debug info for later display in the HTML body
    $debugInfo = null;
    if (isset($_GET['debug'])) {
        $debugInfo = [
            'elections' => $elections,
            'positions' => $positions
        ];
    }
} catch (Exception $e) {
    $message = 'Error loading required data: ' . $e->getMessage();
    $messageType = 'danger';
    error_log($message);
}

// Handle form submission for adding/editing a candidate
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $positionId = (int)($_POST['position_id'] ?? 0);
    $electionId = (int)($_POST['election_id'] ?? 0);
    $platform = trim($_POST['platform'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $status = isset($_POST['status']) ? 1 : 0;
    $removePhoto = isset($_POST['remove_photo']);
    
    // Handle file upload if a new file was uploaded
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo = $_FILES['photo'];
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        $maxFileSize = 10 * 1024 * 1024; // 10MB
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $photo['tmp_name']);
        
        if (!array_key_exists($fileType, $allowedTypes)) {
            $errors[] = 'Invalid file type. Only JPG, PNG, and GIF are allowed.';
        } elseif ($photo['size'] > $maxFileSize) {
            $errors[] = 'File is too large. Maximum size is 10MB.';
        } else {
            // Create uploads directory if it doesn't exist
            $uploadDir = __DIR__ . '/uploads/candidates/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Generate unique filename
            $fileExt = $allowedTypes[$fileType];
            $fileName = uniqid('candidate_') . '.' . $fileExt;
            $photoPath = 'uploads/candidates/' . $fileName;
            
            // Move uploaded file
            if (!move_uploaded_file($photo['tmp_name'], __DIR__ . '/' . $photoPath)) {
                $errors[] = 'Failed to upload photo';
            }
        }
    } elseif ($id && !$removePhoto) {
        // Keep existing photo if editing and not removing
        try {
            $existing = $candidateModel->getCandidateById($id);
            if ($existing && !empty($existing['photo'])) {
                $photoPath = $existing['photo'];
            }
        } catch (Exception $e) {
            error_log('Error getting existing candidate: ' . $e->getMessage());
            $errors[] = 'Error loading candidate data';
        }
    }
    
    // If removing photo, clear the photo path
    if ($removePhoto) {
        $photoPath = '';
    }
    
    // Validate required fields
    if (empty($firstname)) $errors[] = 'First name is required';
    if (empty($lastname)) $errors[] = 'Last name is required';
    if (empty($name)) $errors[] = 'Display name is required';
    if ($positionId <= 0) $errors[] = 'Please select a position';
    if ($electionId <= 0) $errors[] = 'Please select an election';
    if (empty($platform)) $errors[] = 'Platform/Manifesto is required';
    
    // If no validation errors, save the candidate
    if (empty($errors)) {
        $candidateData = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'name' => $name,
            'position_id' => $positionId,
            'election_id' => $electionId,
            'platform' => $platform,
            'bio' => $bio,
            'photo' => $photoPath,
            'status' => $status
        ];
        
        try {
            if ($id) {
                // Update existing candidate
                $candidateModel->updateCandidate($id, $candidateData);
                $message = 'Candidate updated successfully';
                
                // Redirect to prevent form resubmission
                $_SESSION['flash_message'] = $message;
                $_SESSION['flash_type'] = 'success';
                header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?edit=' . $id);
                exit();
            } else {
                // Create new candidate
                $candidateId = $candidateModel->addCandidate($candidateData);
                $message = 'Candidate added successfully';
                
                // Redirect to edit page for the new candidate
                $_SESSION['flash_message'] = $message;
                $_SESSION['flash_type'] = 'success';
                header('Location: manage-candidates.php?edit=' . $candidateId);
                exit();
            }
        } catch (Exception $e) {
            $message = 'Error saving candidate: ' . $e->getMessage();
            $messageType = 'danger';
            error_log($message);
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'danger';
    }
}

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $candidateModel->deleteCandidate($_GET['delete']);
        $message = 'Candidate deleted successfully';
        $messageType = 'success';
        
        // Set flash message and redirect
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $messageType;
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit();
    } catch (Exception $e) {
        $message = 'Error deleting candidate: ' . $e->getMessage();
        $messageType = 'danger';
        error_log($message);
    }
}

// Check for flash messages
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $messageType = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}

// Get candidate for editing
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $editCandidate = $candidateModel->getCandidateById($_GET['edit']);
        if (!$editCandidate) {
            $message = 'Candidate not found';
            $messageType = 'warning';
        }
    } catch (Exception $e) {
        $message = 'Error loading candidate: ' . $e->getMessage();
        $messageType = 'danger';
        error_log($message);
    }
}

// Get all candidates with their election and position info
try {
    $candidates = $candidateModel->getAllCandidatesWithElections();
} catch (Exception $e) {
    $message = 'Error loading candidates: ' . $e->getMessage();
    $messageType = 'danger';
    error_log($message);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .candidate-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .form-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .candidate-card {
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        .candidate-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Navigation Header -->
        <div class="row mb-3">
            <div class="col-12">
                <nav class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                    <div class="d-flex align-items-center">
                        <a href="admin-dashboard.php" class="btn btn-primary me-2">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                        <span class="text-muted">|</span>
                        <a href="manage-elections.php" class="btn btn-outline-primary ms-2 me-2">
                            <i class="fas fa-vote-yea me-1"></i> Manage Elections
                        </a>
                        <a href="view-results.php" class="btn btn-outline-info">
                            <i class="fas fa-chart-bar me-1"></i> View Results
                        </a>
                    </div>
                    <div class="text-muted">
                        <i class="fas fa-user-tie me-1"></i> Admin Panel
                    </div>
                </nav>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-4"><?php echo isset($editCandidate) ? 'Edit' : 'Add New'; ?> Candidate</h2>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($debugInfo): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <h4>Debug Information:</h4>
                        <h5>Active Elections:</h5>
                        <pre><?php echo htmlspecialchars(print_r($debugInfo['elections'], true)); ?></pre>
                        <h5>Active Positions:</h5>
                        <pre><?php echo htmlspecialchars(print_r($debugInfo['positions'], true)); ?></pre>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="form-container">
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="id" value="<?php echo $editCandidate['id'] ?? ''; ?>">
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">Basic Information</h5>
                            </div>
                            
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <!-- First Name -->
                                <div class="mb-3">
                                    <label for="firstname" class="form-label">
                                        <i class="fas fa-user me-1"></i> First Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="firstname" name="firstname" 
                                           placeholder="Enter first name"
                                           value="<?php echo htmlspecialchars($editCandidate['firstname'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide a first name.
                                    </div>
                                </div>
                                
                                <!-- Last Name -->
                                <div class="mb-3">
                                    <label for="lastname" class="form-label">
                                        <i class="fas fa-user me-1"></i> Last Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="lastname" name="lastname" 
                                           placeholder="Enter last name"
                                           value="<?php echo htmlspecialchars($editCandidate['lastname'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide a last name.
                                    </div>
                                </div>
                                
                                <!-- Display Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-tag me-1"></i> Display Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                           placeholder="Name to display on ballots"
                                           value="<?php echo htmlspecialchars($editCandidate['name'] ?? ''); ?>" required>
                                    <div class="form-text">This is how the candidate's name will appear to voters</div>
                                    <div class="invalid-feedback">
                                        Please provide a display name.
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column -->
                            <div class="col-md-6">
                                <!-- Photo Upload -->
                                <div class="mb-3">
                                    <label for="photo" class="form-label">
                                        <i class="fas fa-camera me-1"></i> Candidate Photo
                                    </label>
                                    <?php if (isset($editCandidate['photo']) && !empty($editCandidate['photo'])): ?>
                                        <div class="mb-2 text-center">
                                            <img src="<?php echo htmlspecialchars($editCandidate['photo']); ?>" 
                                                 alt="Current Photo" class="candidate-photo img-thumbnail">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="remove_photo" name="remove_photo">
                                                <label class="form-check-label" for="remove_photo">
                                                    Remove current photo
                                                </label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control form-control-lg" id="photo" name="photo" 
                                           accept="image/jpeg,image/png,image/gif,image/webp">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i> Maximum file size: 10MB. Allowed formats: JPG, PNG, GIF, WebP
                                    </div>
                                </div>
                                
                                <!-- Status -->
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="status" name="status" 
                                           <?php echo (!isset($editCandidate) || (isset($editCandidate['status']) && $editCandidate['status'])) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status">
                                        <i class="fas fa-toggle-on me-1"></i> Active Candidate
                                    </label>
                                    <div class="form-text">Inactive candidates won't appear in voting forms</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Election & Position -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">Election Details</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Election -->
                                <div class="mb-3">
                                    <label for="election_id" class="form-label">
                                        <i class="fas fa-vote-yea me-1"></i> Election <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg" id="election_id" name="election_id" required>
                                        <option value="">-- Select Election --</option>
                                        <?php if (!empty($elections)): ?>
                                            <?php foreach ($elections as $election): ?>
                                                <option value="<?php echo $election['id']; ?>"
                                                    <?php echo (isset($editCandidate['election_id']) && $editCandidate['election_id'] == $election['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($election['title']); ?>
                                                    <?php echo (isset($election['start_date']) && isset($election['end_date'])) ? 
                                                        ' (' . date('M j, Y', strtotime($election['start_date'])) . ' - ' . date('M j, Y', strtotime($election['end_date'])) . ')' : ''; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="" disabled>No elections found</option>
                                        <?php endif; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select an election.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Position -->
                                <div class="mb-3">
                                    <label for="position_id" class="form-label">
                                        <i class="fas fa-bullseye me-1"></i> Position <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg" id="position_id" name="position_id" required>
                                        <option value="">Select Position</option>
                                        <?php foreach ($positions as $position): ?>
                                            <option value="<?php echo $position['id']; ?>"
                                                <?php echo (isset($editCandidate['position_id']) && $editCandidate['position_id'] == $position['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($position['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a position.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Platform & Bio -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">Candidate Information</h5>
                            </div>
                            
                            <div class="col-12">
                                <!-- Platform -->
                                <div class="mb-3">
                                    <label for="platform" class="form-label">
                                        <i class="fas fa-bullhorn me-1"></i> Platform/Manifesto <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="platform" name="platform" rows="4" 
                                              placeholder="Enter the candidate's campaign platform or key promises" required><?php 
                                        echo htmlspecialchars($editCandidate['platform'] ?? ''); 
                                    ?></textarea>
                                    <div class="form-text">This will be visible to voters</div>
                                    <div class="invalid-feedback">
                                        Please provide the candidate's platform.
                                    </div>
                                </div>
                                
                                <!-- Bio -->
                                <div class="mb-3">
                                    <label for="bio" class="form-label">
                                        <i class="fas fa-info-circle me-1"></i> Biography
                                    </label>
                                    <textarea class="form-control" id="bio" name="bio" rows="4"
                                              placeholder="Enter candidate's biography (optional)"><?php 
                                        echo htmlspecialchars($editCandidate['bio'] ?? ''); 
                                    ?></textarea>
                                    <div class="form-text">Additional information about the candidate (optional)</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- System Information (readonly) -->
                        <?php if (isset($editCandidate)): ?>
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">System Information</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-hashtag me-1"></i> Candidate ID
                                    </label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($editCandidate['id']); ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="far fa-calendar-plus me-1"></i> Date Created
                                    </label>
                                    <input type="text" class="form-control" value="<?php echo isset($editCandidate['created_at']) ? date('F j, Y, g:i a', strtotime($editCandidate['created_at'])) : 'N/A'; ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="far fa-calendar-check me-1"></i> Last Updated
                                    </label>
                                    <input type="text" class="form-control" value="<?php echo isset($editCandidate['updated_at']) ? date('F j, Y, g:i a', strtotime($editCandidate['updated_at'])) : 'N/A'; ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Form Buttons -->
                        <div class="row mt-4 pt-3 border-top">
                            <div class="col-12">
                                <div class="d-flex justify-content-between flex-wrap gap-3">
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php if (isset($editCandidate)): ?>
                                            <a href="manage-candidates.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-arrow-left me-1"></i> Back to List
                                            </a>
                                            <a href="manage-candidates.php" class="btn btn-outline-danger">
                                                <i class="fas fa-times me-1"></i> Cancel
                                            </a>
                                        <?php else: ?>
                                            <a href="manage-candidates.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-list me-1"></i> View All Candidates
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php if (isset($editCandidate)): ?>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="confirmDelete(<?php echo $editCandidate['id']; ?>)">
                                                <i class="fas fa-trash-alt me-1"></i> Delete
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="btn btn-outline-warning me-2" onclick="clearForm()">
                                            <i class="fas fa-eraser me-1"></i> Clear Form
                                        </button>
                                        
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save me-1"></i>
                                            <?php echo isset($editCandidate) ? 'Update' : 'Save'; ?> Candidate
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
{{ ... }}
            </div>
        </div>
        
        <!-- Candidates List -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Candidate List</h2>
                    <div class="d-flex gap-2">
                        <a href="manage-candidates.php" class="btn btn-outline-primary">
                            <i class="fas fa-plus me-1"></i> Add New Candidate
                        </a>
                        <button type="button" class="btn btn-outline-info" onclick="toggleBulkActions()">
                            <i class="fas fa-tasks me-1"></i> Bulk Actions
                        </button>
                    </div>
                </div>
                
                <!-- Search and Filter Section -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="searchCandidates" placeholder="Search candidates..." onkeyup="filterCandidates()">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterPosition" onchange="filterCandidates()">
                            <option value="">All Positions</option>
                            <?php foreach ($positions as $position): ?>
                                <option value="<?php echo htmlspecialchars($position['name']); ?>">
                                    <?php echo htmlspecialchars($position['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterStatus" onchange="filterCandidates()">
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                            <i class="fas fa-times me-1"></i> Clear
                        </button>
                    </div>
                </div>
                
                <!-- Bulk Actions Panel (Hidden by default) -->
                <div id="bulkActionsPanel" class="alert alert-light border d-none mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Bulk Actions:</strong>
                            <span id="selectedCount">0</span> candidate(s) selected
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-success" onclick="bulkActivate()">
                                <i class="fas fa-check me-1"></i> Activate
                            </button>
                            <button type="button" class="btn btn-sm btn-warning" onclick="bulkDeactivate()">
                                <i class="fas fa-pause me-1"></i> Deactivate
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="bulkDelete()">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                                Clear Selection
                            </button>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($candidates)): ?>
                    <div class="alert alert-info">No candidates found. Add your first candidate using the form above.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="candidatesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                    <th>Photo</th>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Election</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidates as $candidate): ?>
                                    <tr data-candidate-id="<?php echo $candidate['id']; ?>" 
                                        data-position="<?php echo htmlspecialchars($candidate['position_name'] ?? ''); ?>"
                                        data-status="<?php echo $candidate['status'] ? 'Active' : 'Inactive'; ?>">
                                        <td>
                                            <input type="checkbox" class="candidate-checkbox" value="<?php echo $candidate['id']; ?>" onchange="updateBulkActions()">
                                        </td>
                                        <td>
                                            <?php if (!empty($candidate['photo'])): ?>
                                                <img src="<?php echo htmlspecialchars($candidate['photo']); ?>" 
                                                     alt="<?php echo htmlspecialchars($candidate['name']); ?>" 
                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px; border-radius: 4px;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($candidate['name']); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['position_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($candidate['election_title'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $candidate['status'] ? 'success' : 'secondary'; ?>">
                                                <?php echo $candidate['status'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="viewCandidate(<?php echo $candidate['id']; ?>)" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="?edit=<?php echo $candidate['id']; ?>" class="btn btn-sm btn-outline-primary" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmDelete(<?php echo $candidate['id']; ?>)" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Form validation -->
    <script>
        // Form validation
        (function () {
            'use strict';
            
            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation');
            
            // Loop over them and prevent submission
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
        
        // Confirm before deleting
        function confirmDelete(candidateId) {
            if (confirm('Are you sure you want to delete this candidate? This action cannot be undone.')) {
                window.location.href = '?delete=' + candidateId;
            }
        }
        
        // Clear form function
        function clearForm() {
            if (confirm('Are you sure you want to clear all form data?')) {
                document.querySelector('form').reset();
                // Clear photo preview
                const previewContainer = document.querySelector('.photo-preview-container');
                if (previewContainer) {
                    previewContainer.remove();
                }
                // Remove validation classes
                document.querySelector('form').classList.remove('was-validated');
            }
        }
        
        // Filter candidates function
        function filterCandidates() {
            const searchTerm = document.getElementById('searchCandidates').value.toLowerCase();
            const positionFilter = document.getElementById('filterPosition').value;
            const statusFilter = document.getElementById('filterStatus').value;
            const table = document.getElementById('candidatesTable');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const name = row.cells[2].textContent.toLowerCase();
                const position = row.dataset.position;
                const status = row.dataset.status;
                
                const matchesSearch = name.includes(searchTerm);
                const matchesPosition = !positionFilter || position === positionFilter;
                const matchesStatus = !statusFilter || status === statusFilter;
                
                if (matchesSearch && matchesPosition && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Clear filters function
        function clearFilters() {
            document.getElementById('searchCandidates').value = '';
            document.getElementById('filterPosition').value = '';
            document.getElementById('filterStatus').value = '';
            filterCandidates();
        }
        
        // Toggle bulk actions panel
        function toggleBulkActions() {
            const panel = document.getElementById('bulkActionsPanel');
            panel.classList.toggle('d-none');
        }
        
        // Toggle select all checkboxes
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.candidate-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            updateBulkActions();
        }
        
        // Update bulk actions based on selection
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.candidate-checkbox:checked');
            const count = checkboxes.length;
            document.getElementById('selectedCount').textContent = count;
            
            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.candidate-checkbox');
            const selectAll = document.getElementById('selectAll');
            selectAll.checked = count === allCheckboxes.length;
            selectAll.indeterminate = count > 0 && count < allCheckboxes.length;
        }
        
        // Clear selection
        function clearSelection() {
            document.querySelectorAll('.candidate-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('selectAll').checked = false;
            updateBulkActions();
        }
        
        // Bulk operations (placeholder functions - you'll need to implement server-side handling)
        function bulkActivate() {
            const selected = getSelectedCandidates();
            if (selected.length === 0) {
                alert('Please select candidates to activate.');
                return;
            }
            if (confirm(`Activate ${selected.length} candidate(s)?`)) {
                // Implement server-side bulk activation
                console.log('Bulk activate:', selected);
                alert('Bulk activation feature needs server-side implementation.');
            }
        }
        
        function bulkDeactivate() {
            const selected = getSelectedCandidates();
            if (selected.length === 0) {
                alert('Please select candidates to deactivate.');
                return;
            }
            if (confirm(`Deactivate ${selected.length} candidate(s)?`)) {
                // Implement server-side bulk deactivation
                console.log('Bulk deactivate:', selected);
                alert('Bulk deactivation feature needs server-side implementation.');
            }
        }
        
        function bulkDelete() {
            const selected = getSelectedCandidates();
            if (selected.length === 0) {
                alert('Please select candidates to delete.');
                return;
            }
            if (confirm(`Delete ${selected.length} candidate(s)? This action cannot be undone.`)) {
                // Implement server-side bulk deletion
                console.log('Bulk delete:', selected);
                alert('Bulk deletion feature needs server-side implementation.');
            }
        }
        
        // Get selected candidate IDs
        function getSelectedCandidates() {
            const checkboxes = document.querySelectorAll('.candidate-checkbox:checked');
            return Array.from(checkboxes).map(cb => cb.value);
        }
        
        // View candidate details (modal or new page)
        function viewCandidate(candidateId) {
            // For now, redirect to edit page in view mode
            // You could implement a modal view instead
            window.open(`?edit=${candidateId}&view=1`, '_blank');
        }
        
        // Preview image before upload
        document.getElementById('photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Find or create preview container
                    let previewContainer = document.querySelector('.photo-preview-container');
                    if (!previewContainer) {
                        previewContainer = document.createElement('div');
                        previewContainer.className = 'photo-preview-container mb-3 text-center';
                        document.getElementById('photo').parentNode.appendChild(previewContainer);
                    }
                    
                    // Create or update preview image
                    let preview = previewContainer.querySelector('img');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'candidate-photo img-thumbnail';
                        previewContainer.appendChild(preview);
                    }
                    
                    preview.src = e.target.result;
                    
                    // Uncheck remove photo if it was checked
                    const removeCheckbox = document.getElementById('remove_photo');
                    if (removeCheckbox) {
                        removeCheckbox.checked = false;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>

