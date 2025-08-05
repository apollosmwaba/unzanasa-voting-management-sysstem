<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Require admin authentication
Auth::requireAuth();

// Get parameters from URL
$electionId = $_GET['election_id'] ?? null;
$showAll = isset($_GET['show_all']) && $_GET['show_all'] === '1';
$format = $_GET['format'] ?? 'pdf'; // pdf or word

if (!$electionId && !$showAll) {
    Flash::set('error', 'No election selected for export.');
    header('Location: view-results.php');
    exit;
}

if ($showAll) {
    // Export all elections
    $electionModel = new Election();
    $elections = $electionModel->getAllElections();
    $allElectionResults = [];
    
    foreach ($elections as $election) {
        $candidateModel = new Candidate();
        $candidates = $candidateModel->getCandidatesByElection($election['id']);
        
        if (!empty($candidates)) {
            $db = new Database();
            $db->query('SELECT candidate_id, COUNT(*) as vote_count FROM votes WHERE election_id = :election_id GROUP BY candidate_id');
            $db->bind(':election_id', $election['id']);
            $voteCounts = $db->resultSet();
            
            $db->query('SELECT COUNT(*) as total_votes FROM votes WHERE election_id = :election_id');
            $db->bind(':election_id', $election['id']);
            $totalVotesResult = $db->single();
            $totalVotes = (int)$totalVotesResult['total_votes'];
            
            $results = [];
            foreach ($candidates as $candidate) {
                $voteCount = 0;
                foreach ($voteCounts as $vc) {
                    if ($vc['candidate_id'] == $candidate['id']) {
                        $voteCount = (int)$vc['vote_count'];
                        break;
                    }
                }
                
                $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 2) : 0;
                
                $results[] = [
                    'candidate' => $candidate,
                    'vote_count' => $voteCount,
                    'percentage' => $percentage
                ];
            }
            
            usort($results, function($a, $b) {
                return $b['vote_count'] - $a['vote_count'];
            });
            
            $allElectionResults[] = [
                'election' => $election,
                'results' => $results,
                'total_votes' => $totalVotes
            ];
        }
    }
    
    if ($format === 'word') {
        exportAllElectionsToWord($allElectionResults);
    } else {
        exportAllElectionsToPDF($allElectionResults);
    }
    
} else {
    // Export single election
    $electionModel = new Election();
    $election = $electionModel->getElectionById($electionId);
    
    if (!$election) {
        Flash::set('error', 'Election not found.');
        header('Location: view-results.php');
        exit;
    }
    
    // Get candidates and results for this election
    $candidateModel = new Candidate();
    $candidates = $candidateModel->getCandidatesByElection($electionId);
    
    $db = new Database();
    $db->query('SELECT candidate_id, COUNT(*) as vote_count FROM votes WHERE election_id = :election_id GROUP BY candidate_id');
    $db->bind(':election_id', $electionId);
    $voteCounts = $db->resultSet();
    
    // Get total votes for this election
    $db->query('SELECT COUNT(*) as total_votes FROM votes WHERE election_id = :election_id');
    $db->bind(':election_id', $electionId);
    $totalVotesResult = $db->single();
    $totalVotes = (int)$totalVotesResult['total_votes'];
    
    // Prepare results data
    $results = [];
    $winners = [];
    
    foreach ($candidates as $candidate) {
        $voteCount = 0;
        foreach ($voteCounts as $vc) {
            if ($vc['candidate_id'] == $candidate['id']) {
                $voteCount = (int)$vc['vote_count'];
                break;
            }
        }
        
        $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 2) : 0;
        
        $results[] = [
            'candidate' => $candidate,
            'vote_count' => $voteCount,
            'percentage' => $percentage
        ];
    }
    
    // Sort results by vote count (descending) to identify winners
    usort($results, function($a, $b) {
        return $b['vote_count'] - $a['vote_count'];
    });
    
    // Identify winners (candidates with the highest vote count)
    if (!empty($results)) {
        $highestVotes = $results[0]['vote_count'];
        foreach ($results as $result) {
            if ($result['vote_count'] == $highestVotes) {
                $winners[] = $result;
            } else {
                break;
            }
        }
    }
    
    // Export based on format
    if ($format === 'word') {
        exportToWord($election, $results, $winners, $totalVotes);
    } else {
        exportToPDF($election, $results, $winners, $totalVotes);
    }
}



function exportToPDF($election, $results, $winners, $totalVotes) {
    // Generate HTML content
    $html = generateResultsHTML($election, $results, $winners, $totalVotes);
    
    // Create a printable HTML page that opens in a new window
    $filename = 'Election_Results_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $election['title']) . '_' . date('Y-m-d') . '.html';
    
    // Set headers for HTML download that can be printed as PDF
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Election Results - {$election['title']}</title>
        <style>
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px; 
                line-height: 1.4;
            }
            .header { 
                text-align: center; 
                margin-bottom: 30px; 
                border-bottom: 2px solid #4e73df;
                padding-bottom: 20px;
            }
            .winner-badge { 
                background: #28a745; 
                color: white; 
                padding: 3px 8px; 
                border-radius: 4px; 
                font-size: 11px;
                font-weight: bold;
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 20px 0;
                font-size: 14px;
            }
            th, td { 
                border: 1px solid #ddd; 
                padding: 10px 8px; 
                text-align: left;
            }
            th { 
                background-color: #f8f9fa; 
                font-weight: bold;
                color: #495057;
            }
            .text-center { text-align: center; }
            .footer { 
                margin-top: 40px; 
                font-size: 12px; 
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 20px;
            }
            .print-instructions {
                background: #e3f2fd;
                border: 1px solid #2196f3;
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
            }
        </style>
        <script>
            function printDocument() {
                window.print();
            }
            
            function savePDF() {
                if (window.chrome) {
                    window.print();
                } else {
                    alert('To save as PDF: Press Ctrl+P or Cmd+P, then select Save as PDF as destination.');
                    window.print();
                }
            }
        </script>
    </head>
    <body>
        <div class='print-instructions no-print'>
            <h4>📄 How to Save as PDF:</h4>
            <p><strong>Method 1:</strong> Press <kbd>Ctrl+P</kbd> (or <kbd>Cmd+P</kbd> on Mac), then select 'Save as PDF' as destination.</p>
            <p><strong>Method 2:</strong> <button onclick='savePDF()' style='padding: 5px 10px; background: #4e73df; color: white; border: none; border-radius: 3px; cursor: pointer;'>Click here to Print/Save as PDF</button></p>
        </div>
        
        $html
        
        <div class='no-print' style='text-align: center; margin: 30px 0;'>
            <button onclick='printDocument()' style='padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>
                🖨️ Print Document
            </button>
        </div>
    </body>
    </html>";
}

function exportToWord($election, $results, $winners, $totalVotes) {
    // Set headers for Word document download
    header('Content-Type: application/vnd.ms-word');
    header('Content-Disposition: attachment; filename="Election_Results_' . date('Y-m-d') . '.doc"');
    
    $html = generateResultsHTML($election, $results, $winners, $totalVotes);
    
    echo "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Election Results - {$election['title']}</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .winner-badge { background: #28a745; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .text-center { text-align: center; }
            .footer { margin-top: 30px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        $html
    </body>
    </html>";
}

function generateResultsHTML($election, $results, $winners, $totalVotes) {
    $html = "
    <div class='header'>
        <h1>UNZANASA VOTING SYSTEM</h1>
        <h2>Election Results Report</h2>
        <h3>{$election['title']}</h3>
        <p><strong>Election Period:</strong> " . date('F j, Y', strtotime($election['start_date'])) . " - " . date('F j, Y', strtotime($election['end_date'])) . "</p>
        <p><strong>Total Votes Cast:</strong> $totalVotes</p>
        <p><strong>Report Generated:</strong> " . date('F j, Y g:i A') . "</p>
    </div>
    
    <h3>🏆 Election Winners</h3>";
    
    if (!empty($winners)) {
        $html .= "<table>
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Position</th>
                    <th>Votes</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach ($winners as $winner) {
            $html .= "
                <tr>
                    <td><strong>{$winner['candidate']['name']}</strong> <span class='winner-badge'>WINNER</span></td>
                    <td>{$winner['candidate']['position_name']}</td>
                    <td class='text-center'>{$winner['vote_count']}</td>
                    <td class='text-center'>{$winner['percentage']}%</td>
                </tr>";
        }
        
        $html .= "</tbody></table>";
    } else {
        $html .= "<p>No winners determined.</p>";
    }
    
    $html .= "
    <h3>📊 Complete Results</h3>
    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>Candidate</th>
                <th>Position</th>
                <th>Votes</th>
                <th>Percentage</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>";
    
    $rank = 1;
    foreach ($results as $result) {
        $isWinner = false;
        foreach ($winners as $winner) {
            if ($winner['candidate']['id'] == $result['candidate']['id']) {
                $isWinner = true;
                break;
            }
        }
        
        $status = $isWinner ? "<span class='winner-badge'>WINNER</span>" : "";
        
        $html .= "
            <tr>
                <td class='text-center'>$rank</td>
                <td>{$result['candidate']['name']}</td>
                <td>{$result['candidate']['position_name']}</td>
                <td class='text-center'>{$result['vote_count']}</td>
                <td class='text-center'>{$result['percentage']}%</td>
                <td class='text-center'>$status</td>
            </tr>";
        $rank++;
    }
    
    $html .= "
        </tbody>
    </table>
    
    <div class='footer'>
        <p><strong>UNZANASA Student Union Voting System</strong></p>
        <p>This report was automatically generated on " . date('F j, Y \a\t g:i A') . "</p>
        <p>For questions or concerns, please contact the Election Commission.</p>
    </div>";
    
    return $html;
}

function exportAllElectionsToPDF($allElectionResults) {
    // Generate HTML content
    $html = generateAllElectionsHTML($allElectionResults);
    
    // Create a printable HTML page
    $filename = 'All_Election_Results_' . date('Y-m-d') . '.html';
    
    // Set headers for HTML download that can be printed as PDF
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>All Election Results - UNZANASA</title>
        <style>
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
            body { 
                font-family: Arial, sans-serif; 
                margin: 20px; 
                line-height: 1.4;
            }
            .header { 
                text-align: center; 
                margin-bottom: 30px;
                border-bottom: 2px solid #4e73df;
                padding-bottom: 20px;
            }
            .election-section { 
                margin-bottom: 40px; 
                page-break-inside: avoid;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 30px;
            }
            .election-title { 
                background: #4e73df; 
                color: white; 
                padding: 15px; 
                margin: -20px -20px 20px -20px;
                border-radius: 8px 8px 0 0;
            }
            .winner-badge { 
                background: #28a745; 
                color: white; 
                padding: 3px 8px; 
                border-radius: 4px; 
                font-size: 11px;
                font-weight: bold;
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 20px 0;
                font-size: 14px;
            }
            th, td { 
                border: 1px solid #ddd; 
                padding: 10px 8px; 
                text-align: left;
            }
            th { 
                background-color: #f8f9fa; 
                font-weight: bold;
                color: #495057;
            }
            .text-center { text-align: center; }
            .footer { 
                margin-top: 40px; 
                font-size: 12px; 
                color: #666;
                border-top: 1px solid #ddd;
                padding-top: 20px;
            }
            .print-instructions {
                background: #e3f2fd;
                border: 1px solid #2196f3;
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
            }
        </style>
        <script>
            function printDocument() {
                window.print();
            }
        </script>
    </head>
    <body>
        <div class='print-instructions no-print'>
            <h4>📄 How to Save as PDF:</h4>
            <p><strong>Method 1:</strong> Press <kbd>Ctrl+P</kbd> (or <kbd>Cmd+P</kbd> on Mac), then select 'Save as PDF' as destination.</p>
            <p><strong>Method 2:</strong> <button onclick='printDocument()' style='padding: 5px 10px; background: #4e73df; color: white; border: none; border-radius: 3px; cursor: pointer;'>Click here to Print/Save as PDF</button></p>
        </div>
        
        $html
        
        <div class='no-print' style='text-align: center; margin: 30px 0;'>
            <button onclick='printDocument()' style='padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;'>
                🖨️ Print Document
            </button>
        </div>
    </body>
    </html>";
}

function exportAllElectionsToWord($allElectionResults) {
    // Set headers for Word document download
    header('Content-Type: application/vnd.ms-word');
    header('Content-Disposition: attachment; filename="All_Election_Results_' . date('Y-m-d') . '.doc"');
    
    $html = generateAllElectionsHTML($allElectionResults);
    
    echo "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>All Election Results - UNZANASA</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .election-section { margin-bottom: 40px; }
            .election-title { background: #4e73df; color: white; padding: 10px; margin-bottom: 20px; }
            .winner-badge { background: #28a745; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .text-center { text-align: center; }
            .footer { margin-top: 30px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        $html
    </body>
    </html>";
}

function generateAllElectionsHTML($allElectionResults) {
    $html = "
    <div class='header'>
        <h1>UNZANASA VOTING SYSTEM</h1>
        <h2>Complete Election Results Report</h2>
        <p><strong>All Elections Summary</strong></p>
        <p><strong>Report Generated:</strong> " . date('F j, Y g:i A') . "</p>
    </div>";
    
    if (empty($allElectionResults)) {
        $html .= "<p>No election results available.</p>";
    } else {
        foreach ($allElectionResults as $electionData) {
            $election = $electionData['election'];
            $results = $electionData['results'];
            $totalVotes = $electionData['total_votes'];
            
            $html .= "
            <div class='election-section'>
                <div class='election-title'>
                    <h3>{$election['title']}</h3>
                    <p>" . date('F j, Y', strtotime($election['start_date'])) . " - " . date('F j, Y', strtotime($election['end_date'])) . "</p>
                    <p>Total Votes: $totalVotes</p>
                </div>";
            
            if (!empty($results)) {
                // Find winners
                $highestVotes = $results[0]['vote_count'];
                $winners = [];
                foreach ($results as $result) {
                    if ($result['vote_count'] == $highestVotes && $highestVotes > 0) {
                        $winners[] = $result;
                    } else {
                        break;
                    }
                }
                
                $html .= "
                <h4>🏆 Winners</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Position</th>
                            <th>Votes</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>";
                
                foreach ($winners as $winner) {
                    $html .= "
                        <tr>
                            <td><strong>{$winner['candidate']['name']}</strong> <span class='winner-badge'>WINNER</span></td>
                            <td>{$winner['candidate']['position_name']}</td>
                            <td class='text-center'>{$winner['vote_count']}</td>
                            <td class='text-center'>{$winner['percentage']}%</td>
                        </tr>";
                }
                
                $html .= "
                    </tbody>
                </table>
                
                <h4>📊 Complete Results</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Candidate</th>
                            <th>Position</th>
                            <th>Votes</th>
                            <th>Percentage</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>";
                
                $rank = 1;
                foreach ($results as $result) {
                    $isWinner = false;
                    foreach ($winners as $winner) {
                        if ($winner['candidate']['id'] == $result['candidate']['id']) {
                            $isWinner = true;
                            break;
                        }
                    }
                    
                    $status = $isWinner ? "<span class='winner-badge'>WINNER</span>" : "";
                    
                    $html .= "
                        <tr>
                            <td class='text-center'>$rank</td>
                            <td>{$result['candidate']['name']}</td>
                            <td>{$result['candidate']['position_name']}</td>
                            <td class='text-center'>{$result['vote_count']}</td>
                            <td class='text-center'>{$result['percentage']}%</td>
                            <td class='text-center'>$status</td>
                        </tr>";
                    $rank++;
                }
                
                $html .= "</tbody></table>";
            } else {
                $html .= "<p>No results available for this election.</p>";
            }
            
            $html .= "</div>";
        }
    }
    
    $html .= "
    <div class='footer'>
        <p><strong>UNZANASA Student Union Voting System</strong></p>
        <p>This comprehensive report was automatically generated on " . date('F j, Y \a\t g:i A') . "</p>
        <p>For questions or concerns, please contact the Election Commission.</p>
    </div>";
    
    return $html;
}
?>
