<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Optional: Verify user exists in database
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        // User doesn't exist in database - clear session
        session_destroy();
        header("Location: ../login.php");
        exit();
    }
} catch (Exception $e) {
    // If database error, redirect to login
    session_destroy();
    header("Location: ../login.php");
    exit();
}
?>
