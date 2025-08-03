<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Require admin authentication
Auth::requireAuth();

// Get all elections
$electionModel = new Election();
$elections = $electionModel->getAllElections();

// Get selected election ID
$selectedElectionId = $_GET['election_id'] ?? null;
$selectedElection = null;
$results = [];

if ($selectedElectionId) {
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
                if ($vc->candidate_id == $candidate->id) {
                    $voteCount = (int)$vc->vote_count;
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
