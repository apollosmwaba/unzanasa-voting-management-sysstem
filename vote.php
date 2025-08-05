<?php
// Include initialization file
require_once __DIR__ . '/init.php';

// Check if reset is requested
if (isset($_GET['reset'])) {
    unset($_SESSION['voter_computer_number']);
    unset($_SESSION['voted_elections']);
    unset($_SESSION['voting_completed']);
    header('Location: index.php');
    exit;
}

$electionModel = new Election();
$candidateModel = new Candidate();
$voteModel = new Vote();

// Get active elections
$activeElections = $electionModel->getActiveElections();

// Initialize voted elections tracking
if (!isset($_SESSION['voted_elections'])) {
    $_SESSION['voted_elections'] = [];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'verify_computer_number') {
        // Verify computer number
        $computerNumber = Utils::sanitize($_POST['computer_number']);
        if (empty($computerNumber) || !Utils::validateComputerNumber($computerNumber)) {
            $error = 'Please enter a valid 10-digit computer number';
        } else {
            // Store computer number in session and reset voted elections
            $_SESSION['voter_computer_number'] = $computerNumber;
            $_SESSION['voted_elections'] = [];
            $showElections = true;
        }
    } 
    elseif (isset($_POST['action']) && $_POST['action'] === 'vote') {
        // Handle vote submission
        $computerNumber = $_SESSION['voter_computer_number'] ?? '';
        $electionId = (int)($_POST['election_id'] ?? 0);
        $candidateId = (int)($_POST['candidate_id'] ?? 0);
        
        if (empty($computerNumber) || !Utils::validateComputerNumber($computerNumber)) {
            $error = 'Invalid computer number. Please start over.';
            unset($_SESSION['voter_computer_number']);
            unset($_SESSION['voted_elections']);
        } else {
            $success = $voteModel->castVote(
                $electionId, 
                $candidateId, 
                $computerNumber, 
                Utils::getClientIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? null
            );
            
            if ($success) {
                // Track this election as voted
                $_SESSION['voted_elections'][] = $electionId;
                
                // Check if all elections have been voted for
                $totalActiveElections = count($activeElections);
                $votedElectionsCount = count($_SESSION['voted_elections']);
                
                if ($votedElectionsCount >= $totalActiveElections) {
                    // All elections completed - redirect to thank you page
                    $_SESSION['voting_completed'] = true;
                    unset($_SESSION['voter_computer_number']);
                    unset($_SESSION['voted_elections']);
                    header('Location: vote-complete.php');
                    exit;
                } else {
                    $successMessage = 'Vote cast successfully! Please continue voting for the remaining elections.';
                }
            } else {
                $error = 'Unable to cast vote. You may have already voted for this election.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote - UNZANASA Student Union</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            text-align: center;
        }
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .vote-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .election-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .candidate-option {
            margin-bottom: 1rem;
        }
        .candidate-option input[type="radio"] {
            display: none;
        }
        .candidate-option label {
            display: block;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .candidate-option input[type="radio"]:checked + label {
            border-color: #0d6efd;
            background-color: #f8f9ff;
        }
        .candidate-card {
            display: flex;
            align-items: center;
        }
        .candidate-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 1.5rem;
            flex-shrink: 0;
            background-color: #f1f1f1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #6c757d;
        }
        .candidate-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .candidate-details h4 {
            margin: 0 0 0.5rem 0;
            color: #212529;
        }
        .candidate-bio {
            color: #6c757d;
            margin: 0;
            font-size: 0.9rem;
        }
        .voter-info {
            background: #e9ecef;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-vote {
            margin-top: 1.5rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>UNZANASA</h1>
            <p class="lead">Student Union Voting System</p>
        </div>
    </div>

    <div class="container">
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($successMessage) ?>
                <div class="mt-3">
                    <a href="index.php" class="btn btn-primary">Return Home</a>
                </div>
            </div>
        <?php else: ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!isset($_SESSION['voter_computer_number'])): ?>
                <!-- Step 1: Computer Number Input -->
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="vote-section">
                            <h2 class="text-center mb-4">🔢 Enter Your Computer Number</h2>
                            <form method="POST">
                                <input type="hidden" name="action" value="verify_computer_number">
                                <div class="mb-3">
                                    <label for="computer_number" class="form-label">Computer Number</label>
                                    <input type="text" 
                                           id="computer_number" 
                                           name="computer_number" 
                                           class="form-control form-control-lg" 
                                           placeholder="Enter your 10-digit computer number" 
                                           required
                                           pattern="\d{10}"
                                           title="Please enter a 10-digit number"
                                           value="<?= isset($_POST['computer_number']) ? htmlspecialchars($_POST['computer_number']) : '' ?>">
                                    <div class="form-text">Please enter your 10-digit computer number to continue.</div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">Continue to Vote</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Step 2: Show Elections and Candidates -->
                <div class="voter-info">
                    <div>
                        <span class="text-muted">Voting as:</span>
                        <strong><?= htmlspecialchars($_SESSION['voter_computer_number']) ?></strong>
                    </div>
                    <a href="vote.php?reset=1" class="btn btn-outline-secondary btn-sm">Change Number</a>
                </div>

                <?php if (empty($activeElections)): ?>
                    <div class="alert alert-info">
                        <h4 class="alert-heading">No Active Elections</h4>
                        <p>There are currently no active elections available for voting. Please check back later.</p>
                    </div>
                <?php else: ?>
                    <!-- Voting Progress -->
                    <div class="alert alert-info mb-4">
                        <h5 class="alert-heading">📊 Voting Progress</h5>
                        <p class="mb-0">
                            Completed: <strong><?= count($_SESSION['voted_elections']) ?></strong> of <strong><?= count($activeElections) ?></strong> elections
                            <?php if (count($_SESSION['voted_elections']) > 0): ?>
                                <span class="ms-3">✅ Keep going! Vote for all elections to complete your participation.</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <?php 
                    $remainingElections = [];
                    $completedElections = [];
                    
                    foreach ($activeElections as $election): 
                        $candidates = $candidateModel->getCandidatesByElection($election['id']);
                        if (empty($candidates)) continue;
                        
                        if (in_array($election['id'], $_SESSION['voted_elections'])) {
                            $completedElections[] = $election;
                        } else {
                            $remainingElections[] = $election;
                        }
                    endforeach;
                    ?>
                    
                    <!-- Show Completed Elections -->
                    <?php if (!empty($completedElections)): ?>
                        <div class="mb-4">
                            <h4 class="text-success">✅ Completed Elections</h4>
                            <?php foreach ($completedElections as $election): ?>
                                <div class="election-card mb-3 border-success bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1 text-success"><?= htmlspecialchars($election['title']) ?></h5>
                                            <small class="text-muted">Vote cast successfully</small>
                                        </div>
                                        <span class="badge bg-success fs-6">✓ Complete</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Show Remaining Elections -->
                    <?php if (!empty($remainingElections)): ?>
                        <h4 class="text-primary mb-3">🗳️ Remaining Elections</h4>
                        <?php foreach ($remainingElections as $election): 
                            $candidates = $candidateModel->getCandidatesByElection($election['id']);
                        ?>
                            <div class="election-card mb-4">
                                <h2 class="h4 mb-3"><?= htmlspecialchars($election['title']) ?></h2>
                                <?php if (!empty($election['description'])): ?>
                                    <p class="text-muted mb-3"><?= htmlspecialchars($election['description']) ?></p>
                                <?php endif; ?>
                                
                                <div class="election-meta mb-4">
                                    <span class="badge bg-info text-dark">
                                        🗓️ <?= date('F j, Y', strtotime($election['start_date'])) ?> - <?= date('F j, Y', strtotime($election['end_date'])) ?>
                                    </span>
                                    <span class="badge bg-secondary ms-2">
                                        Position: <?= htmlspecialchars($election['position'] ?? 'General') ?>
                                    </span>
                                </div>
                                
                                <form method="POST" class="candidates-list">
                                    <input type="hidden" name="action" value="vote">
                                    <input type="hidden" name="election_id" value="<?= $election['id'] ?>">
                                    <input type="hidden" name="computer_number" value="<?= htmlspecialchars($_SESSION['voter_computer_number']) ?>">
                                    
                                    <h5 class="mb-3">Select your candidate:</h5>
                                    <div class="row">
                                        <?php foreach ($candidates as $candidate): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="candidate-option">
                                                    <input type="radio" 
                                                           id="candidate_<?= $candidate['id'] ?>_<?= $election['id'] ?>" 
                                                           name="candidate_id" 
                                                           value="<?= $candidate['id'] ?>"
                                                           required>
                                                    <label for="candidate_<?= $candidate['id'] ?>_<?= $election['id'] ?>">
                                                        <div class="candidate-card">
                                                            <div class="candidate-photo">
                                                                <?php if (!empty($candidate['photo'])): ?>
                                                                    <img src="<?= htmlspecialchars($candidate['photo']) ?>" 
                                                                         alt="<?= htmlspecialchars($candidate['name']) ?>" 
                                                                         onerror="this.onerror=null; this.parentElement.innerHTML='👤';">
                                                                <?php else: ?>
                                                                    👤
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="candidate-details">
                                                                <h4><?= htmlspecialchars($candidate['name']) ?></h4>
                                                                <?php if (!empty($candidate['bio'])): ?>
                                                                    <p class="candidate-bio"><?= nl2br(htmlspecialchars($candidate['bio'])) ?></p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg px-5">
                                            Cast Your Vote
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <h4 class="alert-heading">🎉 All Elections Completed!</h4>
                            <p>You have successfully voted in all available elections. Thank you for your participation!</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <footer class="mt-5 py-4 bg-light">
        <div class="container text-center">
            <p class="text-muted mb-0">
                &copy; <?= date('Y') ?> UNZANASA Student Union. All rights reserved.
                <?php if (!isset($_SESSION['admin_id'])): ?>
                    <span class="mx-2">|</span>
                    <a href="admin-login.php" class="text-decoration-none">Admin Login</a>
                <?php endif; ?>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Client-side validation for computer number
        document.addEventListener('DOMContentLoaded', function() {
            const computerNumberInput = document.getElementById('computer_number');
            if (computerNumberInput) {
                computerNumberInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
                });
            }
            
            // Add confirmation dialog before submitting vote
            const voteForms = document.querySelectorAll('form[action*="vote"]');
            voteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Are you sure you want to cast your vote? This action cannot be undone.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
