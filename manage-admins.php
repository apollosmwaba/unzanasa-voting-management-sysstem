<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Require admin authentication
Auth::requireAuth();

$message = '';
$messageType = 'success';

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $fullName = trim($_POST['full_name'] ?? '');
        
        // Validation
        if (empty($username) || empty($email) || empty($password)) {
            $message = 'Username, email, and password are required.';
            $messageType = 'danger';
        } elseif ($password !== $confirmPassword) {
            $message = 'Passwords do not match.';
            $messageType = 'danger';
        } elseif (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters long.';
            $messageType = 'danger';
        } else {
            $admin = new Admin();
            $adminData = [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'full_name' => $fullName
            ];
            
            if ($admin->create($adminData)) {
                $message = 'Admin account created successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to create admin account. Username or email may already exist.';
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'delete') {
        $adminId = (int)($_POST['admin_id'] ?? 0);
        $currentAdminId = $_SESSION['admin_id'] ?? 0;
        
        if ($adminId === $currentAdminId) {
            $message = 'You cannot delete your own account.';
            $messageType = 'danger';
        } else {
            $admin = new Admin();
            if ($admin->delete($adminId)) {
                $message = 'Admin account deleted successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to delete admin account. Cannot delete the last admin.';
                $messageType = 'danger';
            }
        }
    }
}

// Get all admins
$admin = new Admin();
$admins = $admin->getAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - UNZANASA Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .admin-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 1rem;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .admin-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .current-admin {
            border-color: #28a745;
            background-color: #f8fff9;
        }
        
        .nav-buttons {
            margin-bottom: 2rem;
        }
        
        .nav-buttons .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Navigation -->
        <div class="nav-buttons">
            <a href="admin-dashboard.php" class="btn btn-primary">
                <i class="fas fa-home me-1"></i>Dashboard
            </a>
            <a href="admin-register.php" class="btn btn-success">
                <i class="fas fa-user-plus me-1"></i>Add New Admin
            </a>
            <a href="manage-elections.php" class="btn btn-info">
                <i class="fas fa-vote-yea me-1"></i>Manage Elections
            </a>
            <a href="view-results.php" class="btn btn-warning">
                <i class="fas fa-chart-bar me-1"></i>View Results
            </a>
        </div>
        
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-users-cog me-2"></i>Admin Management</h1>
            <p>Manage administrator accounts and permissions</p>
        </div>
        
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Admin Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Admins</h5>
                        <h2 class="mb-0"><?php echo count($admins); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Active Admins</h5>
                        <h2 class="mb-0"><?php echo count(array_filter($admins, function($a) { return ($a['status'] ?? 'active') === 'active'; })); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Current User</h5>
                        <h6 class="mb-0"><?php 
                            $currentAdmin = array_filter($admins, function($a) { return $a['id'] == ($_SESSION['admin_id'] ?? 0); });
                            echo htmlspecialchars(reset($currentAdmin)['username'] ?? 'Unknown'); 
                        ?></h6>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Add New Admin -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>Add New Administrator
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="col-md-6">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-1"></i>Username <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Email <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="col-md-12">
                        <label for="full_name" class="form-label">
                            <i class="fas fa-id-card me-1"></i>Full Name
                        </label>
                        <input type="text" class="form-control" id="full_name" name="full_name">
                    </div>
                    
                    <div class="col-md-6">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                        <div class="form-text">Minimum 6 characters</div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label">
                            <i class="fas fa-lock me-1"></i>Confirm Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>Create Admin Account
                        </button>
                        <button type="reset" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-undo me-1"></i>Reset Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Admins List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Administrator Accounts</h5>
            </div>
            <div class="card-body">
                <?php if (empty($admins)): ?>
                    <div class="alert alert-info">No admin accounts found.</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($admins as $adminUser): 
                            $isCurrentAdmin = $adminUser['id'] == ($_SESSION['admin_id'] ?? 0);
                        ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="admin-card <?php echo $isCurrentAdmin ? 'current-admin' : ''; ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="card-title mb-1">
                                                    <?php echo htmlspecialchars($adminUser['username']); ?>
                                                    <?php if ($isCurrentAdmin): ?>
                                                        <span class="badge bg-success ms-1">You</span>
                                                    <?php endif; ?>
                                                </h6>
                                                <p class="card-text text-muted mb-1">
                                                    <small><?php echo htmlspecialchars($adminUser['email'] ?? 'No email'); ?></small>
                                                </p>
                                                <p class="card-text text-muted mb-1">
                                                    <small>
                                                        <strong>Name:</strong> 
                                                        <?php echo htmlspecialchars(($adminUser['first_name'] ?? '') . ' ' . ($adminUser['last_name'] ?? '') ?: $adminUser['full_name'] ?? 'N/A'); ?>
                                                    </small>
                                                </p>
                                                <p class="card-text text-muted mb-1">
                                                    <small>
                                                        <strong>Created:</strong> 
                                                        <?php echo date('M j, Y', strtotime($adminUser['created_at'])); ?>
                                                    </small>
                                                </p>
                                                <?php if ($adminUser['last_login']): ?>
                                                    <p class="card-text text-muted mb-0">
                                                        <small>
                                                            <strong>Last Login:</strong> 
                                                            <?php echo date('M j, Y g:i A', strtotime($adminUser['last_login'])); ?>
                                                        </small>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!$isCurrentAdmin): ?>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this admin account? This action cannot be undone.')">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="admin_id" value="<?php echo $adminUser['id']; ?>">
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="fas fa-trash me-1"></i>Delete Account
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Instructions</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li><strong>Add New Admin:</strong> Click the "Add New Admin" button to create a new administrator account.</li>
                    <li><strong>Delete Admin:</strong> Use the dropdown menu on each admin card to delete accounts (except your own).</li>
                    <li><strong>Security:</strong> At least one admin account must always exist in the system.</li>
                    <li><strong>Current User:</strong> Your account is highlighted in green and cannot be deleted.</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
