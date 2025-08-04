<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Require admin authentication
Auth::requireAuth();

// Initialize all required variables with default values
$electionModel = new Election();
$electionStats = [
    'total_elections' => 0,
    'active_elections' => 0,
    'completed_elections' => 0
];
$totalVotes = 0;
$totalCandidates = 0;
$recentActivity = [];

// Get flash message
$flash = [];
if (class_exists('Utils')) {
    $flash = Utils::getFlashMessage() ?? [];
}

// Get admin user data
$admin = Auth::user() ?? ['full_name' => 'Administrator'];

try {
    // Get election statistics
    $electionStats = $electionModel->getElectionStats() ?? $electionStats;
    
    // Get total votes
    $db = new Database();
    $db->query('SELECT COUNT(*) as total_votes FROM votes');
    $result = $db->single();
    $totalVotes = $result['total_votes'] ?? 0;
    
    // Get total candidates
    $db->query('SELECT COUNT(*) as total_candidates FROM candidates');
    $result = $db->single();
    $totalCandidates = $result['total_candidates'] ?? 0;
    
    // Get recent activity (last 5 votes)
    $db->query('SELECT v.*, e.title as election_name, c.name as candidate_name 
               FROM votes v 
               JOIN elections e ON v.election_id = e.id 
               JOIN candidates c ON v.candidate_id = c.id 
               ORDER BY v.voted_at DESC LIMIT 5');
    $recentActivity = $db->resultSet() ?? [];
    
} catch (Exception $e) {
    error_log('Database error in admin dashboard: ' . $e->getMessage());
    // Use default values already set above
}

// Include the dashboard view
include __DIR__ . '/application/views/admin-dashboard.php';
