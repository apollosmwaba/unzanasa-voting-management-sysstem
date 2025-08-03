<?php
// Include the initialization file
require_once __DIR__ . '/init.php';

// Create migrations table if it doesn't exist
function ensureMigrationsTable($db) {
    $query = "CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        batch INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->query($query);
    return $db->execute();
}

// Get all migrations that have already been run
function getRanMigrations($db) {
    $db->query('SELECT migration FROM migrations ORDER BY batch, migration');
    $results = $db->resultSet();
    
    return array_map(function($item) {
        return $item->migration;
    }, $results);
}

// Get all migration files
function getMigrationFiles() {
    $migrations = [];
    $files = glob(__DIR__ . '/migrations/*.php');
    
    foreach ($files as $file) {
        $migrations[basename($file)] = $file;
    }
    
    ksort($migrations);
    return $migrations;
}

// Run the migrations
function runMigrations() {
    try {
        $db = new Database();
        
        // Ensure migrations table exists
        ensureMigrationsTable($db);
        
        // Get migrations that have already run
        $ranMigrations = getRanMigrations($db);
        
        // Get all migration files
        $migrations = getMigrationFiles();
        $pendingMigrations = array_diff_key($migrations, array_flip($ranMigrations));
        
        if (empty($pendingMigrations)) {
            echo "No migrations to run.\n";
            return;
        }
        
        // Get the next batch number
        $db->query('SELECT MAX(batch) as max_batch FROM migrations');
        $result = $db->single();
        $batch = ($result->max_batch ?? 0) + 1;
        
        // Run each pending migration
        foreach ($pendingMigrations as $migration => $path) {
            echo "Running migration: $migration\n";
            
            // Include the migration file
            $migrationClass = require $path;
            
            // Run the migration
            if ($migrationClass->up($db)) {
                // Record the migration
                $db->query('INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)');
                $db->bind(':migration', $migration);
                $db->bind(':batch', $batch);
                $db->execute();
                
                echo "Migration $migration completed successfully.\n";
            } else {
                throw new Exception("Migration $migration failed to run.");
            }
        }
        
        echo "All migrations completed successfully.\n";
        
    } catch (Exception $e) {
        echo "Error running migrations: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Run the migrations
runMigrations();
