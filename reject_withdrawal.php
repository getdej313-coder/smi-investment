<?php
require_once 'auth.php';
require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'] ?? 0;
$pdo->prepare("UPDATE withdrawals SET status = 'failed' WHERE id = ?")->execute([$id]);
header("Location: withdrawals.php");