<?php
// Include initialization
require_once __DIR__ . '/init.php';

echo "<h2>Setting up test data...</h2>";

try {
    $db = new Database();
    
    // Add some test computer numbers
    $testNumbers = [
        '1234567890',
        '2345678901',
        '3456789012',
        '4567890123',
        '5678901234'
    ];
    
    echo "<h3>Adding test computer numbers:</h3>";
    foreach ($testNumbers as $number) {
        $db->query("INSERT IGNORE INTO valid_computer_numbers (computer_number, student_name, is_active, uploaded_by) VALUES (:number, :name, 1, 1)");
        $db->bind(':number', $number);
        $db->bind(':name', 'Test Student ' . substr($number, -4));
        
        if ($db->execute()) {
            echo "<p>✓ Added computer number: $number</p>";
        }
    }
    
    // Check if we have active elections
    $db->query("SELECT COUNT(*) as count FROM elections WHERE status = 'active'");
    $result = $db->single();
    
    if ($result['count'] == 0) {
        echo "<h3>Creating test election:</h3>";
        
        // Create a test election
        $db->query("INSERT INTO elections (title, name, description, start_date, end_date, status, created_by) VALUES (:title, :name, :desc, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'active', 1)");
        $db->bind(':title', 'Student Union Elections 2025');
        $db->bind(':name', 'Student Union Elections 2025');
        $db->bind(':desc', 'Annual student union leadership elections');
        
        if ($db->execute()) {
            $electionId = $db->lastInsertId();
            echo "<p>✓ Created test election with ID: $electionId</p>";
            
            // Create test positions
            $positions = [
                ['name' => 'President', 'desc' => 'Student Union President', 'order' => 1],
                ['name' => 'Vice President', 'desc' => 'Student Union Vice President', 'order' => 2],
                ['name' => 'Secretary', 'desc' => 'Student Union Secretary', 'order' => 3]
            ];
            
            echo "<h3>Creating test positions:</h3>";
            foreach ($positions as $pos) {
                $db->query("INSERT INTO positions (election_id, title, name, description, max_vote, display_order) VALUES (:election_id, :title, :name, :desc, 1, :order)");
                $db->bind(':election_id', $electionId);
                $db->bind(':title', $pos['name']);
                $db->bind(':name', $pos['name']);
                $db->bind(':desc', $pos['desc']);
                $db->bind(':order', $pos['order']);
                
                if ($db->execute()) {
                    $positionId = $db->lastInsertId();
                    echo "<p>✓ Created position: {$pos['name']} (ID: $positionId)</p>";
                    
                    // Create test candidates for each position
                    $candidates = [
                        ['fname' => 'John', 'lname' => 'Doe', 'platform' => 'Making student life better with innovative programs and transparent leadership.'],
                        ['fname' => 'Jane', 'lname' => 'Smith', 'platform' => 'Advocating for student rights and improving campus facilities.']
                    ];
                    
                    foreach ($candidates as $candidate) {
                        $db->query("INSERT INTO candidates (firstname, lastname, name, position_id, election_id, platform, status) VALUES (:fname, :lname, :name, :pos_id, :election_id, :platform, 1)");
                        $db->bind(':fname', $candidate['fname']);
                        $db->bind(':lname', $candidate['lname']);
                        $db->bind(':name', $candidate['fname'] . ' ' . $candidate['lname']);
                        $db->bind(':pos_id', $positionId);
                        $db->bind(':election_id', $electionId);
                        $db->bind(':platform', $candidate['platform']);
                        
                        if ($db->execute()) {
                            echo "<p>  ✓ Added candidate: {$candidate['fname']} {$candidate['lname']}</p>";
                        }
                    }
                }
            }
        }
    } else {
        echo "<p>Active elections already exist: {$result['count']}</p>";
    }
    
    echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin-top: 20px;'>";
    echo "<h3>✅ Test Data Setup Complete!</h3>";
    echo "<p><strong>You can now test the voting system with these computer numbers:</strong></p>";
    echo "<ul>";
    foreach ($testNumbers as $number) {
        echo "<li>$number</li>";
    }
    echo "</ul>";
    echo "<p><a href='vote.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Voting Page</a></p>";
    echo "<p><a href='admin-login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Admin Login</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
