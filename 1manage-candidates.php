<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

// Initialize models
$candidateModel = new Candidate();
$electionModel = new Election();
$positionModel = new Position();

// Initialize variables
$message = '';
$messageType = 'success';
$candidates = [];
$positions = [];
$elections = [];
$editCandidate = null;

// Load common data
try {
    $elections = $electionModel->getActiveElections();
    $positions = $positionModel->getAllPositions(['status' => 1]);
    $candidates = $candidateModel->getAllCandidatesWithElections(true);
} catch (Exception $e) {
    $message = 'Error loading data: ' . $e->getMessage();
    $messageType = 'danger';
    error_log($message);
}

// Handle form submission
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
    $photoPath = null;
    
    // Handle file upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo = $_FILES['photo'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        
        if (in_array($photo['type'], $allowedTypes) && $photo['size'] <= $maxFileSize) {
            $uploadDir = __DIR__ . '/uploads/candidates/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExt = pathinfo($photo['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('candidate_') . '.' . $fileExt;
            $photoPath = 'uploads/candidates/' . $fileName;
            
            if (move_uploaded_file($photo['tmp_name'], __DIR__ . '/' . $photoPath)) {
                // File uploaded successfully
            }
        }
    }
    
    // Validate input
    $errors = [];
    if (empty($firstname)) $errors[] = 'First name is required';
    if (empty($lastname)) $errors[] = 'Last name is required';
    if (empty($name)) $errors[] = 'Display name is required';
    if ($positionId <= 0) $errors[] = 'Please select a position';
    if ($electionId <= 0) $errors[] = 'Please select an election';
    
    if (empty($errors)) {
        try {
            $candidateData = [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'name' => $name,
                'position_id' => $positionId,
                'election_id' => $electionId,
                'platform' => $platform,
                'bio' => $bio,
                'status' => $status
            ];
            
            if ($photoPath) {
                $candidateData['photo'] = $photoPath;
            }
            
            if ($id) {
                // Update existing candidate
                $candidateModel->updateCandidate($id, $candidateData);
                $message = 'Candidate updated successfully';
            } else {
                // Add new candidate
                $candidateModel->addCandidate($candidateData);
                $message = 'Candidate added successfully';
                
                // Reset form
                $firstname = $lastname = $name = $platform = $bio = '';
                $positionId = $electionId = 0;
                $status = 1;
            }
            
            // Reload candidates
            $candidates = $candidateModel->getAllCandidatesWithElections(true);
            
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
        $candidates = $candidateModel->getAllCandidatesWithElections(true);
    } catch (Exception $e) {
        $message = 'Error deleting candidate: ' . $e->getMessage();
        $messageType = 'danger';
        error_log($message);
    }
}

// Handle edit action
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $editCandidate = $candidateModel->getCandidateById($_GET['edit']);
        if (!$editCandidate) {
            throw new Exception('Candidate not found');
        }
    } catch (Exception $e) {
        $message = 'Error loading candidate: ' . $e->getMessage();
        $messageType = 'danger';
        error_log($message);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - UNZANASA Voting System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .candidate-photo {
            max-width: 150px;
            max-height: 150px;
            display: block;
            margin-bottom: 10px;
        }
        .form-label {
            font-weight: 500;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        .action-buttons {
            margin-top: 20px;
        }
        .candidate-card {
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        .candidate-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .table-responsive {
            margin-top: 20px;
        }
        .btn-add-new {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1><i class="fas fa-users me-2"></i> Manage Candidates</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Candidates</li>
                    </ol>
                </nav>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Add New Candidate Button -->
        <div class="row mb-4">
            <div class="col-12">
                <button class="btn btn-primary btn-add-new" data-bs-toggle="collapse" data-bs-target="#candidateForm">
                    <i class="fas fa-plus me-2"></i> Add New Candidate
                </button>
            </div>
        </div>

        <!-- Add/Edit Candidate Form -->
        <div class="row mb-4 collapse <?php echo isset($editCandidate) ? 'show' : ''; ?>" id="candidateForm">
            <div class="col-12">
                <div class="form-container">
                    <h3><i class="fas fa-user-plus me-2"></i> <?php echo isset($editCandidate) ? 'Edit' : 'Add New'; ?> Candidate</h3>
                    <hr>
                    
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <input type="hidden" name="id" value="<?php echo $editCandidate['id'] ?? ''; ?>">
                        
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <!-- First Name -->
                                <div class="mb-3">
                                    <label for="firstname" class="form-label required-field">First Name</label>
                                    <input type="text" class="form-control" id="firstname" name="firstname" 
                                           value="<?php echo htmlspecialchars($editCandidate['firstname'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">Please enter first name</div>
                                </div>

                                <!-- Last Name -->
                                <div class="mb-3">
                                    <label for="lastname" class="form-label required-field">Last Name</label>
                                    <input type="text" class="form-control" id="lastname" name="lastname" 
                                           value="<?php echo htmlspecialchars($editCandidate['lastname'] ?? ''); ?>" required>
                                    <div class="invalid-feedback">Please enter last name</div>
                                </div>

                                <!-- Display Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label required-field">Display Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($editCandidate['name'] ?? ''); ?>" required>
                                    <small class="text-muted">This name will be displayed to voters</small>
                                    <div class="invalid-feedback">Please enter display name</div>
                                </div>

                                <!-- Election -->
                                <div class="mb-3">
                                    <label for="election_id" class="form-label required-field">Election</label>
                                    <select class="form-select" id="election_id" name="election_id" required>
                                        <option value="">-- Select Election --</option>
                                        <?php foreach ($elections as $election): ?>
                                            <option value="<?php echo $election['id']; ?>"
                                                <?php echo (isset($editCandidate['election_id']) && $editCandidate['election_id'] == $election['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($election['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select an election</div>
                                </div>

                                <!-- Position -->
                                <div class="mb-3">
                                    <label for="position_id" class="form-label required-field">Position</label>
                                    <select class="form-select" id="position_id" name="position_id" required>
                                        <option value="">-- Select Position --</option>
                                        <?php foreach ($positions as $position): ?>
                                            <option value="<?php echo $position['id']; ?>"
                                                <?php echo (isset($editCandidate['position_id']) && $editCandidate['position_id'] == $position['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($position['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a position</div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <!-- Photo Upload -->
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Candidate Photo</label>
                                    <div class="photo-upload-container">
                                        <?php if (isset($editCandidate['photo']) && !empty($editCandidate['photo'])): ?>
                                            <img src="<?php echo htmlspecialchars($editCandidate['photo']); ?>" 
                                                 class="img-thumbnail mb-2" style="max-width: 150px;" 
                                                 id="photo-preview">
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" 
                                                       id="remove_photo" name="remove_photo">
                                                <label class="form-check-label" for="remove_photo">
                                                    Remove current photo
                                                </label>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" id="photo" name="photo" 
                                               accept="image/jpeg,image/png,image/gif,image/webp">
                                        <small class="text-muted">Max file size: 2MB. Allowed formats: JPG, PNG, GIF, WebP</small>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="status" name="status" 
                                           <?php echo (isset($editCandidate['status']) && $editCandidate['status']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="status">Active Candidate</label>
                                    <small class="d-block text-muted">Inactive candidates won't be shown to voters</small>
                                </div>
                            </div>

                            <!-- Full Width Fields -->
                            <div class="col-12">
                                <!-- Platform -->
                                <div class="mb-3">
                                    <label for="platform" class="form-label required-field">Platform/Manifesto</label>
                                    <textarea class="form-control" id="platform" name="platform" rows="4" required><?php 
                                        echo htmlspecialchars($editCandidate['platform'] ?? ''); 
                                    ?></textarea>
                                    <div class="invalid-feedback">Please enter the candidate's platform</div>
                                </div>

                                <!-- Bio -->
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Biography</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3"><?php 
                                        echo htmlspecialchars($editCandidate['bio'] ?? ''); 
                                    ?></textarea>
                                    <small class="text-muted">Optional additional information about the candidate</small>
                                </div>
                            </div>

                            <!-- Form Buttons -->
                            <div class="col-12 mt-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <?php if (isset($editCandidate)): ?>
                                        <a href="manage-candidates.php" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </a>
                                    <?php endif; ?>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> 
                                        <?php echo isset($editCandidate) ? 'Update' : 'Save'; ?> Candidate
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Candidates List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i> Candidate List
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($candidates)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i> No candidates found.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Photo</th>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Election</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($candidates as $candidate): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($candidate['photo'])): ?>
                                                        <img src="<?php echo htmlspecialchars($candidate['photo']); ?>" 
                                                             class="rounded-circle" width="40" height="40" 
                                                             alt="<?php echo htmlspecialchars($candidate['name']); ?>">
                                                    <?php else: ?>
                                                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                             style="width: 40px; height: 40px;">
                                                            <i class="fas fa-user"></i>
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
                                                <td class="text-end">
                                                    <div class="btn-group">
                                                        <a href="?edit=<?php echo $candidate['id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="confirmDelete(<?php echo $candidate['id']; ?>)">
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
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Form validation
    (function () {
        'use strict'
        
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')
        
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
            
        // Confirm before deleting
        window.confirmDelete = function(id) {
            if (confirm('Are you sure you want to delete this candidate? This action cannot be undone.')) {
                window.location.href = 'manage-candidates.php?delete=' + id;
            }
        }
        
        // Preview image before upload
        const photoInput = document.getElementById('photo');
        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        let preview = document.getElementById('photo-preview');
                        if (!preview) {
                            const container = document.querySelector('.photo-upload-container');
                            if (container) {
                                preview = document.createElement('img');
                                preview.id = 'photo-preview';
                                preview.className = 'img-thumbnail mb-2';
                                preview.style.maxWidth = '150px';
                                container.insertBefore(preview, container.firstChild);
                            }
                        }
                        if (preview) {
                            preview.src = e.target.result;
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    })()
    </script>
</body>
</html>
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
    // Debug: Check database directly for active elections
    $db = new Database();
    $db->query("SELECT * FROM elections WHERE status = 'active' AND start_date <= NOW() AND end_date >= NOW()");
    $activeElections = $db->resultSet();
    error_log('Direct DB Query - Active Elections: ' . print_r($activeElections, true));
    
    // Get only active elections using the model
    $elections = $electionModel->getAllElections(['status' => 'active']);
    
    // Debug: Log the elections being loaded
    $debug_info = 'Active elections loaded: ' . print_r($elections, true);
    error_log($debug_info);
    
    // Get active positions
    $positions = $positionModel->getAllPositions(['status' => 1]);
    
    // Debug: Show elections at the top of the page if in development
    if (isset($_GET['debug'])) {
        echo '<div class="container mt-4"><div class="alert alert-info">';
        echo '<h4>Database Check - Active Elections:</h4><pre>';
        echo htmlspecialchars(print_r($activeElections, true));
        echo '</pre><h4>Model Results - Active Elections:</h4><pre>';
        echo htmlspecialchars(print_r($elections, true));
        echo '</pre><h4>Active Positions:</h4><pre>';
        echo htmlspecialchars(print_r($positions, true));
        echo '</pre></div></div>';
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
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $photo['tmp_name']);
        
        if (!array_key_exists($fileType, $allowedTypes)) {
            $errors[] = 'Invalid file type. Only JPG, PNG, and GIF are allowed.';
        } elseif ($photo['size'] > $maxFileSize) {
            $errors[] = 'File is too large. Maximum size is 2MB.';
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
            'status' => $status,
            'photo' => $photoPath
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - UNZANASA Voting System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .candidate-photo {
            max-width: 150px;
            max-height: 150px;
            display: block;
            margin-bottom: 10px;
        }
        .form-label {
            font-weight: 500;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-4"><?php echo isset($editCandidate) ? 'Edit' : 'Add New'; ?> Candidate</h2>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
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
                                    <div class="photo-upload-container">
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
                                            <i class="fas fa-info-circle me-1"></i> Maximum file size: 2MB. Allowed formats: JPG, PNG, GIF, WebP
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Status -->
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="status" name="status" 
                                           <?php echo (isset($editCandidate['status']) && $editCandidate['status']) ? 'checked' : ''; ?>>
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
                                        <option value="">-- Select Active Election --</option>
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
                                            <option value="" disabled>No active elections found</option>
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
                                    <input type="text" class="form-control" value="<?php echo date('F j, Y, g:i a', strtotime($editCandidate['created_at'])); ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="far fa-calendar-check me-1"></i> Last Updated
                                    </label>
                                    <input type="text" class="form-control" value="<?php echo date('F j, Y, g:i a', strtotime($editCandidate['updated_at'])); ?>" readonly>
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
            </div>
        </div>
        
        <!-- Candidates List -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Candidate List</h2>
                    <a href="manage-candidates.php" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-1"></i> Add New Candidate
                    </a>
                </div>
                
                <?php if (empty($candidates)): ?>
                    <div class="alert alert-info">No candidates found. Add your first candidate using the form above.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
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
                                    <tr>
                                        <td>
                                            <?php if (!empty($candidate->photo)): ?>
                                                <img src="<?php echo htmlspecialchars($candidate->photo); ?>" 
                                                     alt="<?php echo htmlspecialchars($candidate->name); ?>" 
                                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px; border-radius: 4px;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($candidate->name); ?></td>
                                        <td><?php echo htmlspecialchars($candidate->position_name ?? 'N/A'); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($candidate->election_title ?? 'N/A'); ?>
                                            <small class="d-block text-muted">
                                                <?php 
                                                if (isset($candidate->start_date) && isset($candidate->end_date)) {
                                                    echo date('M j, Y', strtotime($candidate->start_date)) . ' to ' . date('M j, Y', strtotime($candidate->end_date));
                                                } else {
                                                    echo 'No dates set';
                                                }
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $candidate->status ? 'success' : 'secondary'; ?> mb-1">
                                                <?php echo $candidate->status ? 'Active' : 'Inactive'; ?>
                                            </span>
                                            <br>
                                            <span class="badge bg-<?php echo ($candidate->election_status === 'active') ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($candidate->election_status ?? 'no status'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="?edit=<?php echo $candidate->id; ?>" class="btn btn-outline-primary" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="if(confirm('Are you sure you want to delete this candidate?')) { window.location.href='?delete=<?php echo $candidate->id; ?>' }" 
                                                        title="Delete">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Form validation
    (function () {
        'use strict'
        
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')
        
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
            
        // Confirm before deleting
        window.confirmDelete = function(id) {
            if (confirm('Are you sure you want to delete this candidate? This action cannot be undone.')) {
                window.location.href = 'manage-candidates.php?delete=' + id;
            }
        }
        
        // Preview image before upload
        const photoInput = document.getElementById('photo');
        if (photoInput) {
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        let preview = document.getElementById('photo-preview');
                        if (!preview) {
                            preview = document.createElement('img');
                            preview.id = 'photo-preview';
                            preview.className = 'candidate-photo img-thumbnail';
                            const container = document.querySelector('.photo-upload-container');
                            if (container) container.prepend(preview);
                        }
                        preview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    })()
    </script>
    
    <!-- Form validation -->
    <script>
    // Form validation
    (function () {
        'use strict'
        
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.querySelectorAll('.needs-validation')
        
        // Loop over them and prevent submission
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
            
        // Confirm before deleting
        window.confirmDelete = function(id) {
            if (confirm('Are you sure you want to delete this candidate? This action cannot be undone.')) {
                window.location.href = 'manage-candidates.php?delete=' + id;
            }
        }
        
        // Preview image before upload
        document.getElementById('photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.getElementById('photo-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.id = 'photo-preview';
                        preview.className = 'candidate-photo img-thumbnail';
                        document.querySelector('.photo-upload-container').prepend(preview);
                    }
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    })()
    </script>
    
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
        
        // Preview image before upload
        document.getElementById('photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'candidate-photo img-thumbnail mb-2';
                    preview.style.display = 'block';
                    
                    const container = document.querySelector('.form-group:has(#photo)');
                    const existingPreview = container.querySelector('img');
                    
                    if (existingPreview) {
                        container.replaceChild(preview, existingPreview);
                    } else {
                        container.insertBefore(preview, document.getElementById('photo'));
                    }
                    
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
