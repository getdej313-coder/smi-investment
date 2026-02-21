<?php
require_once 'config/session.php';
// Turn on error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get database credentials from environment variables (set in Render)
$host = getenv('DB_HOST') ?: 'bv3njkxmimdqq4eoqzrf-mysql.services.clever-cloud.com';
$dbname = getenv('DB_NAME') ?: 'bv3njkxmimdqq4eoqzrf';
$username = getenv('DB_USER') ?: 'ulgggqyehttxmpfvv';  // Fixed: added missing 'g'
$password = getenv('DB_PASSWORD') ?: '2rKYmSYmJ5tJmqUweAUO';
$port = getenv('DB_PORT') ?: '3306';

try {
    // Create PDO connection
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Test connection (optional - remove in production)
    $pdo->query("SELECT 1");
    
} catch(PDOException $e) {
    // Log error but don't expose sensitive data in production
    error_log("Database connection failed: " . $e->getMessage());
    
    // Show user-friendly message
    die("Connection failed. Please try again later.");
    
    // For debugging only (remove in production):
    // die("Connection failed: " . $e->getMessage() . " | Host: $host | DB: $dbname | User: $username");
}

// DO NOT start session here - sessions should be handled in config/session.php
// Remove these lines:
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// Make $pdo available globally
global $pdo;
?>

