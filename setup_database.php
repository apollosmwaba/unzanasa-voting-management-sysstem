<?php
// Start output buffering
ob_start();

// Database configuration
$host = 'localhost:8082';
$user = 'root';
$pass = '';
$dbname = 'unzanasa_voting';

// Initialize messages array
$messages = [];

// HTML Header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - UNZANASA Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 2rem; padding-bottom: 2rem; }
        .status-box { margin-bottom: 2rem; }
        .success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
        .error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
        pre { background: #f8f9fa; padding: 1rem; border-radius: 0.25rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">UNZANASA Voting System</h1>
                <h2 class="h4 mb-4">Database Setup</h2>
                <div class="card status-box">
                    <div class="card-body">
                        <h5 class="card-title">Setup Progress</h5>
                        <div class="progress mb-3" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="setupProgress"></div>
                        </div>
                        <div id="statusMessages">
                            <?php
                            // Database setup code will go here
                            $pdo = null;
                            try {
                                // Connect to MySQL server
                                $pdo = new PDO("mysql:host=$host", $user, $pass);
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                                
                                echo '<div class="alert alert-success">✓ Connected to MySQL server successfully</div>';
                                
                                // Create database if not exists
                                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
                                $pdo->exec("USE `$dbname`");
                                echo '<div class="alert alert-success">✓ Database created/selected successfully</div>';

                                // SQL to create tables
                                $sql = "
                                -- Admin table
                                CREATE TABLE IF NOT EXISTS admins (
                                    id INT PRIMARY KEY AUTO_INCREMENT,
                                    username VARCHAR(50) UNIQUE NOT NULL,
                                    password VARCHAR(255) NOT NULL,
                                    email VARCHAR(100) UNIQUE NOT NULL,
                                    full_name VARCHAR(100) NOT NULL,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    last_login TIMESTAMP NULL
                                );

                                -- Elections table
                                CREATE TABLE IF NOT EXISTS elections (
                                    id INT PRIMARY KEY AUTO_INCREMENT,
                                    title VARCHAR(100) NOT NULL,
                                    description TEXT,
                                    start_date DATETIME NOT NULL,
                                    end_date DATETIME NOT NULL,
                                    status ENUM('pending', 'active', 'completed') DEFAULT 'pending',
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                                );

                                -- Candidates table
                                CREATE TABLE IF NOT EXISTS candidates (
                                    id INT PRIMARY KEY AUTO_INCREMENT,
                                    election_id INT NOT NULL,
                                    name VARCHAR(100) NOT NULL,
                                    photo VARCHAR(255),
                                    bio TEXT,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
                                );

                                -- Votes table
                                CREATE TABLE IF NOT EXISTS votes (
                                    id INT PRIMARY KEY AUTO_INCREMENT,
                                    election_id INT NOT NULL,
                                    candidate_id INT NOT NULL,
                                    computer_number VARCHAR(20) NOT NULL,
                                    ip_address VARCHAR(45) NOT NULL,
                                    user_agent TEXT,
                                    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
                                    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
                                    UNIQUE KEY unique_vote (election_id, computer_number)
                                );
                                
                                -- Remember tokens for persistent login
                                CREATE TABLE IF NOT EXISTS remember_tokens (
                                    id INT PRIMARY KEY AUTO_INCREMENT,
                                    admin_id INT NOT NULL,
                                    token VARCHAR(64) NOT NULL,
                                    expires_at DATETIME NOT NULL,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    last_used TIMESTAMP NULL,
                                    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
                                    UNIQUE KEY unique_token (token)
                                );
                                
                                -- Create a default admin user (username: admin, password: admin123)
                                INSERT IGNORE INTO admins (username, password, email, full_name) 
                                VALUES ('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@unzanasa.com', 'System Administrator');
                                ";
                                
                                // Execute SQL
                                $pdo->exec($sql);
                                
                                echo '<div class="alert alert-success">✓ Database tables created successfully</div>';
                                echo '<div class="alert alert-success">✓ Default admin user created (username: admin, password: admin123)</div>';
                                echo '<div class="mt-4">';
                                echo '<a href="index.php" class="btn btn-primary">Go to Homepage</a>';
                                echo '</div>';
                                
                                // Update progress bar
                                echo '<script>document.getElementById("setupProgress").style.width = "100%";</script>';
                                
                            } catch(PDOException $e) {
                                echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Update progress bar as setup progresses
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.getElementById('setupProgress');
            let progress = 0;
            const interval = setInterval(function() {
                progress += 5;
                if (progress > 90) clearInterval(interval);
                progressBar.style.width = progress + '%';
            }, 300);
        });
    </script>
</body>
</html>
<?php
// Flush output buffer
ob_end_flush();
?>
