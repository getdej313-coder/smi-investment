<?php
// config/session.php
// ALL INI settings go HERE only

// Turn on error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
