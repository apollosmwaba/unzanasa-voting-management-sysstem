<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug Position Class</h2>";

try {
    echo "<p>1. Including init.php...</p>";
    require_once __DIR__ . '/init.php';
    echo "<p>✓ init.php included successfully</p>";
    
    echo "<p>2. Checking if Position class exists...</p>";
    if (class_exists('Position')) {
        echo "<p>✓ Position class exists</p>";
        
        $reflection = new ReflectionClass('Position');
        echo "<p>Position class file: " . $reflection->getFileName() . "</p>";
        
        echo "<p>3. Creating Position instance...</p>";
        $position = new Position();
        echo "<p>✓ Position instance created</p>";
        
        echo "<p>4. Checking methods...</p>";
        $methods = get_class_methods($position);
        echo "<p>Available methods:</p><ul>";
        foreach ($methods as $method) {
            echo "<li>" . $method . "</li>";
        }
        echo "</ul>";
        
        echo "<p>5. Testing getAllPositions method...</p>";
        if (method_exists($position, 'getAllPositions')) {
            echo "<p>✓ getAllPositions method exists</p>";
            $positions = $position->getAllPositions();
            echo "<p>✓ getAllPositions called successfully</p>";
            echo "<p>Found " . count($positions) . " positions</p>";
        } else {
            echo "<p>❌ getAllPositions method does NOT exist</p>";
        }
        
    } else {
        echo "<p>❌ Position class does NOT exist</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>Autoloader Test</h3>";
echo "<p>Checking if Position.php file exists:</p>";
$positionFile = __DIR__ . '/application/models/Position.php';
if (file_exists($positionFile)) {
    echo "<p>✓ Position.php exists at: " . $positionFile . "</p>";
} else {
    echo "<p>❌ Position.php NOT found at: " . $positionFile . "</p>";
}
?>
