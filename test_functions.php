<?php
// test_functions.php
require_once 'includes/functions.php';

echo "Testing functions.php<br>";

// Check if any output was produced
$output = ob_get_clean();
if (!empty($output)) {
    echo "Output detected: " . htmlspecialchars($output);
} else {
    echo "No output detected from functions.php";
}
?>
