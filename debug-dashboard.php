<?php
// Debug script to test dashboard data retrieval
require_once __DIR__ . '/init.php';

echo "<h2>Dashboard Debug Information</h2>";

try {
    $db = new Database();
    
    echo "<h3>Database Connection Test:</h3>";
    echo "<p>✓ Database connection successful</p>";
    
    // Test Election model
    echo "<h3>Election Statistics:</h3>";
    $electionModel = new Election();
    $electionStats = $electionModel->getElectionStats();
    echo "<pre>";
    print_r($electionStats);
    echo "</pre>";
    
    // Test direct queries
    echo "<h3>Direct Database Queries:</h3>";
    
    // Total elections
    $db->query('SELECT COUNT(*) as count FROM elections');
    $result = $db->single();
    echo "<p>Total Elections: " . ($result['count'] ?? 'ERROR') . "</p>";
    
    // Active elections
    $db->query('SELECT COUNT(*) as count FROM elections WHERE start_date <= NOW() AND end_date >= NOW()');
    $result = $db->single();
    echo "<p>Active Elections: " . ($result['count'] ?? 'ERROR') . "</p>";
    
    // Total candidates
    $db->query('SELECT COUNT(*) as count FROM candidates');
    $result = $db->single();
    echo "<p>Total Candidates: " . ($result['count'] ?? 'ERROR') . "</p>";
    
    // Total votes
    $db->query('SELECT COUNT(*) as count FROM votes');
    $result = $db->single();
    echo "<p>Total Votes: " . ($result['count'] ?? 'ERROR') . "</p>";
    
    // Show actual elections
    echo "<h3>Actual Elections in Database:</h3>";
    $db->query('SELECT * FROM elections');
    $elections = $db->resultSet();
    if (empty($elections)) {
        echo "<p>❌ No elections found in database</p>";
        echo "<p><a href='setup-test-data.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Setup Test Data</a></p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Start Date</th><th>End Date</th></tr>";
        foreach ($elections as $election) {
            echo "<tr>";
            echo "<td>" . $election['id'] . "</td>";
            echo "<td>" . $election['title'] . "</td>";
            echo "<td>" . $election['status'] . "</td>";
            echo "<td>" . $election['start_date'] . "</td>";
            echo "<td>" . $election['end_date'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Show actual candidates
    echo "<h3>Actual Candidates in Database:</h3>";
    $db->query('SELECT * FROM candidates');
    $candidates = $db->resultSet();
    if (empty($candidates)) {
        echo "<p>❌ No candidates found in database</p>";
    } else {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Position ID</th><th>Election ID</th></tr>";
        foreach ($candidates as $candidate) {
            echo "<tr>";
            echo "<td>" . $candidate['id'] . "</td>";
            echo "<td>" . $candidate['name'] . "</td>";
            echo "<td>" . $candidate['position_id'] . "</td>";
            echo "<td>" . $candidate['election_id'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
?>
