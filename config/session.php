<?php
// config/session.php
// Set session save path
$sessionPath = '/tmp';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);

// Configure session cookie
session_set_cookie_params([
    'lifetime' => 86400 * 30,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_name('SMISESSION');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
