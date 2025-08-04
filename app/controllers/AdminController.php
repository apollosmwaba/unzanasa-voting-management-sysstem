<?php
require_once __DIR__ . '/../../router.php';
require_once __DIR__ . '/../../init.php';

class AdminController extends BaseController {
    private $adminModel;
    
    public function __construct() {
        $this->adminModel = new Admin();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            $user = $this->adminModel->authenticate($username, $password);
            
            if ($user) {
                $this->adminModel->createSession($user['id']);
                
                if ($remember) {
                    setcookie('admin_remember', base64_encode($username), time() + (86400 * 30), '/');
                }
                
                $this->redirect('/admin/dashboard');
            } else {
                $error = 'Invalid username or password';
                $this->view('admin/login', ['error' => $error]);
            }
        } else {
            $this->view('admin/login');
        }
    }
    
    public function dashboard() {
        Auth::requireAdmin();
        
        // Get dashboard statistics
        $electionModel = new Election();
        $candidateModel = new Candidate();
        $voteModel = new Vote();
        
        $stats = [
            'total_elections' => $electionModel->getTotalCount(),
            'active_elections' => count($electionModel->getActiveElections()),
            'total_candidates' => $candidateModel->getTotalCount(),
            'total_votes' => $voteModel->getTotalVotes()
        ];
        
        $this->view('admin/dashboard', ['stats' => $stats]);
    }
    
    public function logout() {
        Auth::requireAdmin();
        Admin::logout();
        $this->redirect('/admin/login');
    }
    
    public function manageElections() {
        Auth::requireAdmin();
        
        $electionModel = new Election();
        $elections = $electionModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'create':
                    $data = [
                        'title' => $_POST['title'] ?? '',
                        'name' => $_POST['name'] ?? '',
                        'description' => $_POST['description'] ?? '',
                        'start_date' => $_POST['start_date'] ?? '',
                        'end_date' => $_POST['end_date'] ?? '',
                        'status' => $_POST['status'] ?? 'inactive'
                    ];
                    
                    if ($electionModel->create($data)) {
                        $success = 'Election created successfully!';
                    } else {
                        $error = 'Failed to create election.';
                    }
                    break;
                    
                case 'update':
                    $id = (int)($_POST['id'] ?? 0);
                    $data = [
                        'title' => $_POST['title'] ?? '',
                        'name' => $_POST['name'] ?? '',
                        'description' => $_POST['description'] ?? '',
                        'start_date' => $_POST['start_date'] ?? '',
                        'end_date' => $_POST['end_date'] ?? '',
                        'status' => $_POST['status'] ?? 'inactive'
                    ];
                    
                    if ($electionModel->update($id, $data)) {
                        $success = 'Election updated successfully!';
                    } else {
                        $error = 'Failed to update election.';
                    }
                    break;
                    
                case 'delete':
                    $id = (int)($_POST['id'] ?? 0);
                    if ($electionModel->delete($id)) {
                        $success = 'Election deleted successfully!';
                    } else {
                        $error = 'Failed to delete election.';
                    }
                    break;
            }
            
            // Refresh elections list
            $elections = $electionModel->getAll();
        }
        
        $this->view('admin/manage-elections', [
            'elections' => $elections,
            'success' => $success ?? null,
            'error' => $error ?? null
        ]);
    }
    
    public function manageCandidates() {
        Auth::requireAdmin();
        
        $candidateModel = new Candidate();
        $electionModel = new Election();
        $positionModel = new Position();
        
        $candidates = $candidateModel->getAll();
        $elections = $electionModel->getAll();
        $positions = $positionModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            switch ($action) {
                case 'create':
                    $data = [
                        'firstname' => $_POST['firstname'] ?? '',
                        'lastname' => $_POST['lastname'] ?? '',
                        'position_id' => (int)($_POST['position_id'] ?? 0),
                        'election_id' => (int)($_POST['election_id'] ?? 0),
                        'platform' => $_POST['platform'] ?? '',
                        'status' => 1
                    ];
                    
                    if ($candidateModel->create($data)) {
                        $success = 'Candidate added successfully!';
                    } else {
                        $error = 'Failed to add candidate.';
                    }
                    break;
                    
                case 'update':
                    $id = (int)($_POST['id'] ?? 0);
                    $data = [
                        'firstname' => $_POST['firstname'] ?? '',
                        'lastname' => $_POST['lastname'] ?? '',
                        'position_id' => (int)($_POST['position_id'] ?? 0),
                        'election_id' => (int)($_POST['election_id'] ?? 0),
                        'platform' => $_POST['platform'] ?? ''
                    ];
                    
                    if ($candidateModel->update($id, $data)) {
                        $success = 'Candidate updated successfully!';
                    } else {
                        $error = 'Failed to update candidate.';
                    }
                    break;
                    
                case 'delete':
                    $id = (int)($_POST['id'] ?? 0);
                    if ($candidateModel->delete($id)) {
                        $success = 'Candidate deleted successfully!';
                    } else {
                        $error = 'Failed to delete candidate.';
                    }
                    break;
            }
            
            // Refresh data
            $candidates = $candidateModel->getAll();
        }
        
        $this->view('admin/manage-candidates', [
            'candidates' => $candidates,
            'elections' => $elections,
            'positions' => $positions,
            'success' => $success ?? null,
            'error' => $error ?? null
        ]);
    }
    
    public function viewResults() {
        Auth::requireAdmin();
        
        $electionModel = new Election();
        $voteModel = new Vote();
        
        $elections = $electionModel->getAll();
        $selectedElection = null;
        $results = [];
        
        if (isset($_GET['election_id'])) {
            $electionId = (int)$_GET['election_id'];
            $selectedElection = $electionModel->getById($electionId);
            if ($selectedElection) {
                $results = $voteModel->getElectionResults($electionId);
            }
        }
        
        $this->view('admin/view-results', [
            'elections' => $elections,
            'selectedElection' => $selectedElection,
            'results' => $results
        ]);
    }
}
?>
