<?php
require_once __DIR__ . '/init.php';

echo "<h2>Debug Candidate Data Structure</h2>";

try {
    $electionModel = new Election();
    $elections = $electionModel->getAllElections();
    
    if (!empty($elections)) {
        $election = $elections[0];
        echo "<p>Testing with election: " . htmlspecialchars($election['title']) . " (ID: {$election['id']})</p>";
        
        $candidateModel = new Candidate();
        $candidates = $candidateModel->getCandidatesByElection($election['id']);
        
        if (!empty($candidates)) {
            echo "<h3>Candidate Data Structure:</h3>";
            echo "<pre>";
            print_r($candidates[0]); // Show structure of first candidate
            echo "</pre>";
            
            echo "<h3>All Candidate Fields:</h3>";
            echo "<ul>";
            foreach (array_keys($candidates[0]) as $field) {
                echo "<li><strong>$field:</strong> " . htmlspecialchars($candidates[0][$field] ?? 'NULL') . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No candidates found</p>";
        }
    } else {
        echo "<p>No elections found</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
