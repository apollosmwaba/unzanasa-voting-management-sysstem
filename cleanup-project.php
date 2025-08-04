<?php
echo "<h2>🧹 Cleaning up UNZANASA Voting System Project</h2>";
echo "<p>Reorganizing files into proper MVC structure...</p>";

// Files to remove (duplicates and unnecessary files)
$filesToRemove = [
    '1manage-candidates.php',
    'create_admin.php',
    'db_con.php',
    'test-login.php',
    'upload-voters.php',
    'run-migrations.php',
    'application/views/add_candidate.php',
    'application/views/add_election.php',
    'application/views/admin_dashboard.php',
    'application/views/admin_login.php',
    'application/views/candidate_list.php',
    'application/views/election_list.php',
    'application/views/home.php',
    'application/views/upload_numbers.php',
    'application/views/voting_activity.php',
    'application/views/voting_form.php'
];

echo "<h3>📁 Files to be removed:</h3>";
echo "<ul>";
foreach ($filesToRemove as $file) {
    if (file_exists($file)) {
        echo "<li style='color: red;'>❌ $file</li>";
    } else {
        echo "<li style='color: gray;'>⚪ $file (not found)</li>";
    }
}
echo "</ul>";

// Core files to keep and organize
$coreFiles = [
    'Controllers' => [
        'AdminController.php',
        'VoteController.php',
        'ElectionController.php',
        'CandidateController.php',
        'ResultController.php'
    ],
    'Models' => [
        'Admin.php',
        'Election.php',
        'Candidate.php',
        'Vote.php',
        'Position.php',
        'ValidNumber.php'
    ],
    'Views' => [
        'admin/login.php',
        'admin/dashboard.php',
        'admin/manage-elections.php',
        'admin/manage-candidates.php',
        'admin/view-results.php',
        'voting/vote.php',
        'voting/results.php',
        'layouts/header.php',
        'layouts/footer.php'
    ]
];

echo "<h3>📂 Recommended MVC Structure:</h3>";
echo "<pre>";
echo "qqqq/\n";
echo "├── index.php (main entry point)\n";
echo "├── init.php (initialization)\n";
echo "├── .htaccess (URL rewriting)\n";
echo "├── config/\n";
echo "│   ├── database.php\n";
echo "│   └── app.php\n";
echo "├── app/\n";
echo "│   ├── controllers/\n";
foreach ($coreFiles['Controllers'] as $controller) {
    echo "│   │   ├── $controller\n";
}
echo "│   ├── models/\n";
foreach ($coreFiles['Models'] as $model) {
    echo "│   │   ├── $model\n";
}
echo "│   └── views/\n";
foreach ($coreFiles['Views'] as $view) {
    echo "│       ├── $view\n";
}
echo "├── public/\n";
echo "│   ├── css/\n";
echo "│   ├── js/\n";
echo "│   └── uploads/\n";
echo "└── database/\n";
echo "    ├── migrations/\n";
echo "    └── unzanasa_voting.sql\n";
echo "</pre>";

echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>⚠️ Manual Cleanup Required</h3>";
echo "<p>For safety reasons, please manually:</p>";
echo "<ol>";
echo "<li>Remove the unnecessary duplicate files listed above</li>";
echo "<li>Organize the remaining files into the MVC structure</li>";
echo "<li>Update file paths in the remaining files</li>";
echo "<li>Test the system after cleanup</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
echo "<h3>✅ Current Working Files:</h3>";
echo "<ul>";
echo "<li><strong>Admin Login:</strong> admin-login.php (Username: admin, Password: admin123)</li>";
echo "<li><strong>Voting Interface:</strong> vote.php</li>";
echo "<li><strong>Admin Dashboard:</strong> admin-dashboard.php</li>";
echo "<li><strong>Candidate Management:</strong> manage-candidates.php</li>";
echo "<li><strong>Election Management:</strong> manage-elections.php</li>";
echo "<li><strong>Results View:</strong> view-results.php</li>";
echo "</ul>";
echo "<p><a href='setup-test-data.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Setup Test Data First</a></p>";
echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>
