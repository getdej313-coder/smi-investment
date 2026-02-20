<?php
require_once 'auth.php';
require_once __DIR__ . '/../config/database.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE id = ?");
$stmt->execute([$id]);
$w = $stmt->fetch();
if ($w && $w['status'] == 'pending') {
    // Update status to success
    $pdo->prepare("UPDATE withdrawals SET status = 'success' WHERE id = ?")->execute([$id]);
    // Deduct from user balance (since we didn't deduct at request)
    $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?")->execute([$w['amount'], $w['user_id']]);
}
header("Location: withdrawals.php");
