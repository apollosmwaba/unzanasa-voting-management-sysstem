<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Require admin authentication
Auth::requireAuth();

// Get admin user data
$admin = Auth::user() ?? ['full_name' => 'Administrator'];

// Initialize variables
$voterTurnoutData = [];
$totalVoters = 0;
$electionStats = [];

try {
    // Get database connection
    $db = new Database();
    
    // Get voter turnout data - computer numbers that have voted
    $db->query("
        SELECT DISTINCT 
            vr.voter_id as computer_number,
            COUNT(v.id) as votes_cast,
            MIN(v.voted_at) as first_vote_time,
            MAX(v.voted_at) as last_vote_time,
            GROUP_CONCAT(e.title ORDER BY v.voted_at SEPARATOR ', ') as elections_voted
        FROM voters vr
        INNER JOIN votes v ON vr.id = v.voter_id
        INNER JOIN elections e ON v.election_id = e.id
        GROUP BY vr.voter_id
        ORDER BY first_vote_time DESC
    ");
    $voterTurnoutData = $db->resultSet() ?? [];
    $totalVoters = count($voterTurnoutData);
    
    // Get election statistics for context
    $db->query("
        SELECT 
            COUNT(*) as total_elections,
            SUM(CASE WHEN status = 'active' AND start_date <= NOW() AND end_date >= NOW() THEN 1 ELSE 0 END) as active_elections,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_elections
        FROM elections
    ");
    $electionStats = $db->single() ?? ['total_elections' => 0, 'active_elections' => 0, 'completed_elections' => 0];
    
} catch (Exception $e) {
    error_log('Database error in voter turnout: ' . $e->getMessage());
    $voterTurnoutData = [];
    $totalVoters = 0;
}

// Get flash message
$flash = [];
if (class_exists('Utils')) {
    $flash = Utils::getFlashMessage() ?? [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Turnout - UNZANASA Voting System</title>
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
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 2rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            display: block;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .voter-table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }
        
        .voter-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .voter-table th,
        .voter-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        .voter-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .voter-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .computer-number {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #667eea;
        }
        
        .vote-count {
            background: #e3f2fd;
            color: #1976d2;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .elections-list {
            font-size: 0.9rem;
            color: #666;
        }
        
        .no-data {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .no-data-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        @media print {
            .header, .btn, .no-print {
                display: none !important;
            }
            
            body {
                background: white !important;
            }
            
            .container {
                max-width: none;
                margin: 0;
                padding: 1rem;
            }
            
            .voter-table-container {
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .page-title::before {
                content: "UNZANASA Student Union - ";
            }
        }
    </style>
</head>
<body>
    <header class="header no-print">
        <h1>📊 Voter Turnout Report</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($admin['full_name'] ?? 'Admin'); ?></span>
            <a href="admin-dashboard.php" class="btn btn-primary">
                <span>🏠</span> Dashboard
            </a>
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
        
        <div class="page-header">
            <h1 class="page-title">
                <span>📊</span>
                Voter Turnout Report
            </h1>
            <button onclick="window.print()" class="btn btn-success no-print">
                <span>🖨️</span> Print Report
            </button>
        </div>
        
        <!-- Statistics Summary -->
        <div class="stats-summary">
            <div class="stat-card">
                <span class="stat-number"><?php echo number_format($totalVoters); ?></span>
                <div class="stat-label">Total Voters</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo number_format($electionStats['total_elections'] ?? 0); ?></span>
                <div class="stat-label">Total Elections</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo number_format($electionStats['active_elections'] ?? 0); ?></span>
                <div class="stat-label">Active Elections</div>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo number_format(array_sum(array_column($voterTurnoutData, 'votes_cast'))); ?></span>
                <div class="stat-label">Total Votes Cast</div>
            </div>
        </div>
        
        <!-- Voter Turnout Table -->
        <div class="voter-table-container">
            <div class="table-header">
                <h2 class="table-title">Computer Numbers That Have Voted</h2>
                <span class="no-print" style="color: #666; font-size: 0.9rem;">
                    Generated on <?php echo date('F j, Y \a\t g:i A'); ?>
                </span>
            </div>
            
            <?php if (!empty($voterTurnoutData)): ?>
                <table class="voter-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Computer Number</th>
                            <th>Votes Cast</th>
                            <th>First Vote</th>
                            <th>Last Vote</th>
                            <th>Elections Participated</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($voterTurnoutData as $index => $voter): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td class="computer-number"><?php echo htmlspecialchars($voter['computer_number']); ?></td>
                                <td>
                                    <span class="vote-count"><?php echo $voter['votes_cast']; ?> vote<?php echo $voter['votes_cast'] > 1 ? 's' : ''; ?></span>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($voter['first_vote_time'])); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($voter['last_vote_time'])); ?></td>
                                <td class="elections-list"><?php echo htmlspecialchars($voter['elections_voted']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <div class="no-data-icon">🗳️</div>
                    <h3>No Votes Cast Yet</h3>
                    <p>No computer numbers have voted in any elections yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Creative Designer Label -->
    <div class="no-print" style="position: fixed; bottom: 15px; right: 15px; z-index: 1000;">
        <div style="
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 11px;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        " 
        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.4)';" 
        onmouseout="this.style.transform='translateY(0px)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.3)';">
            <span style="font-size: 12px;">⚡</span>
            <span>Designed by <strong>Apollos Mwaba @cs</strong></span>
        </div>
    </div>
    
</body>
</html>
