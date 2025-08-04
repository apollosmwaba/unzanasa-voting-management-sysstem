<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Elections - UNZANASA Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .election-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .election-card {
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        .election-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
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
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage Elections</h1>
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

        <!-- Add/Edit Election Form -->
        <div class="election-form mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?php echo isset($editElection) ? 'Edit Election' : 'Add New Election'; ?></h4>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <?php 
                        // Initialize variables with default values for new election
                        $editId = '';
                        $editTitle = '';
                        $editDescription = '';
                        $editStartDate = '';
                        $editEndDate = '';
                        $editStatus = 'draft';
                        $editCreatedBy = '';
                        $editCreatedAt = '';
                        $editUpdatedAt = '';
                        $editMaxVotes = 1;
                        
                        // Populate variables if editing an existing election
                        if (isset($editElection)) {
                            $editId = is_object($editElection) ? ($editElection->id ?? '') : ($editElection['id'] ?? '');
                            $editTitle = is_object($editElection) ? ($editElection->title ?? '') : ($editElection['title'] ?? '');
                            $editDescription = is_object($editElection) ? ($editElection->description ?? '') : ($editElection['description'] ?? '');
                            $editStartDate = is_object($editElection) ? ($editElection->start_date ?? '') : ($editElection['start_date'] ?? '');
                            $editEndDate = is_object($editElection) ? ($editElection->end_date ?? '') : ($editElection['end_date'] ?? '');
                            $editStatus = is_object($editElection) ? ($editElection->status ?? 'draft') : ($editElection['status'] ?? 'draft');
                            $editCreatedBy = is_object($editElection) ? ($editElection->created_by ?? 'System') : ($editElection['created_by'] ?? 'System');
                            $editCreatedAt = is_object($editElection) ? ($editElection->created_at ?? '') : ($editElection['created_at'] ?? '');
                            $editUpdatedAt = is_object($editElection) ? ($editElection->updated_at ?? '') : ($editElection['updated_at'] ?? '');
                            $editMaxVotes = is_object($editElection) ? ($editElection->max_votes ?? 1) : ($editElection['max_votes'] ?? 1);
                        
                            if (!empty($editId)) {
                                echo '<input type="hidden" name="id" value="' . htmlspecialchars($editId) . '">';
                            }
                        }
                        ?>
                        
                        <div class="row g-3">
                            <!-- Basic Information -->
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">Basic Information</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="title" class="form-label">Election Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" required 
                                       value="<?php echo isset($editElection) ? htmlspecialchars($editTitle ?? '') : ''; ?>"
                                       placeholder="Enter election title">
                                <div class="invalid-feedback">
                                    Please provide an election title.
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="draft" <?php echo ($editStatus ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="active" <?php echo ($editStatus ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($editStatus ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="completed" <?php echo ($editStatus ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <div class="form-text">Draft: Not visible to voters | Active: Accepting votes | Inactive: Visible but not accepting votes | Completed: Voting ended</div>
                            </div>
                            
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    placeholder="Enter a detailed description of this election"><?php 
                                    echo isset($editElection) ? htmlspecialchars($editDescription ?? '') : ''; 
                                ?></textarea>
                                <div class="form-text">Provide information about this election that will be visible to voters</div>
                            </div>
                            
                            <!-- Date & Time Settings -->
                            <div class="col-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3">Date & Time Settings</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="start_date" name="start_date" required
                                       value="<?php echo isset($editStartDate) ? date('Y-m-d\TH:i', strtotime($editStartDate)) : ''; ?>">
                                <div class="form-text">When the election will start accepting votes</div>
                                <div class="invalid-feedback">
                                    Please select a valid start date and time.
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date & Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="end_date" name="end_date" required
                                       value="<?php echo isset($editEndDate) ? date('Y-m-d\TH:i', strtotime($editEndDate)) : ''; ?>">
                                <div class="form-text">When the election will stop accepting votes</div>
                                <div class="invalid-feedback">
                                    Please select a valid end date and time after the start date.
                                </div>
                            </div>
                            
                            <!-- Additional Settings -->
                            <div class="col-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3">Additional Settings</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="created_by" class="form-label">Created By</label>
                                <input type="text" class="form-control" id="created_by" name="created_by" readonly
                                       value="<?php 
                                           if (isset($editElection)) {
                                               echo htmlspecialchars($editCreatedBy ?? 'System');
                                           } else {
                                               echo htmlspecialchars($_SESSION['admin_username'] ?? 'System');
                                           }
                                       ?>">
                                <div class="form-text">User who created this election</div>
                            </div>
                            
                            <?php if (isset($editElection)): ?>
                                <div class="col-md-6">
                                    <label class="form-label">Last Updated</label>
                                    <input type="text" class="form-control" readonly
                                           value="<?php 
                                               $updatedAt = is_object($editElection) ? ($editElection->updated_at ?? 'Never') : ($editElection['updated_at'] ?? 'Never');
                                               echo $updatedAt !== 'Never' ? date('M j, Y g:i A', strtotime($updatedAt)) : 'Never';
                                           ?>">
                                    <div class="form-text">Last time this election was modified</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Created At</label>
                                    <input type="text" class="form-control" readonly
                                           value="<?php 
                                               $createdAt = is_object($editElection) ? ($editElection->created_at ?? '') : ($editElection['created_at'] ?? '');
                                               echo $createdAt ? date('M j, Y g:i A', strtotime($createdAt)) : 'N/A';
                                           ?>">
                                    <div class="form-text">When this election was created</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Election ID</label>
                                    <input type="text" class="form-control" readonly value="<?php echo htmlspecialchars($editId); ?>">
                                    <div class="form-text">Unique identifier for this election</div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Positions Section -->
                            <div class="col-12 mt-4">
                                <h5 class="border-bottom pb-2 mb-3">Election Positions</h5>
                                <div class="positions-container">
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <p class="text-muted">Add the positions that students will vote for in this election.</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Dynamic Positions List -->
                                    <div id="positions-list">
                                        <div class="position-item mb-3 p-3 border rounded" data-position="0">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Position Title <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="positions[0][title]" placeholder="e.g., President" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Display Order</label>
                                                    <input type="number" class="form-control" name="positions[0][display_order]" value="1" min="1">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Max Votes</label>
                                                    <input type="number" class="form-control" name="positions[0][max_vote]" value="1" min="1">
                                                </div>
                                                <div class="col-md-1">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-position" disabled>
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control" name="positions[0][description]" rows="2" placeholder="Brief description of this position"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12">
                                            <button type="button" class="btn btn-outline-primary" id="add-position">
                                                <i class="bi bi-plus-circle"></i> Add Another Position
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <div class="d-flex justify-content-between">
                                    <?php if (isset($editElection)): ?>
                                        <a href="manage-elections.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-left"></i> Back to List
                                        </a>
                                    <?php else: ?>
                                        <div></div>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <button type="reset" class="btn btn-outline-secondary me-2">
                                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> <?php echo isset($editElection) ? 'Update Election' : 'Create Election'; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Elections List -->
        <div class="elections-list">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>All Elections</h3>
                <div>
                    <span class="badge bg-primary">Total: <?php echo is_countable($elections) ? count($elections) : 0; ?></span>
                    <span class="badge bg-success">Active: <?php 
                        if (is_countable($elections)) {
                            $activeCount = 0;
                            foreach ($elections as $election) {
                                if (is_object($election) && property_exists($election, 'status') && $election->status === 'active') {
                                    $activeCount++;
                                } elseif (is_array($election) && ($election['status'] ?? '') === 'active') {
                                    $activeCount++;
                                }
                            }
                            echo $activeCount;
                        } else {
                            echo 0;
                        }
                    ?></span>
                </div>
            </div>

            <?php if (empty($elections) || !is_array($elections)): ?>
                <div class="alert alert-info">No elections found. Create your first election using the form above.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Period</th>
                                <th>Max Votes</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($elections as $election): 
                                // Handle both object and array access with null coalescing
                                $electionId = is_object($election) ? ($election->id ?? '') : ($election['id'] ?? '');
                                $electionTitle = is_object($election) ? ($election->title ?? '') : ($election['title'] ?? '');
                                $electionStatus = is_object($election) ? ($election->status ?? 'inactive') : ($election['status'] ?? 'inactive');
                                $electionDescription = is_object($election) ? ($election->description ?? '') : ($election['description'] ?? '');
                                $electionStartDate = is_object($election) ? ($election->start_date ?? '') : ($election['start_date'] ?? '');
                                $electionEndDate = is_object($election) ? ($election->end_date ?? '') : ($election['end_date'] ?? '');
                                $electionMaxVotes = is_object($election) ? ($election->max_votes ?? 1) : ($election['max_votes'] ?? 1);
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($electionTitle); ?></strong>
                                        <?php if ($electionStatus === 'active'): ?>
                                            <span class="badge bg-success ms-2">Live</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($electionDescription, 0, 50)); ?><?php 
                                        echo strlen($electionDescription) > 50 ? '...' : ''; 
                                    ?></td>
                                    <td>
                                        <div><small class="text-muted">Start:</small> <?php 
                                            echo date('M j, Y g:i A', strtotime($electionStartDate)); 
                                        ?></div>
                                        <div><small class="text-muted">End:</small> <?php 
                                            echo date('M j, Y g:i A', strtotime($electionEndDate)); 
                                        ?></div>
                                    </td>
                                    <td><?php echo $electionMaxVotes; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $electionStatus; ?>">
                                            <?php echo ucfirst($electionStatus); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="?edit=<?php echo $electionId; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <?php if ($electionStatus === 'active'): ?>
                                            <a href="?action=deactivate&id=<?php echo $electionId; ?>" 
                                               class="btn btn-sm btn-outline-warning"
                                               onclick="return confirm('Deactivate this election?')">
                                                <i class="bi bi-pause"></i> Pause
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=activate&id=<?php echo $electionId; ?>" 
                                               class="btn btn-sm btn-outline-success"
                                               onclick="return confirm('Activate this election?')">
                                                <i class="bi bi-play"></i> Activate
                                            </a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?php echo $electionId; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this election? This action cannot be undone.')">
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
        // Enable Bootstrap form validation
        (function () {
            'use strict'

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            const forms = document.querySelectorAll('.needs-validation')

            // Loop over them and prevent submission
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    
                    // Additional date validation
                    const startDate = document.getElementById('start_date');
                    const endDate = document.getElementById('end_date');
                    
                    if (startDate && endDate) {
                        const start = new Date(startDate.value);
                        const end = new Date(endDate.value);
                        
                        if (start >= end) {
                            event.preventDefault();
                            event.stopPropagation();
                            alert('End date must be after start date');
                            endDate.focus();
                            return false;
                        }
                    }
                    
                    form.classList.add('was-validated')
                }, false)
            });
            
            // Set minimum dates for date inputs
            const today = new Date();
            const timezoneOffset = today.getTimezoneOffset() * 60000; // Convert minutes to milliseconds
            const localISOTime = (new Date(Date.now() - timezoneOffset)).toISOString().slice(0, 16);
            
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            if (startDateInput && !startDateInput.value) {
                startDateInput.min = localISOTime;
                startDateInput.value = localISOTime;
            }
            
            if (endDateInput && !endDateInput.value) {
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                const tomorrowISO = tomorrow.toISOString().slice(0, 16);
                
                endDateInput.min = tomorrowISO;
                endDateInput.value = tomorrowISO;
            }
            
            // Update end date min when start date changes
            if (startDateInput && endDateInput) {
                startDateInput.addEventListener('change', function() {
                    const startDate = new Date(this.value);
                    const minEndDate = new Date(startDate.getTime() + 3600000); // 1 hour later
                    
                    endDateInput.min = minEndDate.toISOString().slice(0, 16);
                    
                    if (new Date(endDateInput.value) <= startDate) {
                        endDateInput.value = minEndDate.toISOString().slice(0, 16);
                    }
                });
            }
        })();
        
        // Positions Management
        let positionCounter = 1;
        
        // Add new position
        document.getElementById('add-position').addEventListener('click', function() {
            const positionsList = document.getElementById('positions-list');
            const newPosition = document.createElement('div');
            newPosition.className = 'position-item mb-3 p-3 border rounded';
            newPosition.setAttribute('data-position', positionCounter);
            
            newPosition.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Position Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="positions[${positionCounter}][title]" placeholder="e.g., Vice President" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Display Order</label>
                        <input type="number" class="form-control" name="positions[${positionCounter}][display_order]" value="${positionCounter + 1}" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Max Votes</label>
                        <input type="number" class="form-control" name="positions[${positionCounter}][max_vote]" value="1" min="1">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-position">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="positions[${positionCounter}][description]" rows="2" placeholder="Brief description of this position"></textarea>
                    </div>
                </div>
            `;
            
            positionsList.appendChild(newPosition);
            positionCounter++;
            
            // Update remove button states
            updateRemoveButtons();
        });
        
        // Remove position
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-position')) {
                const positionItem = e.target.closest('.position-item');
                if (positionItem) {
                    positionItem.remove();
                    updateRemoveButtons();
                }
            }
        });
        
        // Update remove button states (disable if only one position)
        function updateRemoveButtons() {
            const positions = document.querySelectorAll('.position-item');
            const removeButtons = document.querySelectorAll('.remove-position');
            
            removeButtons.forEach(button => {
                button.disabled = positions.length <= 1;
            });
        }
        
        // Initialize remove button states
        updateRemoveButtons();
    </script>
</body>
</html>