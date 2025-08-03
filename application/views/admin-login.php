<?php
/**
 * UNZANASA Voting Management System - Admin Login
 * Part 3: Admin Authentication and Dashboard
 */

// admin-login.php
if (basename($_SERVER['PHP_SELF']) === 'admin-login.php') {
    require_once __DIR__ . '/../../init.php';
    
    // Redirect if already logged in
    if (Auth::check()) {
        Utils::redirect('admin-dashboard.php');
    }
    
    $error = '';
    
    if ($_POST) {
        $username = Utils::sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please fill in all fields';
        } else {
            $adminModel = new Admin();
            $admin = $adminModel->authenticate($username, $password);
            
            if ($admin) {
                $adminModel->createSession($admin['id']);
                Utils::flashMessage('Welcome back, ' . $admin['full_name'], 'success');
                Utils::redirect('admin-dashboard.php');
            } else {
                $error = 'Invalid username or password';
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - UNZANASA Voting System</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .login-container {
                background: white;
                padding: 2rem;
                border-radius: 10px;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 400px;
            }
            
            .logo {
                text-align: center;
                margin-bottom: 2rem;
            }
            
            .logo h1 {
                color: #333;
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }
            
            .logo p {
                color: #666;
                font-size: 0.9rem;
            }
            
            .form-group {
                margin-bottom: 1rem;
            }
            
            label {
                display: block;
                margin-bottom: 0.5rem;
                color: #333;
                font-weight: 500;
            }
            
            input[type="text"],
            input[type="password"] {
                width: 100%;
                padding: 0.75rem;
                border: 2px solid #e1e1e1;
                border-radius: 5px;
                font-size: 1rem;
                transition: border-color 0.3s;
            }
            
            input[type="text"]:focus,
            input[type="password"]:focus {
                outline: none;
                border-color: #667eea;
            }
            
            .btn {
                width: 100%;
                padding: 0.75rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 5px;
                font-size: 1rem;
                cursor: pointer;
                transition: transform 0.2s;
            }
            
            .btn:hover {
                transform: translateY(-2px);
            }
            
            .error {
                background: #ffe6e6;
                color: #d63031;
                padding: 0.75rem;
                border-radius: 5px;
                margin-bottom: 1rem;
                border-left: 4px solid #d63031;
            }
            
            .footer {
                text-align: center;
                margin-top: 2rem;
                color: #666;
                font-size: 0.9rem;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="logo">
                <h1>🗳️ UNZANASA</h1>
                <p>Voting Management System</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error">❌ <?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required 
                           value="<?= htmlspecialchars($username ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn">🔐 Login to Dashboard</button>
                <a href="index.php" class="btn" style="background: #6c757d; margin-top: 10px; display: inline-block; text-decoration: none; text-align: center;">🏠 Go to Index</a>
            </form>
            
            <div class="footer">
                <p>&copy; <?= date('Y') ?> UNZANASA. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// admin-dashboard.php
if (basename($_SERVER['PHP_SELF']) === 'admin-dashboard.php') {
    require_once __DIR__ . '/../../init.php';
    
    Auth::requireAuth();
    $admin = Auth::user();
    
    // Get dashboard statistics
    $electionModel = new Election();
    $candidateModel = new Candidate();
    $voteModel = new Vote();
    $computerNumberModel = new ComputerNumber();
    
    $electionStats = $electionModel->getElectionStats();
    $computerStats = $computerNumberModel->getStats();
    $recentActivity = $voteModel->getVotingActivity(10);
    
    // Get total votes across all elections
    $stmt = Database::getInstance()->getConnection()->query("SELECT COUNT(*) as total_votes FROM votes");
    $totalVotes = $stmt->fetch()['total_votes'];
    
    // Get total candidates
    $stmt = Database::getInstance()->getConnection()->query("SELECT COUNT(*) as total_candidates FROM candidates");
    $totalCandidates = $stmt->fetch()['total_candidates'];
    
    $flash = Utils::getFlashMessage();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard - UNZANASA Voting System</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #f5f7fa;
                color: #333;
            }
            
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 1rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .header .user-info {
                display: flex;
                align-items: center;
                gap: 1rem;
            }
            
            .logout-btn {
                background: rgba(255,255,255,0.2);
                color: white;
                padding: 0.5rem 1rem;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-decoration: none;
                transition: background 0.3s;
            }
            
            .logout-btn:hover {
                background: rgba(255,255,255,0.3);
            }
            
            .container {
                max-width: 1200px;
                margin: 2rem auto;
                padding: 0 2rem;
            }
            
            .flash-message {
                padding: 1rem;
                border-radius: 5px;
                margin-bottom: 2rem;
                border-left: 4px solid;
            }
            
            .flash-success {
                background: #d4edda;
                color: #155724;
                border-color: #28a745;
            }
            
            .flash-error {
                background: #f8d7da;
                color: #721c24;
                border-color: #dc3545;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }
            
            .stat-card {
                background: white;
                padding: 1.5rem;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
                transition: transform 0.3s;
            }
            
            .stat-card:hover {
                transform: translateY(-5px);
            }
            
            .stat-card .icon {
                font-size: 2rem;
                margin-bottom: 0.5rem;
            }
            
            .stat-card .number {
                font-size: 2rem;
                font-weight: bold;
                color: #667eea;
                margin-bottom: 0.5rem;
            }
            
            .stat-card .label {
                color: #666;
                font-size: 0.9rem;
            }
            
            .dashboard-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 2rem;
                margin-bottom: 2rem;
            }
            
            .card {
                background: white;
                padding: 1.5rem;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .card h3 {
                margin-bottom: 1rem;
                color: #333;
                border-bottom: 2px solid #667eea;
                padding-bottom: 0.5rem;
            }
            
            .quick-actions {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
                margin-bottom: 2rem;
            }
            
            .action-btn {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 1rem;
                border: none;
                border-radius: 10px;
                cursor: pointer;
                text-decoration: none;
                text-align: center;
                transition: transform 0.3s;
                display: block;
            }
            
            .action-btn:hover {
                transform: translateY(-3px);
                color: white;
            }
            
            .action-btn .icon {
                display: block;
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }
            
            .activity-item {
                padding: 0.75rem;
                border-bottom: 1px solid #eee;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .activity-item:last-child {
                border-bottom: none;
            }
            
            .activity-status {
                padding: 0.25rem 0.5rem;
                border-radius: 3px;
                font-size: 0.8rem;
                font-weight: bold;
            }
            
            .status-success {
                background: #d4edda;
                color: #155724;
            }
            
            .status-warning {
                background: #fff3cd;
                color: #856404;
            }
            
            .status-danger {
                background: #f8d7da;
                color: #721c24;
            }
            
            @media (max-width: 768px) {
                .dashboard-grid {
                    grid-template-columns: 1fr;
                }
                
                .header {
                    flex-direction: column;
                    gap: 1rem;
                }
                
                .container {
                    padding: 0 1rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>🗳️ UNZANASA Admin Dashboard</h1>
            <div class="user-info">
                <span>Welcome, <?= htmlspecialchars($admin['full_name']) ?></span>
                <a href="admin-logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </div>
        
        <div class="container">
            <?php if ($flash): ?>
                <div class="flash-message flash-<?= $flash['type'] ?>">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon">👥</div>
                    <div class="number"><?= number_format($computerStats['active_numbers']) ?></div>
                    <div class="label">Active Voters</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon">🗳️</div>
                    <div class="number"><?= number_format($totalVotes) ?></div>
                    <div class="label">Total Votes Cast</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon">🧍</div>
                    <div class="number"><?= number_format($totalCandidates) ?></div>
                    <div class="label">Total Candidates</div>
                </div>
                
                <div class="stat-card">
                    <div class="icon">📊</div>
                    <div class="number"><?= number_format($electionStats['total_elections']) ?></div>
                    <div class="label">Total Elections</div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="manage-elections.php" class="action-btn">
                    <span class="icon">📋</span>
                    Manage Elections
                </a>
                <a href="manage-candidates.php" class="action-btn">
                    <span class="icon">👤</span>
                    Manage Candidates
                </a>
                <a href="upload-voters.php" class="action-btn">
                    <span class="icon">📤</span>
                    Upload Voters
                </a>
                <a href="view-results.php" class="action-btn">
                    <span class="icon">📈</span>
                    View Results
                </a>
            </div>
            
            <!-- Dashboard Content -->
            <div class="dashboard-grid">
                <div class="card">
                    <h3>📊 Election Analytics</h3>
                    <canvas id="electionChart" width="400" height="200"></canvas>
                </div>
                
                <div class="card">
                    <h3>📋 Recent Voting Activity</h3>
                    <div class="activity-list">
                        <?php if (empty($recentActivity)): ?>
                            <p>No recent voting activity</p>
                        <?php else: ?>
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="activity-item">
                                    <div>
                                        <strong><?= htmlspecialchars($activity['computer_number']) ?></strong>
                                        <br>
                                        <small><?= htmlspecialchars($activity['election_title']) ?></small>
                                    </div>
                                    <div>
                                        <span class="activity-status status-<?= $activity['action'] === 'vote_cast' ? 'success' : ($activity['action'] === 'invalid_attempt' ? 'danger' : 'warning') ?>">
                                            <?= ucfirst(str_replace('_', ' ', $activity['action'])) ?>
                                        </span>
                                        <br>
                                        <small><?= Utils::formatDateTime($activity['timestamp']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            // Election Analytics Chart
            const ctx = document.getElementById('electionChart').getContext('2d');
            
            // Fetch election data for chart
            fetch('api/election-stats.php')
                .then(response => response.json())
                .then(data => {
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Active Elections', 'Completed Elections', 'Draft Elections'],
                            datasets: [{
                                data: [
                                    <?= $electionStats['active_elections'] ?>,
                                    <?= $electionStats['completed_elections'] ?>,
                                    <?= $electionStats['total_elections'] - $electionStats['active_elections'] - $electionStats['completed_elections'] ?>
                                ],
                                backgroundColor: [
                                    '#28a745',
                                    '#667eea',
                                    '#ffc107'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                });
        </script>
    </body>
    </html>
    <?php
    exit;
}