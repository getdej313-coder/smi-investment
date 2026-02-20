<?php
$host = 'localhost';
$dbname = 'smi_investment';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>