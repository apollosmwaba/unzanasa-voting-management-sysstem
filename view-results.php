<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Require admin authentication
Auth::requireAuth();

// Get all elections
$electionModel = new Election();
$elections = $electionModel->getAllElections();

// Get selected election ID or check if showing all elections
$selectedElectionId = $_GET['election_id'] ?? null;
$showAllElections = isset($_GET['show_all']) && $_GET['show_all'] === '1';
$selectedElection = null;
$results = [];
$allElectionResults = [];

if ($showAllElections) {
    // Show results for all elections
    $candidateModel = new Candidate();
    $db = new Database();
    
    foreach ($elections as $election) {
        // Get candidates for this election
        $candidates = $candidateModel->getCandidatesByElection($election['id']);
        
        if (!empty($candidates)) {
            // Get vote counts for each candidate in this election
            $db->query('SELECT candidate_id, COUNT(*) as vote_count FROM votes WHERE election_id = :election_id GROUP BY candidate_id');
            $db->bind(':election_id', $election['id']);
            $voteCounts = $db->resultSet();
            
            $electionResults = [];
            
            // Prepare results data for this election
            foreach ($candidates as $candidate) {
                $voteCount = 0;
                foreach ($voteCounts as $vc) {
                    if ($vc['candidate_id'] == $candidate['id']) {
                        $voteCount = (int)$vc['vote_count'];
                        break;
                    }
                }
                
                $electionResults[] = [
                    'candidate' => $candidate,
                    'vote_count' => $voteCount,
                    'percentage' => 0 // Will be calculated after we have total votes
                ];
            }
            
            // Calculate percentages for this election
            $totalVotes = array_sum(array_column($electionResults, 'vote_count'));
            if ($totalVotes > 0) {
                foreach ($electionResults as &$result) {
                    $result['percentage'] = round(($result['vote_count'] / $totalVotes) * 100, 2);
                }
                unset($result); // Break the reference
            }
            
            // Sort results by vote count (descending)
            usort($electionResults, function($a, $b) {
                return $b['vote_count'] - $a['vote_count'];
            });
            
            $allElectionResults[] = [
                'election' => $election,
                'results' => $electionResults,
                'total_votes' => $totalVotes
            ];
        }
    }
} elseif ($selectedElectionId) {
    // Get the selected election
    $selectedElection = $electionModel->getElectionById($selectedElectionId);
    
    if ($selectedElection) {
        // Get candidates for this election
        $candidateModel = new Candidate();
        $candidates = $candidateModel->getCandidatesByElection($selectedElectionId);
        
        // Get vote counts for each candidate
        $db = new Database();
        $db->query('SELECT candidate_id, COUNT(*) as vote_count FROM votes WHERE election_id = :election_id GROUP BY candidate_id');
        $db->bind(':election_id', $selectedElectionId);
        $voteCounts = $db->resultSet();
        
        // Prepare results data
        foreach ($candidates as $candidate) {
            $voteCount = 0;
            foreach ($voteCounts as $vc) {
                if ($vc['candidate_id'] == $candidate['id']) {
                    $voteCount = (int)$vc['vote_count'];
                    break;
                }
            }
            
            $results[] = [
                'candidate' => $candidate,
                'vote_count' => $voteCount,
                'percentage' => 0 // Will be calculated after we have total votes
            ];
        }
        
        // Calculate percentages
        $totalVotes = array_sum(array_column($results, 'vote_count'));
        if ($totalVotes > 0) {
            foreach ($results as &$result) {
                $result['percentage'] = round(($result['vote_count'] / $totalVotes) * 100, 2);
            }
            unset($result); // Break the reference
        }
        
        // Sort results by vote count (descending)
        usort($results, function($a, $b) {
            return $b['vote_count'] - $a['vote_count'];
        });
    }
}

// Include the view
include __DIR__ . '/application/views/view-results.php';
?>
