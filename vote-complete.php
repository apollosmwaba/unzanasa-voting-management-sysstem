<?php
// Include initialization file
require_once __DIR__ . '/init.php';

// Check if voting was actually completed
if (!isset($_SESSION['voting_completed']) || $_SESSION['voting_completed'] !== true) {
    // Redirect back to voting page if they didn't complete voting
    header('Location: vote.php');
    exit;
}

// Clear the completion flag
unset($_SESSION['voting_completed']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Complete - UNZANASA Student Union</title>
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
        .completion-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
            padding: 3rem;
            text-align: center;
            margin: 2rem auto;
            max-width: 600px;
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1.5rem;
        }
        .completion-message {
            font-size: 1.2rem;
            color: #495057;
            margin-bottom: 2rem;
        }
        .action-buttons {
            margin-top: 2rem;
        }
        .action-buttons .btn {
            margin: 0.5rem;
            padding: 0.75rem 2rem;
        }
        .thank-you-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin: 2rem 0;
        }
        .stats-section {
            background: #e9ecef;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 1.5rem 0;
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
        <div class="completion-card">
            <div class="success-icon">
                🎉
            </div>
            
            <h2 class="text-success mb-4">Voting Completed Successfully!</h2>
            
            <div class="completion-message">
                <p class="lead">
                    <strong>Congratulations!</strong> You have successfully participated in all available elections.
                </p>
                <p>
                    Your votes have been recorded securely and will be counted in the final results. 
                    Thank you for being an active participant in the UNZANASA Student Union democratic process.
                </p>
            </div>

            <div class="thank-you-section">
                <h4>🙏 Thank You for Your Participation</h4>
                <p class="mb-0">
                    Your voice matters! Every vote contributes to shaping the future of our student union. 
                    Stay tuned for the election results announcement.
                </p>
            </div>

            <div class="stats-section">
                <h5>📊 What Happens Next?</h5>
                <ul class="list-unstyled text-start">
                    <li>✅ Your votes are securely stored and encrypted</li>
                    <li>📊 Results will be tallied after the voting period ends</li>
                    <li>📢 Official results will be announced on the student portal</li>
                    <li>🎯 Elected representatives will begin their terms as scheduled</li>
                </ul>
            </div>

            <div class="action-buttons">
                <a href="vote.php" class="btn btn-outline-primary">
                    🔄 Vote with Different Computer Number
                </a>
                <a href="view-results.php" class="btn btn-info">
                    📊 View Current Results
                </a>
            </div>

            <div class="mt-4">
                <small class="text-muted">
                    Voting session completed at <?= date('F j, Y \a\t g:i A') ?>
                </small>
            </div>
        </div>
    </div>

    <footer class="mt-5 py-4 bg-light">
        <div class="container text-center">
            <p class="text-muted mb-0">
                &copy; <?= date('Y') ?> UNZANASA Student Union. All rights reserved.
                <span class="mx-2">|</span>
                <a href="admin-login.php" class="text-decoration-none">Admin Login</a>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auto-redirect after 30 seconds -->
    <script>
        setTimeout(function() {
            if (confirm('Would you like to return to the main voting page?')) {
                window.location.href = 'vote.php';
            }
        }, 30000); // 30 seconds
    </script>
</body>
</html>
