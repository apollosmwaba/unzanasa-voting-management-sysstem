<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Elections - UNZANASA Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h3><?php echo isset($editElection) ? 'Edit Election' : 'Add New Election'; ?></h3>
            <form method="POST" class="row g-3">
                <?php if (isset($editElection)): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($editElection['id']); ?>">
                <?php endif; ?>
                
                <div class="col-md-6">
                    <label for="name" class="form-label">Election Name *</label>
                    <input type="text" class="form-control" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($editElection['name'] ?? ''); ?>">
                </div>
                
                <div class="col-md-6">
                    <label for="max_votes" class="form-label">Max Votes per Voter *</label>
                    <input type="number" class="form-control" id="max_votes" name="max_votes" min="1" required 
                           value="<?php echo htmlspecialchars($editElection['max_votes'] ?? '1'); ?>">
                </div>
                
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2"><?php 
                        echo htmlspecialchars($editElection['description'] ?? ''); 
                    ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Start Date *</label>
                    <input type="datetime-local" class="form-control" id="start_date" name="start_date" required
                           value="<?php echo isset($editElection['start_date']) ? 
                           date('Y-m-d\TH:i', strtotime($editElection['start_date'])) : ''; ?>">
                </div>
                
                <div class="col-md-6">
                    <label for="end_date" class="form-label">End Date *</label>
                    <input type="datetime-local" class="form-control" id="end_date" name="end_date" required
                           value="<?php echo isset($editElection['end_date']) ? 
                           date('Y-m-d\TH:i', strtotime($editElection['end_date'])) : ''; ?>">
                </div>
                
                <?php if (isset($editElection)): ?>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo ($editElection['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($editElection['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                <?php endif; ?>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <?php echo isset($editElection) ? 'Update Election' : 'Create Election'; ?>
                    </button>
                    <?php if (isset($editElection)): ?>
                        <a href="manage-elections.php" class="btn btn-outline-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Elections List -->
        <div class="elections-list">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>All Elections</h3>
                <div>
                    <span class="badge bg-primary">Total: <?php echo is_array($elections) ? count($elections) : 0; ?></span>
                    <span class="badge bg-success">Active: <?php 
                        echo is_array($elections) ? count(array_filter($elections, fn($e) => ($e['status'] ?? '') === 'active')) : 0; 
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
                            <?php foreach ($elections as $election): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($election['name']); ?></strong>
                                        <?php if ($election['status'] === 'active'): ?>
                                            <span class="badge bg-success ms-2">Live</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($election['description'], 0, 50)); ?><?php 
                                        echo strlen($election['description']) > 50 ? '...' : ''; 
                                    ?></td>
                                    <td>
                                        <div><small class="text-muted">Start:</small> <?php 
                                            echo date('M j, Y g:i A', strtotime($election['start_date'])); 
                                        ?></div>
                                        <div><small class="text-muted">End:</small> <?php 
                                            echo date('M j, Y g:i A', strtotime($election['end_date'])); 
                                        ?></div>
                                    </td>
                                    <td><?php echo $election['max_votes']; ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $election['status']; ?>">
                                            <?php echo ucfirst($election['status']); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="?edit=<?php echo $election['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <?php if ($election['status'] === 'active'): ?>
                                            <a href="?action=deactivate&id=<?php echo $election['id']; ?>" 
                                               class="btn btn-sm btn-outline-warning"
                                               onclick="return confirm('Deactivate this election?')">
                                                <i class="bi bi-pause"></i> Pause
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=activate&id=<?php echo $election['id']; ?>" 
                                               class="btn btn-sm btn-outline-success"
                                               onclick="return confirm('Activate this election?')">
                                                <i class="bi bi-play"></i> Activate
                                            </a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?php echo $election['id']; ?>" 
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
        // Client-side validation for date ranges
        document.addEventListener('DOMContentLoaded', function() {
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            
            if (startDate && endDate) {
                startDate.addEventListener('change', function() {
                    endDate.min = startDate.value;
                    if (endDate.value && endDate.value < startDate.value) {
                        endDate.value = startDate.value;
                    }
                });
                
                endDate.addEventListener('change', function() {
                    if (endDate.value < startDate.value) {
                        alert('End date must be after start date');
                        endDate.value = startDate.value;
                    }
                });
            }
        });
    </script>
</body>
</html>