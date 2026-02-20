<?php
// debug.php - place in root directory
session_start();

echo "<h1>🔍 Session Debug</h1>";

echo "<h2>Session ID:</h2>";
echo session_id() ?: "No session ID";
echo "<br><br>";

echo "<h2>Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Cookies:</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

echo "<h2>Links:</h2>";
echo "<a href='login.php'>Login Page</a><br>";
echo "<a href='home.php'>Home Page</a><br>";
echo "<a href='logout.php'>Logout</a><br>";

echo "<h2>Clear Session:</h2>";
echo "<a href='debug.php?clear=1'>Clear Session</a>";

if (isset($_GET['clear'])) {
    session_destroy();
    echo "<p style='color:red'>Session destroyed!</p>";
}
?>
