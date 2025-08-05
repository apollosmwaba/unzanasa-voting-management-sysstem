<?php
// Test upload limit configuration
echo "<h2>PHP Upload Configuration Test</h2>";

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Setting</th><th>Current Value</th><th>Status</th></tr>";

$settings = [
    'upload_max_filesize' => '10M',
    'post_max_size' => '12M',
    'max_execution_time' => '300',
    'max_input_time' => '300'
];

foreach ($settings as $setting => $expected) {
    $current = ini_get($setting);
    $status = '';
    
    if ($setting === 'upload_max_filesize') {
        $currentBytes = return_bytes($current);
        $expectedBytes = return_bytes($expected);
        $status = ($currentBytes >= $expectedBytes) ? '✅ OK' : '❌ Too Low';
    } elseif ($setting === 'post_max_size') {
        $currentBytes = return_bytes($current);
        $expectedBytes = return_bytes($expected);
        $status = ($currentBytes >= $expectedBytes) ? '✅ OK' : '❌ Too Low';
    } else {
        $status = ($current >= intval($expected)) ? '✅ OK' : '❌ Too Low';
    }
    
    echo "<tr>";
    echo "<td><strong>$setting</strong></td>";
    echo "<td>$current</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int) $val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

echo "<br><p><strong>Note:</strong> If any settings show as 'Too Low', you may need to update your php.ini file or contact your hosting provider.</p>";
echo "<p><strong>Application Setting:</strong> The candidate management system now accepts files up to 10MB.</p>";
?>

<style>
table { border-collapse: collapse; margin: 20px 0; }
th, td { padding: 10px; text-align: left; }
th { background-color: #f5f5f5; }
</style>
