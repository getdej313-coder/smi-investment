<?php
require_once 'config/database.php';

// Process daily earnings
$today = date('Y-m-d');
$pending_earnings = $pdo->prepare("SELECT de.*, ui.user_id FROM daily_earnings de JOIN user_investments ui ON de.investment_id = ui.id WHERE de.payout_date = ? AND de.status = 'pending'");
$pending_earnings->execute([$today]);
$earnings = $pending_earnings->fetchAll();

foreach ($earnings as $earning) {
    // Add to user balance
    $pdo->prepare("UPDATE users SET balance = balance + ?, total_earned = total_earned + ? WHERE id = ?")->execute([$earning['amount'], $earning['amount'], $earning['user_id']]);
    
    // Mark as paid
    $pdo->prepare("UPDATE daily_earnings SET status = 'paid' WHERE id = ?")->execute([$earning['id']]);
    
    // Update investment progress
    $pdo->prepare("UPDATE user_investments SET days_completed = days_completed + 1, total_earned = total_earned + ?, last_payout_date = NOW() WHERE id = ?")->execute([$earning['amount'], $earning['investment_id']]);
}

// Mark investments as completed
$pdo->query("UPDATE user_investments SET status = 'completed' WHERE DATE_ADD(start_date, INTERVAL period_days DAY) < NOW() AND status = 'active'");

echo "Daily payouts processed for " . count($earnings) . " earnings.\n";