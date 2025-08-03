<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Candidates - UNZANASA Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .candidate-photo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .candidate-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 15px;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        .action-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .photo-preview {
            max-width: 150px;
            max-height: 150px;
            margin: 10px 0;
            display: none;
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
        <div class="candidate-form mb-4">
            <h3><?php echo isset($editCandidate) ? 'Edit Candidate' : 'Add New Candidate'; ?></h3>
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <?php if (isset($editCandidate)): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($editCandidate['id']); ?>">
                <?php endif; ?>
                
                <div class="col-md-6">
                    <label for="name" class="form-label">Candidate Name *</label>
                    <input type="text" class="form-control" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($editCandidate['name'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6">
                    <label for="position" class="form-label">Position *</label>
                    <input type="text" class="form-control" id="position" name="position" required
                           value="<?php echo htmlspecialchars($editCandidate['position'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6">
                    <label for="election_id" class="form-label">Election *</label>
                    <select class="form-select" id="election_id" name="election_id" required>
                        <option value="">Select an election</option>
                        <?php foreach ($elections as $election): ?>
                            <option value="<?php echo $election['id']; ?>"
                                <?php echo (isset($editCandidate) && $editCandidate['election_id'] == $election['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($election['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12">
                    <label for="description" class="form-label">Description/Manifesto</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?php 
                        echo htmlspecialchars($editCandidate['description'] ?? ''); 
                    ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <label for="photo" class="form-label">Candidate Photo</label>
                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*" 
                           onchange="previewPhoto(this)">
                    <small class="text-muted">Max size: 2MB. Formats: JPG, PNG, GIF</small>
                    <?php if (isset($editCandidate) && !empty($editCandidate['photo_path'])): ?>
                        <div class="mt-2">
                            <img src="<?php echo htmlspecialchars($editCandidate['photo_path']); ?>" 
                                 alt="Current photo" class="img-thumbnail" style="max-height: 100px;">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo">
                                <label class="form-check-label" for="remove_photo">
                                    Remove current photo
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <img id="photoPreview" class="photo-preview img-thumbnail" alt="Preview">
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> <?php echo isset($editCandidate) ? 'Update' : 'Create'; ?> Candidate
                    </button>
                    <?php if (isset($editCandidate)): ?>
                        <a href="manage-candidates.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x"></i> Cancel
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Candidates List -->
        <div class="candidates-list">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>All Candidates</h3>
                <div>
                    <span class="badge bg-primary">Total: <?php echo is_array($candidates) ? count($candidates) : 0; ?></span>
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
                                        <?php if (!empty($candidate['description'])): ?>
                                            <p class="text-muted small mb-0">
                                                <?php echo htmlspecialchars(substr($candidate['description'], 0, 50)); ?>
                                                <?php echo strlen($candidate['description']) > 50 ? '...' : ''; ?>
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($candidate['position']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['election_name'] ?? 'N/A'); ?></td>
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
            const preview = document.getElementById('photoPreview');
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }
        
        // Initialize photo preview if editing with existing photo
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($editCandidate) && !empty($editCandidate['photo_path'])): ?>
                const preview = document.getElementById('photoPreview');
                if (preview) {
                    preview.src = '<?php echo htmlspecialchars($editCandidate['photo_path']); ?>';
                    preview.style.display = 'block';
                }
            <?php endif; ?>
            
            // Handle remove photo checkbox
            const removePhotoCheckbox = document.getElementById('remove_photo');
            const photoInput = document.getElementById('photo');
            
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