<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Turnout Report - UNZANASA Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .turnout-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .computer-number {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #495057;
        }
        .vote-time {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .election-selector {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
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
                <a class="nav-link" href="view-results.php">
                    <i class="fas fa-chart-bar me-1"></i>View Results
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Admin Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-4 text-center mb-3">
                    <i class="fas fa-users text-primary me-3"></i>Voter Turnout Report
                </h1>
                <p class="text-center text-muted">Track voting participation and engagement statistics</p>
            </div>
        </div>

        <!-- Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Election Selector -->
        <div class="election-selector">
            <h4 class="mb-3"><i class="fas fa-ballot-check me-2"></i>Select Election</h4>
            <form method="GET" class="row g-3">
                <div class="col-md-8">
                    <select name="election_id" class="form-select form-select-lg" required>
                        <option value="">-- Select an Election --</option>
                        <?php foreach ($elections as $election): ?>
                            <option value="<?php echo $election['id']; ?>" 
                                    <?php echo (isset($_GET['election_id']) && $_GET['election_id'] == $election['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($election['title']); ?> 
                                (<?php echo ucfirst($election['status']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-search me-2"></i>Generate Report
                    </button>
                </div>
            </form>
        </div>

        <?php if ($selectedElection && !empty($turnoutData)): ?>
            <!-- Election Info -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <?php echo htmlspecialchars($selectedElection['title']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> 
                                        <span class="badge bg-<?php echo $selectedElection['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($selectedElection['status']); ?>
                                        </span>
                                    </p>
                                    <p><strong>Start Date:</strong> <?php echo date('M d, Y', strtotime($selectedElection['start_date'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>End Date:</strong> <?php echo date('M d, Y', strtotime($selectedElection['end_date'])); ?></p>
                                    <p><strong>Description:</strong> <?php echo htmlspecialchars($selectedElection['description'] ?? 'No description'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <h3><?php echo number_format($turnoutStats['total_votes']); ?></h3>
                        <p class="mb-0"><i class="fas fa-vote-yea me-2"></i>Total Votes Cast</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <h3><?php echo number_format($turnoutStats['total_registered']); ?></h3>
                        <p class="mb-0"><i class="fas fa-users me-2"></i>Registered Voters</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <h3><?php echo $turnoutStats['turnout_percentage']; ?>%</h3>
                        <p class="mb-0"><i class="fas fa-percentage me-2"></i>Turnout Rate</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card text-center">
                        <h3><?php echo $turnoutStats['peak_hour']; ?>:00</h3>
                        <p class="mb-0"><i class="fas fa-clock me-2"></i>Peak Hour</p>
                        <small>(<?php echo $turnoutStats['peak_votes']; ?> votes)</small>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="chart-container">
                        <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i>Hourly Voting Distribution</h5>
                        <canvas id="hourlyChart" width="400" height="200"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <h5 class="mb-3"><i class="fas fa-chart-line me-2"></i>Daily Voting Timeline</h5>
                        <canvas id="dailyChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Voters Table -->
            <div class="turnout-table">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>Voters Who Participated
                            <span class="badge bg-primary ms-2"><?php echo count($turnoutData); ?> voters</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="10%">#</th>
                                        <th width="30%">Computer Number</th>
                                        <th width="40%">Vote Time</th>
                                        <th width="20%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($turnoutData as $index => $voter): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td>
                                                <span class="computer-number">
                                                    <?php echo htmlspecialchars($voter['computer_number']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="vote-time">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    <?php echo $voter['formatted_time']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Voted
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($selectedElection && empty($turnoutData)): ?>
            <!-- No votes yet -->
            <div class="text-center py-5">
                <i class="fas fa-vote-yea fa-5x text-muted mb-4"></i>
                <h3 class="text-muted">No Votes Cast Yet</h3>
                <p class="text-muted">No voters have participated in this election yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($selectedElection && !empty($turnoutData)): ?>
    <script>
        // Hourly Chart
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        const hourlyData = <?php echo json_encode($turnoutStats['hourly_votes']); ?>;
        
        const hourlyLabels = [];
        const hourlyValues = [];
        for (let i = 0; i < 24; i++) {
            hourlyLabels.push(i + ':00');
            hourlyValues.push(hourlyData[i] || 0);
        }
        
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: hourlyLabels,
                datasets: [{
                    label: 'Votes per Hour',
                    data: hourlyValues,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Daily Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        const dailyData = <?php echo json_encode($turnoutStats['daily_votes']); ?>;
        
        const dailyLabels = dailyData.map(item => {
            const date = new Date(item.vote_date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        const dailyValues = dailyData.map(item => item.daily_votes);
        
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Votes per Day',
                    data: dailyValues,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
