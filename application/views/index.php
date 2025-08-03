<?php
// Part 5: Voting interface (homepage for voters)
if (basename($_SERVER['PHP_SELF']) === 'index.php') {
    // Include initialization file
    require_once __DIR__ . '/../../init.php';
    
    $electionModel = new Election();
    $candidateModel = new Candidate();
    $voteModel = new Vote();
    
    $activeElections = $electionModel->getActiveElections();
    
    // Handle vote submission
    if ($_POST && isset($_POST['action']) && $_POST['action'] === 'vote') {
        $computerNumber = Utils::sanitize($_POST['computer_number']);
        $electionId = (int)$_POST['election_id'];
        $candidateId = (int)$_POST['candidate_id'];
        
        if (empty($computerNumber) || !Utils::validateComputerNumber($computerNumber)) {
            $error = 'Please enter a valid 10-digit computer number';
        } else {
            $success = $voteModel->castVote(
                $electionId, 
                $candidateId, 
                $computerNumber, 
                Utils::getClientIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? null
            );
            
            if ($success) {
                $successMessage = 'Your vote has been cast successfully! Thank you for participating.';
            } else {
                $error = 'Unable to cast vote. You may have already voted or the computer number is invalid.';
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>UNZANASA Student Voting System</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                color: #333;
            }
            
            .header {
                background: rgba(255,255,255,0.1);
                backdrop-filter: blur(10px);
                color: white;
                padding: 2rem 0;
                text-align: center;
                border-bottom: 1px solid rgba(255,255,255,0.2);
            }
            
            .header h1 {
                font-size: 2.5rem;
                margin-bottom: 0.5rem;
                text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            }
            
            .header p {
                font-size: 1.1rem;
                opacity: 0.9;
            }
            
            .container {
                max-width: 1200px;
                margin: 2rem auto;
                padding: 0 2rem;
            }
            
            .message {
                padding: 1rem;
                border-radius: 10px;
                margin-bottom: 2rem;
                text-align: center;
                font-weight: 500;
            }
            
            .message.success {
                background: rgba(40, 167, 69, 0.9);
                color: white;
                border: 2px solid #28a745;
            }
            
            .message.error {
                background: rgba(220, 53, 69, 0.9);
                color: white;
                border: 2px solid #dc3545;
            }
            
            .elections-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 2rem;
                margin-bottom: 2rem;
            }
            
            .election-card {
                background: rgba(255,255,255,0.95);
                backdrop-filter: blur(10px);
                border-radius: 15px;
                padding: 2rem;
                box-shadow: 0 8px 32px rgba(0,0,0,0.1);
                border: 1px solid rgba(255,255,255,0.2);
                transition: transform 0.3s, box-shadow 0.3s;
            }
            
            .election-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 40px rgba(0,0,0,0.15);
            }
            
            .election-title {
                font-size: 1.5rem;
                font-weight: bold;
                color: #333;
                margin-bottom: 0.5rem;
            }
            
            .election-position {
                font-size: 1.1rem;
                color: #667eea;
                font-weight: 600;
                margin-bottom: 1rem;
            }
            
            .election-description {
                color: #666;
                margin-bottom: 1.5rem;
                line-height: 1.5;
            }
            
            .candidates-list {
                margin-bottom: 1.5rem;
            }
            
            .candidate-item {
                display: flex;
                align-items: center;
                padding: 1rem;
                border: 2px solid #e1e1e1;
                border-radius: 10px;
                margin-bottom: 1rem;
                transition: all 0.3s;
                cursor: pointer;
            }
            
            .candidate-item:hover {
                border-color: #667eea;
                background: #f8f9fa;
                transform: translateX(5px);
            }
            
            .candidate-item.selected {
                border-color: #667eea;
                background: rgba(102, 126, 234, 0.1);
            }
            
            .candidate-photo {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: #f0f0f0;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 1rem;
                font-size: 1.5rem;
                color: #666;
                overflow: hidden;
            }
            
            .candidate-photo img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .candidate-info {
                flex: 1;
            }
            
            .candidate-name {
                font-weight: bold;
                margin-bottom: 0.25rem;
                color: #333;
            }
            
            .candidate-bio {
                font-size: 0.9rem;
                color: #666;
                line-height: 1.4;
            }
            
            .vote-section {
                background: rgba(255,255,255,0.95);
                backdrop-filter: blur(10px);
                border-radius: 15px;
                padding: 2rem;
                box-shadow: 0 8px 32px rgba(0,0,0,0.1);
                border: 1px solid rgba(255,255,255,0.2);
            }
            
            .form-group {
                margin-bottom: 1.5rem;
            }
            
            label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 600;
                color: #333;
            }
            
            input, select {
                width: 100%;
                padding: 1rem;
                border: 2px solid #e1e1e1;
                border-radius: 10px;
                font-size: 1rem;
                transition: border-color 0.3s;
                background: white;
            }
            
            input:focus, select:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            
            .btn {
                width: 100%;
                padding: 1rem 2rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 1.1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
            }
            
            .btn:disabled {
                background: #ccc;
                cursor: not-allowed;
                transform: none;
                box-shadow: none;
            }
            
            .info-section {
                background: rgba(255,255,255,0.95);
                backdrop-filter: blur(10px);
                border-radius: 15px;
                padding: 2rem;
                box-shadow: 0 8px 32px rgba(0,0,0,0.1);
                border: 1px solid rgba(255,255,255,0.2);
                margin-bottom: 2rem;
                text-align: center;
            }
            
            .info-section h3 {
                color: #333;
                margin-bottom: 1rem;
            }
            
            .voting-rules {
                list-style: none;
                text-align: left;
                max-width: 600px;
                margin: 0 auto;
            }
            
            .voting-rules li {
                padding: 0.5rem 0;
                border-bottom: 1px solid #eee;
                position: relative;
                padding-left: 2rem;
            }
            
            .voting-rules li:before {
                content: '✓';
                position: absolute;
                left: 0;
                color: #28a745;
                font-weight: bold;
            }
            
            .no-elections {
                text-align: center;
                padding: 3rem;
                color: white;
                font-size: 1.2rem;
            }
            
            .admin-link {
                position: fixed;
                bottom: 2rem;
                right: 2rem;
                background: rgba(255,255,255,0.2);
                color: white;
                padding: 1rem;
                border-radius: 50%;
                text-decoration: none;
                font-size: 1.5rem;
                transition: all 0.3s;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.3);
            }
            
            .admin-link:hover {
                background: rgba(255,255,255,0.3);
                transform: scale(1.1);
            }
            
            @media (max-width: 768px) {
                .header h1 {
                    font-size: 2rem;
                }
                
                .elections-grid {
                    grid-template-columns: 1fr;
                }
                
                .container {
                    padding: 0 1rem;
                }
                
                .candidate-item {
                    flex-direction: column;
                    text-align: center;
                }
                
                .candidate-photo {
                    margin-right: 0;
                    margin-bottom: 1rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>🗳️ UNZANASA</h1>
            <p>Student Union Voting System</p>
        </div>
        
        <div class="container">
            <?php if (isset($successMessage)): ?>
                <div class="message success">
                    ✅ <?= htmlspecialchars($successMessage) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="message error">
                    ❌ <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <!-- Voting Instructions -->
            <div class="info-section">
                <h3>📋 Voting Instructions</h3>
                <ul class="voting-rules">
                    <li>Enter your 10-digit computer number to vote</li>
                    <li>You can only vote once per election position</li>
                    <li>Select one candidate per position</li>
                    <li>Your vote is secret and secure</li>
                    <li>Make sure to vote before the election deadline</li>
                </ul>
            </div>
            
            <?php if (empty($activeElections)): ?>
                <div class="no-elections">
                    <h2>🗳️ No Active Elections</h2>
                    <p>There are currently no active elections. Please check back later.</p>
                </div>
            <?php else: ?>
                <!-- Computer Number Input -->
                <div class="vote-section">
                    <h3>🔢 Enter Your Computer Number</h3>
                    <form id="votingForm" method="POST">
                        <input type="hidden" name="action" value="vote">
                        <input type="hidden" name="election_id" id="selectedElectionId">
                        <input type="hidden" name="candidate_id" id="selectedCandidateId">
                        
                        <div class="form-group">
                            <label for="computer_number">Computer Number (10 digits)</label>
                            <input type="text" id="computer_number" name="computer_number" 
                                   pattern="[0-9]{10}" maxlength="10" required
                                   placeholder="Enter your 10-digit computer number">
                        </div>
                        
                        <button type="submit" class="btn" id="voteBtn" disabled>
                            🗳️ Cast Your Vote
                        </button>
                    </form>
                </div>
                
                <!-- Elections -->
                <div class="elections-grid">
                    <?php foreach ($activeElections as $election): ?>
                        <?php 
                        $candidates = $candidateModel->getCandidatesByElection($election['id']);
                        ?>
                        <div class="election-card">
                            <div class="election-title"><?= htmlspecialchars($election['title']) ?></div>
                            <div class="election-position"><?= htmlspecialchars($election['position']) ?></div>
                            <?php if ($election['description']): ?>
                                <div class="election-description"><?= htmlspecialchars($election['description']) ?></div>
                            <?php endif; ?>
                            
                            <div class="candidates-list">
                                <h4>Candidates:</h4>
                                <?php if (empty($candidates)): ?>
                                    <p>No candidates registered yet.</p>
                                <?php else: ?>
                                    <?php foreach ($candidates as $candidate): ?>
                                        <div class="candidate-item" onclick="selectCandidate(<?= $election['id'] ?>, <?= $candidate['id'] ?>, this)">
                                            <div class="candidate-photo">
                                                <?php if ($candidate['photo'] && file_exists($candidate['photo'])): ?>
                                                    <img src="<?= htmlspecialchars($candidate['photo']) ?>" alt="<?= htmlspecialchars($candidate['name']) ?>">
                                                <?php else: ?>
                                                    👤
                                                <?php endif; ?>
                                            </div>
                                            <div class="candidate-info">
                                                <div class="candidate-name"><?= htmlspecialchars($candidate['name']) ?></div>
                                                <?php if ($candidate['bio']): ?>
                                                    <div class="candidate-bio"><?= htmlspecialchars(substr($candidate['bio'], 0, 100)) ?>...</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <a href="admin-login.php" class="admin-link" title="Admin Login">🔐</a>
        
        <script>
            let selectedElectionId = null;
            let selectedCandidateId = null;
            
            function selectCandidate(electionId, candidateId, element) {
                // Remove previous selections
                document.querySelectorAll('.candidate-item').forEach(item => {
                    item.classList.remove('selected');
                });
                
                // Add selection to clicked item
                element.classList.add('selected');
                
                // Update form
                selectedElectionId = electionId;
                selectedCandidateId = candidateId;
                
                document.getElementById('selectedElectionId').value = electionId;
                document.getElementById('selectedCandidateId').value = candidateId;
                
                updateVoteButton();
            }
            
            function updateVoteButton() {
                const computerNumber = document.getElementById('computer_number').value;
                const voteBtn = document.getElementById('voteBtn');
                
                if (computerNumber.length === 10 && selectedCandidateId) {
                    voteBtn.disabled = false;
                    voteBtn.textContent = '🗳️ Cast Your Vote';
                } else {
                    voteBtn.disabled = true;
                    if (!selectedCandidateId) {
                        voteBtn.textContent = 'Select a Candidate First';
                    } else {
                        voteBtn.textContent = 'Enter Computer Number';
                    }
                }
            }
            
            // Update vote button when computer number changes
            document.getElementById('computer_number').addEventListener('input', function() {
                // Only allow digits
                this.value = this.value.replace(/[^0-9]/g, '');
                updateVoteButton();
            });
            
            // Form validation
            document.getElementById('votingForm').addEventListener('submit', function(e) {
                if (!selectedCandidateId || !selectedElectionId) {
                    e.preventDefault();
                    alert('Please select a candidate first!');
                    return false;
                }
                
                const computerNumber = document.getElementById('computer_number').value;
                if (computerNumber.length !== 10) {
                    e.preventDefault();
                    alert('Please enter a valid 10-digit computer number!');
                    return false;
                }
                
                return confirm('Are you sure you want to cast your vote? This action cannot be undone.');
            });
        </script>
    </body>
    </html>
    <?php
    exit;
}