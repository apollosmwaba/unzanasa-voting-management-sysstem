<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - Election System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --light-bg: #f8f9fc;
        }
        
        body {
            background-color: var(--light-bg);
            color: #5a5c69;
        }
        
        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .card-header {
            background-color: var(--primary-color);
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 1rem 1.25rem;
        }
        
        .candidate-photo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #e3e6f0;
        }
        
        .photo-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 0.35rem;
            border: 1px solid #d1d3e2;
            padding: 0.25rem;
            background-color: #fff;
        }
        
        .required:after {
            content: " *";
            color: var(--danger-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.04em;
            color: #4e73df;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #bac8f3;
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }
        
        .action-buttons .btn {
            min-width: 80px;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
        }
        
        .search-box {
            max-width: 300px;
        }
        
        @media (max-width: 768px) {
            .search-box {
                max-width: 100%;
                margin-top: 1rem;
            }
            
            .table-responsive {
                border: none;
            }
        }
        
        /* Ballot Card Styles */
        .ballot-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .ballot-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .ballot-photo-container {
            position: relative;
            height: 200px;
            overflow: hidden;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ballot-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .ballot-photo:hover {
            transform: scale(1.05);
        }
        .ballot-details {
            padding: 20px;
        }
        .ballot-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        .ballot-position {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        .ballot-platform {
            color: #34495e;
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        /* Form Styles */
        .candidate-form {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
        }
        .form-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
        
        /* Photo Upload Styles */
        .photo-upload-container {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
            margin-bottom: 20px;
        }
        .photo-upload-container:hover {
            border-color: #80bdff;
            background: #f1f8ff;
        }
        .photo-upload-icon {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 10px;
        }
        .photo-preview-container {
            position: relative;
            max-width: 100%;
            margin: 0 auto 20px;
            text-align: center;
        }
        .photo-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: none;
        }
        .remove-photo-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Candidates</h1>
            <a href="admin-dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Add/Edit Candidate Form -->
        <div class="candidate-form">
            <h3 class="mb-4"><?php echo isset($editCandidate) ? 'Edit Candidate' : 'Add New Candidate'; ?></h3>
            <form method="POST" action="" enctype="multipart/form-data" class="row g-4">
                <?php if (isset($editCandidate)): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($editCandidate['id']); ?>">
                <?php endif; ?>
                
                <!-- Candidate Photo -->
                <div class="col-12">
                    <div class="form-section">
                        <h4 class="section-title">Candidate Photo</h4>
                        <div class="photo-upload-container" id="photoUploadContainer">
                            <div id="photoUploadContent">
                                <div class="photo-upload-icon">
                                    <i class="bi bi-camera"></i>
                                </div>
                                <h5>Click to upload candidate's photo</h5>
                                <p class="text-muted small">Recommended size: 400x400px (JPG, PNG, max 2MB)</p>
                            </div>
                            <input type="file" id="photo" name="photo" accept="image/*" class="d-none" 
                                   onchange="previewPhoto(this)" <?php echo !isset($editCandidate) ? 'required' : ''; ?>>
                        </div>
                        
                        <div class="photo-preview-container" id="photoPreviewContainer" style="display: none;">
                            <img id="photoPreview" class="photo-preview" alt="Preview">
                            <button type="button" class="btn btn-danger btn-sm remove-photo-btn" id="removePhotoBtn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        
                        <?php if (isset($editCandidate) && !empty($editCandidate['photo'])): ?>
                            <div class="current-photo mt-3">
                                <p class="small text-muted mb-2">Current Photo:</p>
                                <img src="<?php echo htmlspecialchars($editCandidate['photo']); ?>" 
                                     class="img-thumbnail" style="max-height: 150px;">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo">
                                    <label class="form-check-label" for="remove_photo">
                                        Remove current photo
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Personal Information -->
                <div class="col-12">
                    <div class="form-section">
                        <h4 class="section-title">Personal Information</h4>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="firstname" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" required 
                                       value="<?php echo htmlspecialchars($editCandidate['firstname'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="lastname" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" required
                                       value="<?php echo htmlspecialchars($editCandidate['lastname'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="name" class="form-label">Display Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       value="<?php echo htmlspecialchars($editCandidate['name'] ?? ''); ?>">
                                <small class="text-muted">Name to display on ballot</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Election & Position -->
                <div class="col-12">
                    <div class="form-section">
                        <h4 class="section-title">Election & Position</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="election_id" class="form-label">Election *</label>
                                <select class="form-select" id="election_id" name="election_id" required>
                                    <option value="">Select an election</option>
                                    <?php 
                                    if (isset($elections) && is_array($elections)): 
                                        foreach ($elections as $election): 
                                    ?>
                                        <option value="<?php echo $election['id']; ?>"
                                            <?php echo (isset($editCandidate['election_id']) && $editCandidate['election_id'] == $election['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($election['name']); ?>
                                        </option>
                                    <?php 
                                        endforeach;
                                    endif; 
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="position_id" class="form-label">Position *</label>
                                <select class="form-select" id="position_id" name="position_id" required>
                                    <option value="">Select Position</option>
                                    <?php 
                                    // Assuming $positions is available with id and name fields
                                    if (isset($positions) && is_array($positions)): 
                                        foreach ($positions as $position): 
                                    ?>
                                        <option value="<?php echo $position['id']; ?>"
                                            <?php echo (isset($editCandidate['position_id']) && $editCandidate['position_id'] == $position['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($position['name']); ?>
                                        </option>
                                    <?php 
                                        endforeach;
                                    endif; 
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Platform & Bio -->
                <div class="col-12">
                    <div class="form-section">
                        <h4 class="section-title">Candidate Information</h4>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="platform" class="form-label">Platform/Manifesto *</label>
                                <textarea class="form-control" id="platform" name="platform" rows="4" required><?php 
                                    echo htmlspecialchars($editCandidate['platform'] ?? ''); 
                                ?></textarea>
                                <small class="text-muted">Outline the candidate's key promises and platform points</small>
                            </div>
                            
                            <div class="col-12">
                                <label for="bio" class="form-label">Biography</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3" 
                                          placeholder="Brief background, education, experience, etc."><?php 
                                    echo htmlspecialchars($editCandidate['bio'] ?? ''); 
                                ?></textarea>
                                <small class="text-muted">A brief background about the candidate</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Photo Upload Section -->
                <div class="col-12">
                    <div class="form-section">
                        <h4 class="section-title">Candidate Photo</h4>
                        <div class="photo-upload-container" id="photoUploadContainer">
                            <div id="photoPreviewContainer" class="mb-3">
                                <?php if (isset($editCandidate) && !empty($editCandidate['photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($editCandidate['photo']); ?>" 
                                         alt="Candidate Photo" 
                                         class="photo-preview" 
                                         id="photoPreview">
                                <?php else: ?>
                                    <div class="text-center py-4" id="photoPlaceholder">
                                        <i class="bi bi-image display-4 text-muted"></i>
                                        <p class="mt-2 mb-0">No photo selected</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" class="d-none" id="photo" name="photo" accept="image/*">
                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('photo').click()">
                                <i class="bi bi-upload me-1"></i> Choose Photo
                            </button>
                            <p class="small text-muted mt-2 mb-0">
                                Recommended size: 400x400px. Max file size: 2MB. Allowed formats: JPG, PNG, GIF, WebP
                            </p>
                            <?php if (isset($editCandidate) && !empty($editCandidate['photo'])): ?>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="remove_photo" name="remove_photo">
                                    <label class="form-check-label text-danger" for="remove_photo">
                                        Remove current photo
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Status Section -->
                <div class="col-12">
                    <div class="form-section">
                        <h4 class="section-title">Status</h4>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="status" name="status" value="1" 
                                <?php echo (!isset($editCandidate['status']) || $editCandidate['status'] == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                    </div>
                </div>
                
                <!-- Form Submission Buttons -->
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center border-top pt-4 mt-3">
                        <div>
                            <a href="manage-candidates.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                        </div>
                        <div>
                            <button type="reset" class="btn btn-outline-danger me-2">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Form
                            </button>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> 
                                <?php echo isset($editCandidate) ? 'Update' : 'Save'; ?> Candidate
                                <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status"></span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Main Submit Button -->
                <div class="col-12 mt-4">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg py-3">
                            <i class="bi bi-save me-2"></i>
                            <?php echo isset($editCandidate) ? 'Update Candidate' : 'Save Candidate'; ?>
                            <span class="spinner-border spinner-border-sm ms-2 d-none" id="mainSubmitSpinner" role="status"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Form Submission Feedback -->
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="formFeedback" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <strong class="me-auto">Success</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    Candidate information has been saved successfully!
                </div>
            </div>
        </div>

        <!-- Candidates List -->
        <div class="candidates-list mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 mb-0">All Candidates</h2>
                <a href="?add" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add New Candidate
                </a>
            </div>
            
            <!-- Search and Filter Bar -->
            <div class="card mb-4">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search candidates by name, position, or election...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select" id="electionFilter">
                                <option value="">All Elections</option>
                                <?php foreach ($elections as $election): ?>
                                    <option value="<?php echo htmlspecialchars($election['title']); ?>">
                                        <?php echo htmlspecialchars($election['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary me-2">Total: <?php echo is_array($candidates) ? count($candidates) : 0; ?></span>
                    <span class="badge bg-success me-2">Active: <?php echo is_array($candidates) ? count(array_filter($candidates, function($c) { return $c['status'] == 1; })) : 0; ?></span>
                    <span class="badge bg-secondary">Inactive: <?php echo is_array($candidates) ? count(array_filter($candidates, function($c) { return $c['status'] == 0; })) : 0; ?></span>
                </div>
            </div>

            <?php if (empty($candidates) || !is_array($candidates)): ?>
                <div class="alert alert-info">No candidates found. Add your first candidate using the form above.</div>
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($candidates as $candidate): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($candidate['photo_path'])): ?>
                                            <img src="<?php echo htmlspecialchars($candidate['photo_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($candidate['name']); ?>" 
                                                 class="candidate-photo">
                                        <?php else: ?>
                                            <div class="candidate-photo bg-light d-flex align-items-center justify-content-center">
                                                <i class="bi bi-person" style="font-size: 2rem; color: #6c757d;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($candidate['name']); ?></strong>
                                        <?php if (!empty($candidate['platform'])): ?>
                                            <p class="text-muted small mb-0">
                                                <?php echo htmlspecialchars(substr($candidate['platform'], 0, 50)); ?>
                                                <?php echo strlen($candidate['platform']) > 50 ? '...' : ''; ?>
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($candidate['position_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['election_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php 
                                        $status = $candidate['status'] ?? 'active';
                                        $statusClass = $status === 'active' ? 'bg-success' : 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="?edit=<?php echo $candidate['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="?action=delete&id=<?php echo $candidate['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this candidate? This action cannot be undone.')">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview photo before upload
        function previewPhoto(input) {
            const previewContainer = document.getElementById('photoPreviewContainer');
            const photoPlaceholder = document.getElementById('photoPlaceholder');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (previewContainer) {
                        // Remove existing preview or placeholder
                        const existingPreview = document.getElementById('photoPreview');
                        if (existingPreview) {
                            existingPreview.remove();
                        }
                        if (photoPlaceholder) {
                            photoPlaceholder.remove();
                        }
                        
                        // Create and append new preview image
                        const img = document.createElement('img');
                        img.id = 'photoPreview';
                        img.src = e.target.result;
                        img.className = 'photo-preview';
                        img.alt = 'Photo preview';
                        previewContainer.prepend(img);
                    }
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Handle form submission
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            const submitSpinner = document.getElementById('submitSpinner');
            const formFeedback = document.getElementById('formFeedback');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Show loading state
                    submitBtn.disabled = true;
                    submitSpinner.classList.remove('d-none');
                    
                    // You can add additional form validation here if needed
                    
                    // For demo purposes, we'll show the toast after a short delay
                    // In a real application, this would be handled by the form submission response
                    setTimeout(() => {
                        const toast = new bootstrap.Toast(formFeedback);
                        toast.show();
                        
                        // Reset form if it was a new entry
                        if (!<?php echo isset($editCandidate) ? 'true' : 'false'; ?>) {
                            form.reset();
                            // Reset photo preview
                            const previewContainer = document.getElementById('photoPreviewContainer');
                            const existingPreview = document.getElementById('photoPreview');
                            const photoPlaceholder = document.getElementById('photoPlaceholder');
                            
                            if (existingPreview) existingPreview.remove();
                            if (!photoPlaceholder) {
                                const placeholder = document.createElement('div');
                                placeholder.id = 'photoPlaceholder';
                                placeholder.className = 'text-center py-4';
                                placeholder.innerHTML = `
                                    <i class="bi bi-image display-4 text-muted"></i>
                                    <p class="mt-2 mb-0">No photo selected</p>
                                `;
                                previewContainer.prepend(placeholder);
                            }
                        }
                        
                        // Reset button state
                        submitBtn.disabled = false;
                        submitSpinner.classList.add('d-none');
                    }, 1000);
                });
            }
            const uploadContent = document.getElementById('photoUploadContent');
            const file = input.files[0];
            
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, GIF)');
                    input.value = '';
                    return;
                }
                
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('Image size should not exceed 2MB');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Create image to check dimensions
                    const img = new Image();
                    img.onload = function() {
                        // You can add dimension validation here if needed
                        // For example, ensure it's square or meets certain dimensions
                        
                        // Display the preview
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        previewContainer.style.display = 'block';
                        if (uploadContent) uploadContent.style.display = 'none';
                        
                        // Show remove button
                        document.getElementById('removePhotoBtn').style.display = 'block';
                    };
                    img.src = e.target.result;
                };
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                previewContainer.style.display = 'none';
                if (uploadContent) uploadContent.style.display = 'block';
            }
        }
        
        // Initialize photo preview if editing with existing photo
        document.addEventListener('DOMContentLoaded', function() {
            // Photo upload container click handler
            const photoUploadContainer = document.getElementById('photoUploadContainer');
            const photoInput = document.getElementById('photo');
            
            if (photoUploadContainer && photoInput) {
                photoUploadContainer.addEventListener('click', function() {
                    photoInput.click();
                });
            }
            
            // Remove photo button handler
            const removePhotoBtn = document.getElementById('removePhotoBtn');
            if (removePhotoBtn) {
                removePhotoBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const photoInput = document.getElementById('photo');
                    const previewContainer = document.getElementById('photoPreviewContainer');
                    const uploadContent = document.getElementById('photoUploadContent');
                    
                    // Reset file input
                    if (photoInput) {
                        photoInput.value = '';
                        // For IE11
                        photoInput.type = '';
                        photoInput.type = 'file';
                    }
                    
                    // Hide preview and show upload content
                    if (previewContainer) previewContainer.style.display = 'none';
                    if (uploadContent) uploadContent.style.display = 'block';
                    
                    // If editing, show the remove photo checkbox
                    const removeCheckbox = document.getElementById('remove_photo');
                    if (removeCheckbox) {
                        removeCheckbox.checked = true;
                    }
                });
            }
            
            // Initialize with existing photo if editing
            <?php if (isset($editCandidate) && !empty($editCandidate['photo'])): ?>
                const preview = document.getElementById('photoPreview');
                const previewContainer = document.getElementById('photoPreviewContainer');
                const uploadContent = document.getElementById('photoUploadContent');
                
                if (preview) {
                    preview.src = '<?php echo htmlspecialchars($editCandidate['photo']); ?>';
                    preview.style.display = 'block';
                    if (previewContainer) previewContainer.style.display = 'block';
                    if (uploadContent) uploadContent.style.display = 'none';
                }
            <?php endif; ?>
            
            // Handle remove photo checkbox
            const removePhotoCheckbox = document.getElementById('remove_photo');
            
            if (removePhotoCheckbox && photoInput) {
                removePhotoCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        photoInput.required = true;
                    } else {
                        photoInput.required = false;
                    }
                });
            }
        });
    </script>
</body>
</html>