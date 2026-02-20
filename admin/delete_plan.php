<?php
require_once 'auth.php'; // Ensures only logged-in admins can access

$plan_id = $_GET['id'] ?? 0;

if (!$plan_id) {
    header("Location: plans.php?error=Invalid plan ID");
    exit;
}

// Check if the plan exists
$stmt = $pdo->prepare("SELECT * FROM recharge_plans WHERE id = ?");
$stmt->execute([$plan_id]);
$plan = $stmt->fetch();

if (!$plan) {
    header("Location: plans.php?error=Plan not found");
    exit;
}

// Check for any active investments using this plan
$check = $pdo->prepare("SELECT COUNT(*) FROM user_investments WHERE plan_id = ? AND status = 'active'");
$check->execute([$plan_id]);
$active_count = $check->fetchColumn();

if ($active_count > 0) {
    // Cannot delete plan with active investments
    header("Location: plans.php?error=Cannot delete plan with active investments. Deactivate it instead.");
    exit;
}

// Also check for any investments (even inactive) – optional, but might want to prevent deletion if any history exists
$total_investments = $pdo->prepare("SELECT COUNT(*) FROM user_investments WHERE plan_id = ?");
$total_investments->execute([$plan_id]);
$total_count = $total_investments->fetchColumn();

if ($total_count > 0) {
    // Option 1: Allow deletion (investments will be orphaned? better not)
    // Option 2: Prevent deletion if any investment record exists
    // We'll choose to prevent deletion to keep data integrity.
    header("Location: plans.php?error=Cannot delete plan that has investment history. Please deactivate it instead.");
    exit;
}

// Perform deletion
try {
    $pdo->prepare("DELETE FROM recharge_plans WHERE id = ?")->execute([$plan_id]);
    header("Location: plans.php?success=Plan deleted successfully");
} catch (PDOException $e) {
    // If foreign key constraint fails, show appropriate message
    header("Location: plans.php?error=Failed to delete plan: " . $e->getMessage());
}
exit;
