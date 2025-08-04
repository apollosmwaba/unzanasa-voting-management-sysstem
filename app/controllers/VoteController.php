<?php
require_once __DIR__ . '/../../router.php';
require_once __DIR__ . '/../../init.php';

class VoteController extends BaseController {
    private $voteModel;
    private $electionModel;
    private $candidateModel;
    
    public function __construct() {
        $this->voteModel = new Vote();
        $this->electionModel = new Election();
        $this->candidateModel = new Candidate();
    }
    
    public function index() {
        $error = null;
        $success = null;
        $elections = [];
        $voterVerified = false;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'verify') {
                $computerNumber = trim($_POST['computer_number'] ?? '');
                
                if (empty($computerNumber) || !Utils::validateComputerNumber($computerNumber)) {
                    $error = 'Please enter a valid computer number.';
                } else {
                    if ($this->voteModel->isValidComputerNumber($computerNumber)) {
                        $_SESSION['voter_computer_number'] = $computerNumber;
                        $voterVerified = true;
                        $elections = $this->electionModel->getActiveElections();
                        
                        if (empty($elections)) {
                            $error = 'No active elections at the moment.';
                        }
                    } else {
                        $error = 'Invalid computer number. Please contact the administrator.';
                    }
                }
            } elseif ($action === 'vote') {
                $computerNumber = $_SESSION['voter_computer_number'] ?? '';
                $electionId = (int)($_POST['election_id'] ?? 0);
                $candidateId = (int)($_POST['candidate_id'] ?? 0);
                
                if (empty($computerNumber)) {
                    $error = 'Session expired. Please verify your computer number again.';
                } elseif ($electionId <= 0 || $candidateId <= 0) {
                    $error = 'Invalid election or candidate selection.';
                } else {
                    // Check if already voted
                    if ($this->voteModel->hasVoted($computerNumber, $electionId)) {
                        $error = 'You have already voted in this election.';
                    } else {
                        $result = $this->voteModel->castVote(
                            $electionId,
                            $candidateId,
                            $computerNumber,
                            Utils::getClientIP(),
                            $_SERVER['HTTP_USER_AGENT'] ?? null
                        );
                        
                        if ($result) {
                            $success = 'Your vote has been cast successfully!';
                            unset($_SESSION['voter_computer_number']);
                            $voterVerified = false;
                        } else {
                            $error = 'Unable to cast vote. Please try again or contact administrator.';
                        }
                    }
                }
                
                if ($voterVerified) {
                    $elections = $this->electionModel->getActiveElections();
                }
            }
        } else {
            // Check if voter is already verified
            if (isset($_SESSION['voter_computer_number'])) {
                $voterVerified = true;
                $elections = $this->electionModel->getActiveElections();
            }
        }
        
        // Get candidates for each election
        $electionCandidates = [];
        foreach ($elections as $election) {
            $electionCandidates[$election['id']] = $this->candidateModel->getByElection($election['id']);
        }
        
        $this->view('voting/vote', [
            'error' => $error,
            'success' => $success,
            'elections' => $elections,
            'electionCandidates' => $electionCandidates,
            'voterVerified' => $voterVerified,
            'computerNumber' => $_SESSION['voter_computer_number'] ?? ''
        ]);
    }
    
    public function results() {
        $electionModel = new Election();
        $voteModel = new Vote();
        
        $elections = $electionModel->getCompletedElections();
        $selectedElection = null;
        $results = [];
        
        if (isset($_GET['election_id'])) {
            $electionId = (int)$_GET['election_id'];
            $selectedElection = $electionModel->getById($electionId);
            
            // Only show results for completed elections
            if ($selectedElection && $selectedElection['status'] === 'completed') {
                $results = $voteModel->getElectionResults($electionId);
            }
        }
        
        $this->view('voting/results', [
            'elections' => $elections,
            'selectedElection' => $selectedElection,
            'results' => $results
        ]);
    }
    
    public function checkVotingStatus() {
        if (!isset($_SESSION['voter_computer_number'])) {
            $this->json(['error' => 'Not verified']);
            return;
        }
        
        $computerNumber = $_SESSION['voter_computer_number'];
        $electionId = (int)($_GET['election_id'] ?? 0);
        
        if ($electionId <= 0) {
            $this->json(['error' => 'Invalid election']);
            return;
        }
        
        $hasVoted = $this->voteModel->hasVoted($computerNumber, $electionId);
        $this->json(['hasVoted' => $hasVoted]);
    }
}
?>
