<?php include __DIR__ . '/includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Upload Voters</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($message)): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        <h5><i class="bi bi-info-circle-fill"></i> Instructions</h5>
                        <p>Upload a CSV file containing voter information. The file should have the following columns in order:</p>
                        <ol>
                            <li>Computer Number (required)</li>
                            <li>Full Name (required)</li>
                            <li>Email (optional)</li>
                            <li>Phone (optional)</li>
                        </ol>
                        <p class="mb-0"><a href="/sample-voters.csv" class="btn btn-sm btn-outline-primary">Download Sample CSV</a></p>
                    </div>

                    <form action="upload-voters.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="election_id" class="form-label">Select Election</label>
                            <select class="form-select" id="election_id" name="election_id" required>
                                <option value="">-- Select Election --</option>
                                <?php foreach ($elections as $election): ?>
                                    <option value="<?php echo htmlspecialchars($election['id']); ?>">
                                        <?php echo htmlspecialchars($election['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="voters_file" class="form-label">CSV File</label>
                            <input class="form-control" type="file" id="voters_file" name="voters_file" accept=".csv" required>
                            <div class="form-text">Only .csv files are accepted. Max file size: 5MB</div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="manage-elections.php" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-arrow-left"></i> Back to Elections
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Upload Voters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>