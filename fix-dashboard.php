<?php
// Complete dashboard fix script
require_once __DIR__ . '/init.php';

echo "<h2>Dashboard Fix Script</h2>";

try {
    $db = new Database();
    
    echo "<h3>Step 1: Verify Database Structure</h3>";
    
    // Check if elections table exists and has data
    $db->query("SELECT COUNT(*) as count FROM elections");
    $result = $db->single();
    $electionCount = $result['count'] ?? 0;
    echo "<p>Current elections in database: $electionCount</p>";
    
    if ($electionCount == 0) {
        echo "<h3>Step 2: Creating Test Data</h3>";
        
        // Create a test election
        $db->query("INSERT INTO elections (title, description, start_date, end_date, status, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $db->bind(1, 'Student Union Elections 2025');
        $db->bind(2, 'Annual student union leadership elections');
        $db->bind(3, date('Y-m-d H:i:s')); // Start now
        $db->bind(4, date('Y-m-d H:i:s', strtotime('+7 days'))); // End in 7 days
        $db->bind(5, 'active');
        $db->bind(6, 1); // Admin ID
        
        if ($db->execute()) {
            $electionId = $db->lastInsertId();
            echo "<p>✓ Created test election with ID: $electionId</p>";
            
            // Create test positions
            $positions = [
                ['President', 'Student Union President'],
                ['Vice President', 'Student Union Vice President'],
                ['Secretary', 'Student Union Secretary']
            ];
            
            foreach ($positions as $index => $pos) {
                $db->query("INSERT INTO positions (election_id, title, description, max_vote, display_order) VALUES (?, ?, ?, ?, ?)");
                $db->bind(1, $electionId);
                $db->bind(2, $pos[0]);
                $db->bind(3, $pos[1]);
                $db->bind(4, 1);
                $db->bind(5, $index + 1);
                
                if ($db->execute()) {
                    $positionId = $db->lastInsertId();
                    echo "<p>✓ Created position: {$pos[0]} (ID: $positionId)</p>";
                    
                    // Create test candidates for each position
                    $candidates = [
                        ['John Doe', 'Making student life better'],
                        ['Jane Smith', 'Advocating for student rights']
                    ];
                    
                    foreach ($candidates as $candidate) {
                        $db->query("INSERT INTO candidates (name, position_id, election_id, platform, status) VALUES (?, ?, ?, ?, ?)");
                        $db->bind(1, $candidate[0]);
                        $db->bind(2, $positionId);
                        $db->bind(3, $electionId);
                        $db->bind(4, $candidate[1]);
                        $db->bind(5, 1); // Active
                        
                        if ($db->execute()) {
                            echo "<p>  ✓ Added candidate: {$candidate[0]}</p>";
                        }
                    }
                }
            }
        }
    }
    
    echo "<h3>Step 3: Testing Dashboard Data Retrieval</h3>";
    
    // Test Election model
    $electionModel = new Election();
    $electionStats = $electionModel->getElectionStats();
    echo "<p>Election Stats:</p>";
    echo "<ul>";
    echo "<li>Total Elections: " . ($electionStats['total_elections'] ?? 0) . "</li>";
    echo "<li>Active Elections: " . ($electionStats['active_elections'] ?? 0) . "</li>";
    echo "<li>Completed Elections: " . ($electionStats['completed_elections'] ?? 0) . "</li>";
    echo "</ul>";
    
    // Test vote and candidate counts
    $db->query('SELECT COUNT(*) as count FROM candidates');
    $result = $db->single();
    $candidateCount = $result['count'] ?? 0;
    echo "<p>Total Candidates: $candidateCount</p>";
    
    $db->query('SELECT COUNT(*) as count FROM votes');
    $result = $db->single();
    $voteCount = $result['count'] ?? 0;
    echo "<p>Total Votes: $voteCount</p>";
    
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin-top: 20px;'>";
    echo "<h3>✅ Dashboard Fix Complete!</h3>";
    echo "<p>The dashboard should now display proper statistics.</p>";
    echo "<p><a href='admin-dashboard.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Dashboard</a></p>";
    echo "<p><a href='debug-dashboard.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Debug Dashboard</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
?>
