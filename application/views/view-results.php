<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results - UNZANASA Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .results-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .election-selector {
            margin-bottom: 2rem;
        }
        .results-header {
            margin-bottom: 2rem;
            text-align: center;
        }
        .results-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .candidate-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 1rem;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .candidate-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .candidate-photo {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .vote-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            margin: 0.5rem 0;
            overflow: hidden;
        }
        .vote-progress {
            height: 100%;
            background: linear-gradient(90deg, #4e73df, #224abe);
            border-radius: 4px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 2rem 0;
        }
        .badge-winner {
            background: #1cc88a;
            color: white;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="admin-dashboard.php">
                <i class="fas fa-vote-yea me-2"></i>UNZANASA Voting System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="admin-dashboard.php">
                    <i class="fas fa-home me-1"></i>Home
                </a>
                <a class="nav-link" href="upload-voters.php">
                    <i class="fas fa-users me-1"></i>Voter Turnout
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Admin Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="results-container">
        <div class="election-selector">
            <h2>Election Results</h2>
            <form method="get" action="view-results.php" class="row g-3">
                <div class="col-md-8">
                    <select name="election_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Select Election --</option>
                        <?php foreach ($elections as $election): ?>
                            <option value="<?php echo $election['id']; ?>" 
                                <?php echo ($selectedElection && $selectedElection['id'] == $election['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($election['title']); ?> 
                                (<?php echo date('M j, Y', strtotime($election['start_date'])); ?> - <?php echo date('M j, Y', strtotime($election['end_date'])); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">View Results</button>
                </div>
            </form>
        </div>
        
        <?php if ($selectedElection): ?>
            <div class="results-summary">
                <h3><?php echo htmlspecialchars($selectedElection['title']); ?></h3>
                <p class="text-muted">
                    <?php echo date('F j, Y', strtotime($selectedElection['start_date'])); ?> to 
                    <?php echo date('F j, Y', strtotime($selectedElection['end_date'])); ?>
                </p>
                <p><?php echo nl2br(htmlspecialchars($selectedElection['description'] ?? 'No description available')); ?></p>
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Total Votes</h5>
                                <h2 class="mb-0"><?php echo number_format(array_sum(array_column($results, 'vote_count'))); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Candidates</h5>
                                <h2 class="mb-0"><?php echo count($results); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Status</h5>
                                <h4 class="mb-0">
                                    <?php 
                                    $now = new DateTime();
                                    $startDate = new DateTime($selectedElection['start_date']);
                                    $endDate = new DateTime($selectedElection['end_date']);
                                    
                                    if ($now < $startDate) {
                                        echo '<span class="badge bg-warning">Upcoming</span>';
                                    } elseif ($now > $endDate) {
                                        echo '<span class="badge bg-secondary">Completed</span>';
                                    } else {
                                        echo '<span class="badge bg-success">In Progress</span>';
                                    }
                                    ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Vote Distribution</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="resultsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Detailed Results</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($results)): ?>
                                <div class="alert alert-info">No votes have been cast in this election yet.</div>
                            <?php else: ?>
                                <?php foreach ($results as $index => $result): 
                                    $candidate = $result['candidate'];
                                    $isWinner = $index === 0 && $result['vote_count'] > 0;
                                ?>
                                    <div class="candidate-card">
                                        <div class="row g-0">
                                            <div class="col-md-3">
                                                <img src="<?php echo !empty($candidate['photo']) ? htmlspecialchars($candidate['photo']) : 'assets/img/default-avatar.png'; ?>" 
                                                     alt="<?php echo htmlspecialchars($candidate['name']); ?>" 
                                                     class="candidate-photo">
                                            </div>
                                            <div class="col-md-9">
                                                <div class="p-3">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <h5 class="mb-1">
                                                            <?php echo htmlspecialchars($candidate['name']); ?>
                                                            <?php if ($isWinner): ?>
                                                                <span class="badge-winner">Winner</span>
                                                            <?php endif; ?>
                                                        </h5>
                                                        <h4 class="mb-0">
                                                            <span class="text-primary"><?php echo $result['vote_count']; ?></span>
                                                            <small class="text-muted">votes</small>
                                                        </h4>
                                                    </div>
                                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($candidate['position'] ?? 'Position'); ?></p>
                                                    
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <small>Vote Percentage</small>
                                                        <small class="text-muted"><?php echo $result['percentage']; ?>%</small>
                                                    </div>
                                                    <div class="vote-bar">
                                                        <div class="vote-progress" style="width: <?php echo $result['percentage']; ?>%;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Results Summary</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($results)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Candidate</th>
                                                <th class="text-end">Votes</th>
                                                <th class="text-end">%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($results as $result): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($result['candidate']->name); ?></td>
                                                    <td class="text-end"><?php echo number_format($result['vote_count']); ?></td>
                                                    <td class="text-end"><?php echo $result['percentage']; ?>%</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3">
                                    <a href="export-results.php?election_id=<?php echo $selectedElection->id; ?>" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-download me-2"></i>Export Results
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">No results available for this election yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Election Statistics</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($results)): 
                                $totalVotes = array_sum(array_column($results, 'vote_count'));
                                $maxVotes = max(array_column($results, 'vote_count'));
                                $winners = array_filter($results, function($r) use ($maxVotes) {
                                    return $r['vote_count'] === $maxVotes;
                                });
                                $winnerNames = array_map(function($w) {
                                    return $w['candidate']->name;
                                }, $winners);
                                $winnerVoteCount = $maxVotes;
                                $winnerPercentage = $winnerVoteCount > 0 ? round(($winnerVoteCount / $totalVotes) * 100, 2) : 0;
                            ?>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Winner(s)
                                        <span class="badge bg-primary rounded-pill">
                                            <?php echo implode(', ', array_map('htmlspecialchars', $winnerNames)); ?>
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Winning Votes
                                        <span class="text-primary">
                                            <?php echo number_format($winnerVoteCount); ?> 
                                            <small class="text-muted">(<?php echo $winnerPercentage; ?>%)</small>
                                        </span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Total Votes Cast
                                        <span class="text-primary"><?php echo number_format($totalVotes); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Number of Candidates
                                        <span class="text-primary"><?php echo count($results); ?></span>
                                    </li>
                                </ul>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">No statistics available yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
                // Initialize the chart when the page loads
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('resultsChart').getContext('2d');
                    
                    // Prepare chart data
                    const labels = <?php echo json_encode(array_map(function($r) { 
                        return $r['candidate']['name']; 
                    }, $results)); ?>;
                    
                    const data = <?php echo json_encode(array_map(function($r) { 
                        return $r['vote_count']; 
                    }, $results)); ?>;
                    
                    // Create chart
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Votes',
                                data: data,
                                backgroundColor: [
                                    'rgba(78, 115, 223, 0.7)',
                                    'rgba(54, 185, 204, 0.7)',
                                    'rgba(231, 74, 59, 0.7)',
                                    'rgba(246, 194, 62, 0.7)',
                                    'rgba(28, 200, 138, 0.7)',
                                    'rgba(153, 102, 255, 0.7)',
                                    'rgba(255, 159, 64, 0.7)'
                                ],
                                borderColor: [
                                    'rgba(78, 115, 223, 1)',
                                    'rgba(54, 185, 204, 1)',
                                    'rgba(231, 74, 59, 1)',
                                    'rgba(246, 194, 62, 1)',
                                    'rgba(28, 200, 138, 1)',
                                    'rgba(153, 102, 255, 1)',
                                    'rgba(255, 159, 64, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            const value = context.raw;
                                            const total = <?php echo array_sum(array_column($results, 'vote_count')); ?>;
                                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                            return `${label}${value} (${percentage}%)`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        <?php else: ?>
            <div class="alert alert-info">
                Please select an election to view results.
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>