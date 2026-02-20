<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];
$tx_id = $_GET['id'] ?? 0;

// Get transaction details based on type
if (isset($_GET['type']) && $_GET['type'] == 'investment') {
    $stmt = $pdo->prepare("
        SELECT ui.*, rp.name as plan_name, rp.daily_return, rp.period_days 
        FROM user_investments ui 
        JOIN recharge_plans rp ON ui.plan_id = rp.id 
        WHERE ui.id = ? AND ui.user_id = ?
    ");
    $stmt->execute([$tx_id, $user_id]);
    $tx = $stmt->fetch();
    $type = 'investment';
} else {
    // Default to recent transaction from records
    $tx = [
        'id' => $tx_id,
        'type' => 'recharge',
        'amount' => 2060,
        'created_at' => date('Y-m-d H:i:s'),
        'status' => 'completed',
        'plan_name' => 'Quick Recharge',
        'payment_method' => 'Bank Transfer',
        'reference' => 'TXN' . rand(100000, 999999)
    ];
    $type = $_GET['type'] ?? 'recharge';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transaction Details - Smi Investment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.6rem; }
        .back-btn { color:#fbbf24; text-decoration:none; font-size:1.2rem; }
        .status-badge { display:inline-block; padding:8px 20px; border-radius:30px; font-weight:600; margin-bottom:20px; }
        .status-completed { background:#163a30; color:#4ade80; }
        .status-pending { background:#5b4a1a; color:#fbbf24; }
        .status-failed { background:#2d1f1f; color:#f87171; }
        .amount-card { text-align:center; margin-bottom:30px; }
        .amount-label { color:#a5b4cb; font-size:0.9rem; }
        .amount-value { color:white; font-size:3rem; font-weight:700; margin:10px 0; }
        .amount-currency { color:#fbbf24; font-size:1.2rem; }
        .details-card { background:#1e2a3a; border-radius:30px; padding:20px; margin-bottom:20px; }
        .detail-row { display:flex; justify-content:space-between; padding:15px 0; border-bottom:1px solid #2d3a4b; }
        .detail-row:last-child { border-bottom:none; }
        .detail-label { color:#a5b4cb; }
        .detail-value { color:white; font-weight:500; }
        .detail-value.highlight { color:#fbbf24; }
        .actions { display:flex; gap:10px; margin-top:20px; }
        .action-btn { flex:1; padding:15px; border-radius:30px; text-align:center; text-decoration:none; font-weight:600; }
        .btn-primary { background:#fbbf24; color:#0b1424; }
        .btn-secondary { background:#1e2a3a; color:white; border:1px solid #2d3a4b; }
        .bottom-nav { position:absolute; bottom:0; left:0; right:0; background:#0f1a28; display:flex; justify-content:space-around; padding:12px 16px 20px; border-top:1px solid #263340; border-radius:30px 30px 0 0; }
        .nav-item { display:flex; flex-direction:column; align-items:center; color:#6b7e99; font-size:0.7rem; text-decoration:none; }
        .nav-item i { font-size:1.4rem; margin-bottom:4px; }
        .nav-item.active { color:#fbbf24; }
    </style>
</head>
<body>
    <div class="phone-frame">
        <div class="page-header">
            <a href="javascript:history.back()" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            <h2>Details</h2>
            <a href="#" style="color:#fbbf24;"><i class="fas fa-print"></i></a>
        </div>
        
        <div style="text-align:center;">
            <span class="status-badge status-<?= $tx['status'] ?>">
                <i class="fas fa-<?= $tx['status'] == 'completed' ? 'check-circle' : ($tx['status'] == 'pending' ? 'clock' : 'times-circle') ?>"></i>
                <?= ucfirst($tx['status']) ?>
            </span>
        </div>
        
        <div class="amount-card">
            <div class="amount-label">Total Amount</div>
            <div class="amount-value"><?= number_format($tx['amount'], 2) ?></div>
            <div class="amount-currency">ETB</div>
        </div>
        
        <div class="details-card">
            <div class="detail-row">
                <span class="detail-label">Transaction ID</span>
                <span class="detail-value">#<?= $tx['id'] ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Transaction Type</span>
                <span class="detail-value highlight"><?= ucfirst($type) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date & Time</span>
                <span class="detail-value"><?= date('M d, Y H:i', strtotime($tx['created_at'])) ?></span>
            </div>
            <?php if($type == 'investment'): ?>
            <div class="detail-row">
                <span class="detail-label">Plan</span>
                <span class="detail-value"><?= htmlspecialchars($tx['plan_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Daily Return</span>
                <span class="detail-value">ETB <?= number_format($tx['daily_return'], 2) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Period</span>
                <span class="detail-value"><?= $tx['period_days'] ?> Days</span>
            </div>
            <?php endif; ?>
            <div class="detail-row">
                <span class="detail-label">Payment Method</span>
                <span class="detail-value"><?= $tx['payment_method'] ?? 'Bank Transfer' ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Reference</span>
                <span class="detail-value"><?= $tx['reference'] ?? 'N/A' ?></span>
            </div>
        </div>
        
        <div class="actions">
            <a href="#" class="action-btn btn-secondary"><i class="fas fa-download"></i> Receipt</a>
            <a href="#" class="action-btn btn-primary"><i class="fas fa-headset"></i> Support</a>
        </div>
        
        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <a href="home.php" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="product.php" class="nav-item"><i class="fas fa-cube"></i><span>Product</span></a>
            <a href="official.php" class="nav-item"><i class="fas fa-bullhorn"></i><span>Official</span></a>
            <a href="team.php" class="nav-item"><i class="fas fa-users"></i><span>Team</span></a>
            <a href="profile.php" class="nav-item"><i class="fas fa-user"></i><span>Mine</span></a>
        </div>
    </div>
</body>
</html>