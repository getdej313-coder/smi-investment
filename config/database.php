<?php
// Turn on error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get database credentials from environment variables
$host = getenv('DB_HOST') ?: 'bv3njkxmimdqq4eoqzrf-mysql.services.clever-cloud.com';
$dbname = getenv('DB_NAME') ?: 'bv3njkxmimdqq4eoqzrf';
$username = getenv('DB_USER') ?: 'ulgggqyehttxmpfvv';
$password = getenv('DB_PASSWORD') ?: '2rKYmSYmJ5tJmqUweAUO';
$port = getenv('DB_PORT') ?: '3306';

// Initialize PDO variable
$pdo = null;
$db_error = null;

try {
    // Create PDO connection
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Silent connection test (no output)
    $pdo->query("SELECT 1");
    
} catch(PDOException $e) {
    // Log error only - NO OUTPUT TO BROWSER
    error_log("Database connection failed: " . $e->getMessage());
    
    // Set error flag instead of dying
    $db_error = true;
    
    // DO NOT use die() or echo here - it will break headers
}

// Make $pdo available globally
global $pdo, $db_error;

// NO OUTPUT, NO DIE, NO ECHO HERE!
?>
