<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Require admin authentication
Auth::requireAuth();

// Initialize variables
$message = '';
$messageType = '';
$elections = [];

// Get all active elections for the dropdown
$electionModel = new Election();
try {
    $elections = $electionModel->getAllElections(['status' => 'active']);
} catch (Exception $e) {
    $message = 'Error loading elections: ' . $e->getMessage();
    $messageType = 'danger';
    error_log($message);
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['voters_file'])) {
    $electionId = (int)($_POST['election_id'] ?? 0);
    $file = $_FILES['voters_file'];
    
    // Validate input
    $errors = [];
    
    if ($electionId <= 0) {
        $errors[] = 'Please select an election';
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Error uploading file. Please try again.';
    } else {
        // Check file type (only allow CSV)
        $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($fileType !== 'csv') {
            $errors[] = 'Only CSV files are allowed.';
        }
        
        // Check file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'File is too large. Maximum size is 5MB.';
        }
    }
    
    // If no validation errors, process the file
    if (empty($errors)) {
        try {
            // Open the uploaded file
            $handle = fopen($file['tmp_name'], 'r');
            
            if ($handle === false) {
                throw new Exception('Failed to open the uploaded file.');
            }
            
            // Initialize counters
            $imported = 0;
            $skipped = 0;
            $lineNumber = 0;
            $voterModel = new Voter();
            
            // Read the file line by line
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $lineNumber++;
                
                // Skip header row
                if ($lineNumber === 1) {
                    continue;
                }
                
                // Expected columns: computer_number, full_name, email, phone (adjust as needed)
                if (count($data) < 2) {
                    $skipped++;
                    continue;
                }
                
                $computerNumber = trim($data[0]);
                $fullName = trim($data[1]);
                $email = isset($data[2]) ? trim($data[2]) : '';
                $phone = isset($data[3]) ? trim($data[3]) : '';
                
                // Basic validation
                if (empty($computerNumber) || empty($fullName)) {
                    $skipped++;
                    continue;
                }
                
                // Generate a random password
                $password = Utils::generateRandomString(8);
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Prepare voter data
                $voterData = [
                    'computer_number' => $computerNumber,
                    'full_name' => $fullName,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $hashedPassword,
                    'election_id' => $electionId,
                    'plain_password' => $password // For email purposes (not stored in DB)
                ];
                
                try {
                    // Check if voter already exists for this election
                    $existingVoter = $voterModel->getVoterByComputerNumber($computerNumber, $electionId);
                    
                    if ($existingVoter) {
                        // Update existing voter
                        $voterModel->updateVoter($existingVoter['id'], $voterData);
                    } else {
                        // Create new voter
                        $voterModel->createVoter($voterData);
                    }
                    
                    $imported++;
                    
                    // TODO: Send email to voter with login credentials
                    // $this->sendVoterCredentials($email, $fullName, $computerNumber, $password);
                    
                } catch (Exception $e) {
                    error_log("Error processing voter on line {$lineNumber}: " . $e->getMessage());
                    $skipped++;
                    continue;
                }
            }
            
            fclose($handle);
            
            // Set success message
            $message = "Successfully imported {$imported} voters. " . 
                      ($skipped > 0 ? "Skipped {$skipped} invalid or duplicate entries." : "");
            $messageType = 'success';
            
        } catch (Exception $e) {
            $message = 'Error processing file: ' . $e->getMessage();
            $messageType = 'danger';
            error_log($message);
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'danger';
    }
}

// Include the view
include __DIR__ . '/application/views/upload-voters.php';
?>
