<?php
// config/session.php
// This file MUST be included at the VERY TOP of every page that needs a session.

// Set a consistent, writable path for session files.
// Render's /tmp directory is writable.
$sessionPath = '/tmp';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

// Set secure cookie parameters.
// The domain should be your Render domain.
$domain = '.onrender.com'; // The leading dot allows it to work for all subdomains
session_set_cookie_params([
    'lifetime' => 86400 * 30, // 30 days
    'path' => '/',
    'domain' => $domain,
    'secure' => true,     // Only send over HTTPS
    'httponly' => true,   // Prevent JavaScript access
    'samesite' => 'Lax'
]);

// Start the session if it hasn't been started already.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
