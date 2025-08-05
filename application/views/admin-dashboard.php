<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - UNZANASA Voting System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card .icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin: 0.5rem 0;
            display: block;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card h3 {
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
            padding: 1.5rem 1rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .action-btn .icon {
            font-size: 1.8rem;
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .activity-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-info {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .activity-time {
            font-size: 0.8rem;
            color: #666;
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .quick-actions {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 576px) {
            .stats-grid,
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <h1>🗳️ UNZANASA Admin Dashboard</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($admin['full_name'] ?? 'Admin'); ?></span>
            <a href="admin-logout.php" class="btn btn-danger">
                <span>🚪</span> Logout
            </a>
        </div>
    </header>
    
    <main class="container">
        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>" style="padding: 1rem; margin-bottom: 1.5rem; border-radius: 5px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="icon">👥</span>
                <span class="number"><?php echo number_format($electionStats['total_elections'] ?? 0); ?></span>
                <span class="label">Total Elections</span>
            </div>
            
            <div class="stat-card">
                <span class="icon">📊</span>
                <span class="number"><?php echo number_format($electionStats['active_elections'] ?? 0); ?></span>
                <span class="label">Active Elections</span>
            </div>
            
            <div class="stat-card">
                <span class="icon">✅</span>
                <span class="number"><?php echo number_format($electionStats['completed_elections'] ?? 0); ?></span>
                <span class="label">Completed Elections</span>
            </div>
            
            <div class="stat-card">
                <span class="icon">🗳️</span>
                <span class="number"><?php echo number_format($totalVotes ?? 0); ?></span>
                <span class="label">Total Votes Cast</span>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="manage-elections.php" class="action-btn">
                <span class="icon">📋</span>
                <span>Manage Elections</span>
            </a>
            <a href="manage-candidates.php" class="action-btn">
                <span class="icon">👤</span>
                <span>Manage Candidates</span>
            </a>
            <a href="upload-voters.php" class="action-btn">
                <span class="icon">📤</span>
                <span>Upload Voters</span>
            </a>
            <a href="view-results.php" class="action-btn">
                <span class="icon">📈</span>
                <span>View Results</span>
            </a>
            <a href="manage-admins.php" class="action-btn">
                <span class="icon">👨‍💼</span>
                <span>Manage Admins</span>
            </a>
        </div>
        
        <!-- Main Content -->
        <div class="dashboard-grid">
            <!-- Recent Activity -->
            <div class="card">
                <h3>📋 Recent Activity</h3>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-info">
                            <div class="activity-title">Admin logged in</div>
                            <div class="activity-time">Just now</div>
                        </div>
                        <span class="status-badge status-success">Success</span>
                    </div>
                    <!-- More activity items would be dynamically generated -->
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card">
                <h3>📊 Quick Stats</h3>
                <div style="margin-bottom: 1.5rem;">
                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span>Active Elections</span>
                            <span><?php echo $electionStats['active_elections'] ?? 0; ?></span>
                        </div>
                        <div style="height: 8px; background: #f0f0f0; border-radius: 4px; overflow: hidden;">
                            <div style="width: <?php echo min(100, ($electionStats['active_elections'] ?? 0 / max(1, $electionStats['total_elections'] ?? 0)) * 100); ?>%; height: 100%; background: #667eea;"></div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span>Total Candidates</span>
                            <span><?php echo $totalCandidates ?? 0; ?></span>
                        </div>
                        <div style="height: 8px; background: #f0f0f0; border-radius: 4px; overflow: hidden;">
                            <div style="width: <?php echo min(100, ($totalCandidates ?? 0 / max(1, $totalCandidates ?? 0)) * 100); ?>%; height: 100%; background: #764ba2;"></div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 1rem;">System Status</h4>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <span>Database</span>
                            <span style="color: #28a745;">● Online</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Last Backup</span>
                            <span><?php echo date('M j, Y H:i'); ?></span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span>Version</span>
                            <span>1.0.0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        // Simple chart for the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // This would be replaced with actual chart library integration
            console.log('Dashboard loaded');
        });
    </script>
</body>
</html>