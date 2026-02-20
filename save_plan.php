<?php
require_once 'auth.php';
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO recharge_plans (name, level, amount, daily_return, period_days, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['name'],
        $_POST['level'],
        $_POST['amount'],
        $_POST['daily_return'],
        $_POST['period_days'],
        $_POST['description']
    ]);
}
header("Location: plans.php");