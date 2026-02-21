<?php
session_start();
echo "<h1>Session Test on Different Pages</h1>";
echo "<h2>Current Page: " . basename($_SERVER['PHP_SELF']) . "</h2>";
echo "<h3>Session ID: " . session_id() . "</h3>";
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>Links to test:</h3>";
echo "<a href='home.php'>home.php</a><br>";
echo "<a href='profile.php'>profile.php</a><br>";
echo "<a href='team.php'>team.php</a><br>";
echo "<a href='product.php'>product.php</a><br>";
echo "<a href='official.php'>official.php</a><br>";
?>
