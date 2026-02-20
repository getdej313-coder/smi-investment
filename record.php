<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Get all transactions (recharges, withdrawals, earnings)
$recharges = $pdo->prepare("SELECT 'recharge' as type, amount, created_at, 'completed' as status FROM recharges WHERE user_id = ?");
$recharges->execute([$user_id]);

$withdrawals = $pdo->prepare("SELECT 'withdrawal' as type, amount, created_at, status FROM withdrawals WHERE user_id = ?");
$withdrawals->execute([$user_id]);

$earnings = $pdo->prepare("SELECT 'earning' as type, amount, created_at, 'paid' as status FROM daily_earnings WHERE user_id = ?");
$earnings->execute([$user_id]);

// Merge and sort by date
$transactions = array_merge($recharges->fetchAll(), $withdrawals->fetchAll(), $earnings->fetchAll());
usort($transactions, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));

// Pagination
$page = $_GET['page'] ?? 1;
$per_page = 10;
$total = count($transactions);
$pages = ceil($total / $per_page);
$offset = ($page - 1) * $per_page;
$transactions = array_slice($transactions, $offset, $per_page);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transaction History - Smi Investment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.6rem; }
        .back-btn { color:#fbbf24; text-decoration:none; font-size:1.2rem; }
        .filter-bar { display:flex; gap:10px; margin-bottom:20px; }
        .filter-select { flex:1; background:#1e2a3a; color:white; border:1px solid #2d3a4b; border-radius:20px; padding:12px; }
        .transaction-list { display:flex; flex-direction:column; gap:12px; margin-bottom:20px; }
        .transaction-item { background:#1e2a3a; border-radius:20px; padding:16px; display:flex; align-items:center; gap:12px; border:1px solid #2d3a4b; }
        .tx-icon { width:45px; height:45px; border-radius:23px; display:flex; align-items:center; justify-content:center; }
        .tx-recharge { background:#163a30; color:#4ade80; }
        .tx-withdrawal { background:#5b1a1a; color:#f87171; }
        .tx-earning { background:#5b4a1a; color:#fbbf24; }
        .tx-details { flex:1; }
        .tx-type { color:white; font-weight:600; }
        .tx-date { color:#a5b4cb; font-size:0.8rem; }
        .tx-amount { font-weight:700; }
        .tx-recharge-amount { color:#4ade80; }
        .tx-withdrawal-amount { color:#f87171; }
        .tx-earning-amount { color:#fbbf24; }
        .tx-status { font-size:0.7rem; padding:4px 8px; border-radius:20px; background:#0f1a28; }
        .pagination { display:flex; justify-content:center; gap:10px; margin-top:20px; }
        .page-link { padding:8px 12px; background:#1e2a3a; color:white; text-decoration:none; border-radius:10px; }
        .page-link.active { background:#fbbf24; color:#0b1424; }
        .bottom-nav { position:absolute; bottom:0; left:0; right:0; background:#0f1a28; display:flex; justify-content:space-around; padding:12px 16px 20px; border-top:1px solid #263340; border-radius:30px 30px 0 0; }
        .nav-item { display:flex; flex-direction:column; align-items:center; color:#6b7e99; font-size:0.7rem; text-decoration:none; }
        .nav-item i { font-size:1.4rem; margin-bottom:4px; }
        .nav-item.active { color:#fbbf24; }
    </style>
</head>
<body>
    <div class="phone-frame">
        <div class="page-header">
            <a href="home.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            <h2>Records</h2>
            <a href="#" style="color:#fbbf24;"><i class="fas fa-download"></i></a>
        </div>
        
        <div class="filter-bar">
            <select class="filter-select" onchange="window.location.href='?filter='+this.value">
                <option value="all">All Transactions</option>
                <option value="recharge">Recharges</option>
                <option value="withdrawal">Withdrawals</option>
                <option value="earning">Earnings</option>
            </select>
        </div>
        
        <div class="transaction-list">
            <?php if(empty($transactions)): ?>
                <div style="text-align:center; color:#a5b4cb; padding:40px 0;">
                    <i class="fas fa-history" style="font-size:3rem; margin-bottom:20px;"></i>
                    <p>No transactions found</p>
                </div>
            <?php else: ?>
                <?php foreach ($transactions as $tx): ?>
                <div class="transaction-item">
                    <div class="tx-icon tx-<?= $tx['type'] ?>">
                        <i class="fas <?= $tx['type'] == 'recharge' ? 'fa-arrow-up' : ($tx['type'] == 'withdrawal' ? 'fa-arrow-down' : 'fa-coins') ?>"></i>
                    </div>
                    <div class="tx-details">
                        <div class="tx-type"><?= ucfirst($tx['type']) ?></div>
                        <div class="tx-date"><?= date('M d, Y H:i', strtotime($tx['created_at'])) ?></div>
                    </div>
                    <div style="text-align:right;">
                        <div class="tx-amount tx-<?= $tx['type'] ?>-amount">
                            <?= $tx['type'] == 'withdrawal' ? '-' : '+' ?> ETB <?= number_format($tx['amount'], 2) ?>
                        </div>
                        <div class="tx-status" style="color:<?= $tx['status'] == 'completed' || $tx['status'] == 'paid' ? '#4ade80' : '#fbbf24' ?>">
                            <?= ucfirst($tx['status']) ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if($pages > 1): ?>
        <div class="pagination">
            <?php for($i=1; $i<=$pages; $i++): ?>
            <a href="?page=<?= $i ?>" class="page-link <?= $i==$page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
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