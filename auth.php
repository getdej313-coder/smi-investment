<?php
// includes/auth.php
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verify user still exists and is active
$stmt = $pdo->prepare("SELECT id, status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['status'] !== 'active') {
    session_destroy();
    header("Location: login.php?error=account_inactive");
    exit;
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
