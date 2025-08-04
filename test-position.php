<?php
require_once __DIR__ . '/init.php';

echo "<h2>Testing Position Class</h2>";

try {
    echo "<p>Creating Position instance...</p>";
    $positionModel = new Position();
    echo "<p>✓ Position instance created successfully</p>";
    
    echo "<p>Checking if getAllPositions method exists...</p>";
    if (method_exists($positionModel, 'getAllPositions')) {
        echo "<p>✓ getAllPositions method exists</p>";
        
        echo "<p>Calling getAllPositions()...</p>";
        $positions = $positionModel->getAllPositions();
        echo "<p>✓ getAllPositions() called successfully</p>";
        echo "<p>Found " . count($positions) . " positions</p>";
        
        if (!empty($positions)) {
            echo "<h3>Positions:</h3>";
            echo "<pre>" . print_r($positions, true) . "</pre>";
        }
    } else {
        echo "<p>✗ getAllPositions method does NOT exist</p>";
        echo "<p>Available methods:</p>";
        echo "<pre>" . print_r(get_class_methods($positionModel), true) . "</pre>";
    }
    
    echo "<p>Position class: " . get_class($positionModel) . "</p>";
    echo "<p>Position class file: " . (new ReflectionClass($positionModel))->getFileName() . "</p>";
    
} catch (Exception $e) {
    echo "<p>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>