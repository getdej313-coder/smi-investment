<?php
// Get database credentials from environment variables (set in Render)
$host = getenv('DB_HOST') ?: 'bv3njkxmimdqq4eoqzrf-mysql.services.clever-cloud.com';
$dbname = getenv('DB_NAME') ?: 'bv3njkxmimdqq4eoqzrf';
$username = getenv('DB_USER') ?: 'ulgggqyehttxmpfvv';  // ← FIXED: added missing 'g'
$password = getenv('DB_PASSWORD') ?: '2rKYmSYmJ5tJmqUweAUO';
$port = getenv('DB_PORT') ?: '3306';

try {
    // Use port in connection if provided
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    // More detailed error for debugging
    die("Connection failed: " . $e->getMessage() . " | Host: $host | DB: $dbname | User: $username");
}

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
