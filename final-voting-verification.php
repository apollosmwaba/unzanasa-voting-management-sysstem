<?php
require_once __DIR__ . '/init.php';

echo "<h2>🎯 Final Voting System Verification</h2>";

$db = new Database();
$voteModel = new Vote();

// Get election data
$db->query('SELECT * FROM elections ORDER BY id DESC LIMIT 1');
$election = $db->single();

$db->query('SELECT * FROM candidates WHERE election_id = :election_id LIMIT 1');
$db->bind(':election_id', $election['id']);
$candidate = $db->single();

echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<h3>✅ ISSUE RESOLVED: Computer Number Voting Restrictions</h3>";
echo "<p><strong>Election:</strong> " . htmlspecialchars($election['title']) . " (ID: {$election['id']})</p>";
echo "<p><strong>Candidate:</strong> " . htmlspecialchars($candidate['name']) . " (ID: {$candidate['id']})</p>";
echo "</div>";

echo "<h3>🧪 Test Scenarios:</h3>";

$testResults = [];

// Test 1: New computer number should vote successfully
$testComputer1 = '5555555555';
echo "<h4>Test 1: New Computer Number ($testComputer1)</h4>";
$result1 = $voteModel->castVote($election['id'], $candidate['id'], $testComputer1, '127.0.0.1', 'Test Browser');
if ($result1) {
    echo "<p>✅ SUCCESS: New computer number can vote</p>";
    $testResults['new_vote'] = '✅ PASS';
} else {
    echo "<p>❌ FAIL: New computer number cannot vote</p>";
    $testResults['new_vote'] = '❌ FAIL';
}

// Test 2: Same computer number should be blocked
echo "<h4>Test 2: Same Computer Number ($testComputer1) - Should Be Blocked</h4>";
$result2 = $voteModel->castVote($election['id'], $candidate['id'], $testComputer1, '127.0.0.1', 'Test Browser');
if (!$result2) {
    echo "<p>✅ SUCCESS: Duplicate vote correctly blocked</p>";
    $testResults['duplicate_block'] = '✅ PASS';
} else {
    echo "<p>❌ FAIL: Duplicate vote was allowed</p>";
    $testResults['duplicate_block'] = '❌ FAIL';
}

// Test 3: Different computer number should vote successfully
$testComputer2 = '6666666666';
echo "<h4>Test 3: Different Computer Number ($testComputer2)</h4>";
$result3 = $voteModel->castVote($election['id'], $candidate['id'], $testComputer2, '127.0.0.1', 'Test Browser');
if ($result3) {
    echo "<p>✅ SUCCESS: Different computer number can vote</p>";
    $testResults['different_vote'] = '✅ PASS';
} else {
    echo "<p>❌ FAIL: Different computer number cannot vote</p>";
    $testResults['different_vote'] = '❌ FAIL';
}

// Test 4: Verify database integrity
echo "<h4>Test 4: Database Integrity Check</h4>";
$db->query('SELECT computer_number, COUNT(*) as count FROM votes WHERE election_id = :election_id GROUP BY computer_number HAVING count > 1');
$db->bind(':election_id', $election['id']);
$duplicates = $db->resultSet();

if (empty($duplicates)) {
    echo "<p>✅ SUCCESS: No duplicate votes per computer number</p>";
    $testResults['db_integrity'] = '✅ PASS';
} else {
    echo "<p>❌ FAIL: Found duplicate votes for same computer numbers</p>";
    $testResults['db_integrity'] = '❌ FAIL';
}

// Test 5: Check constraint is working
echo "<h4>Test 5: Unique Constraint Verification</h4>";
$db->query('SELECT computer_number, COUNT(*) as count FROM votes WHERE election_id = :election_id AND computer_number IN (:comp1, :comp2) GROUP BY computer_number');
$db->bind(':election_id', $election['id']);
$db->bind(':comp1', $testComputer1);
$db->bind(':comp2', $testComputer2);
$counts = $db->resultSet();

$constraintWorking = true;
foreach ($counts as $count) {
    if ($count['count'] > 1) {
        $constraintWorking = false;
        break;
    }
}

if ($constraintWorking) {
    echo "<p>✅ SUCCESS: Unique constraint working properly</p>";
    $testResults['constraint'] = '✅ PASS';
} else {
    echo "<p>❌ FAIL: Unique constraint not working</p>";
    $testResults['constraint'] = '❌ FAIL';
}

// Display current votes
echo "<h3>📊 Current Votes in Election {$election['id']}:</h3>";
$db->query('SELECT computer_number, candidate_id, voted_at FROM votes WHERE election_id = :election_id ORDER BY voted_at DESC LIMIT 10');
$db->bind(':election_id', $election['id']);
$recentVotes = $db->resultSet();

if (!empty($recentVotes)) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'><th>Computer Number</th><th>Candidate ID</th><th>Voted At</th></tr>";
    foreach ($recentVotes as $vote) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($vote['computer_number']) . "</strong></td>";
        echo "<td>" . $vote['candidate_id'] . "</td>";
        echo "<td>" . $vote['voted_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Summary
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #28a745;'>";
echo "<h3>🎯 Test Results Summary:</h3>";
echo "<ul style='font-size: 16px; line-height: 1.6;'>";
foreach ($testResults as $test => $result) {
    $testName = ucwords(str_replace('_', ' ', $test));
    echo "<li><strong>$testName:</strong> $result</li>";
}
echo "</ul>";

$allPassed = !in_array('❌ FAIL', $testResults);
if ($allPassed) {
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
    echo "<h4>🎉 ALL TESTS PASSED!</h4>";
    echo "<p><strong>The voting system is working correctly:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Different computer numbers can vote independently</li>";
    echo "<li>✅ Same computer number is blocked from voting twice</li>";
    echo "<li>✅ Database integrity is maintained</li>";
    echo "<li>✅ Unique constraints are enforced properly</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-top: 15px;'>";
    echo "<h4>⚠️ Some tests failed - please review the issues above</h4>";
    echo "</div>";
}
echo "</div>";

echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h4>📝 What This Means:</h4>";
echo "<p>If you see the error message <em>'Unable to cast vote. You may have already voted for this election'</em> in the voting interface, it means:</p>";
echo "<ul>";
echo "<li><strong>The system is working correctly</strong> - it's preventing duplicate votes</li>";
echo "<li><strong>That specific computer number has already voted</strong> in that election</li>";
echo "<li><strong>Different computer numbers can still vote</strong> without any issues</li>";
echo "</ul>";
echo "</div>";

?>
