<?php
require_once 'auth.php';
require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'] ?? 0;
$pdo->prepare("UPDATE users SET is_admin = 1 WHERE id = ?")->execute([$id]);
header("Location: users.php");
