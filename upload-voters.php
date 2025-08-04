<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Require admin authentication
Auth::requireAuth();

// Initialize variables
$message = '';
$messageType = '';
$elections = [];
$selectedElection = null;
$turnoutData = [];
$turnoutStats = [];

// Get all elections for the dropdown
$electionModel = new Election();
try {
    $elections = $electionModel->getAllElections();
} catch (Exception $e) {
    $message = 'Error loading elections: ' . $e->getMessage();
    $messageType = 'danger';
    error_log($message);
}

// Handle election selection for turnout report
if (isset($_GET['election_id']) && !empty($_GET['election_id'])) {
    $electionId = (int)$_GET['election_id'];
    
    try {
        // Get election details
        $selectedElection = $electionModel->getElectionById($electionId);
        
        if ($selectedElection) {
            $db = new Database();
            
            // Get turnout data - voters who have voted (with computer numbers)
            $db->query("
                SELECT DISTINCT 
                    vr.voter_id as computer_number,
                    v.voted_at,
                    DATE_FORMAT(v.voted_at, '%M %d, %Y at %h:%i %p') as formatted_time,
                    HOUR(v.voted_at) as vote_hour
                FROM votes v 
                INNER JOIN voters vr ON v.voter_id = vr.id 
                WHERE v.election_id = :election_id 
                ORDER BY v.voted_at DESC
            ");
            $db->bind(':election_id', $electionId);
            $turnoutData = $db->resultSet();
            
            // Calculate turnout statistics
            $totalVotes = count($turnoutData);
            
            // Get total registered voters for this election (from valid_computer_numbers)
            $db->query("SELECT COUNT(*) as total FROM valid_computer_numbers WHERE is_active = 1");
            $totalRegistered = $db->single()['total'] ?? 0;
            
            // Calculate turnout percentage
            $turnoutPercentage = $totalRegistered > 0 ? round(($totalVotes / $totalRegistered) * 100, 2) : 0;
            
            // Get hourly voting distribution
            $hourlyVotes = [];
            foreach ($turnoutData as $vote) {
                $hour = $vote['vote_hour'];
                $hourlyVotes[$hour] = ($hourlyVotes[$hour] ?? 0) + 1;
            }
            
            // Get peak voting hour
            $peakHour = 0;
            $peakVotes = 0;
            foreach ($hourlyVotes as $hour => $votes) {
                if ($votes > $peakVotes) {
                    $peakVotes = $votes;
                    $peakHour = $hour;
                }
            }
            
            // Get voting timeline (votes per day)
            $db->query("
                SELECT 
                    DATE(v.voted_at) as vote_date,
                    COUNT(*) as daily_votes
                FROM votes v 
                WHERE v.election_id = :election_id 
                GROUP BY DATE(v.voted_at)
                ORDER BY vote_date ASC
            ");
            $db->bind(':election_id', $electionId);
            $dailyVotes = $db->resultSet();
            
            $turnoutStats = [
                'total_votes' => $totalVotes,
                'total_registered' => $totalRegistered,
                'turnout_percentage' => $turnoutPercentage,
                'peak_hour' => $peakHour,
                'peak_votes' => $peakVotes,
                'hourly_votes' => $hourlyVotes,
                'daily_votes' => $dailyVotes
            ];
        }
    } catch (Exception $e) {
        $message = 'Error loading turnout data: ' . $e->getMessage();
        $messageType = 'danger';
        error_log($message);
    }
}

// Include the view
include __DIR__ . '/application/views/voter-turnout.php';
?>
