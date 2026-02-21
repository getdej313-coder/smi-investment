<?php
// includes/auth.php - ULTRA SIMPLE
// DO NOT START SESSION HERE - already started in config/session.php

// If no user_id, redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// If we get here, user is logged in - do nothing else
?>
