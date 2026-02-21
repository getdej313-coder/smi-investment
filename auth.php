<?php
// Always start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If no user_id, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
