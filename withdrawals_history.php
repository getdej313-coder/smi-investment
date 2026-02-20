<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Get user's withdrawals
$stmt = $pdo->prepare("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$withdrawals = $stmt->fetchAll();

// Calculate totals
$total_withdrawn = array_sum(array_column($withdrawals, 'amount'));
$pending_withdrawals = array_sum(array_column(array_filter($withdrawals, fn($w) => $w['status'] == 'pending'), 'amount'));
?>
<!DOCTYPE html>
<html>
<head>
    <title>Withdrawals History - Smi Investment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.6rem; }
        .back-btn { color:#fbbf24; text-decoration:none; font-size:1.2rem; }
        .stats-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px; }
        .stat-card { background:#1e2a3a; border-radius:20px; padding:16px; text-align:center; }
        .stat-value { color:#fbbf24; font-size:1.4rem; font-weight:700; }
        .stat-label { color:#a5b4cb; font-size:0.8rem; }
        .filter-tabs { display:flex; gap:10px; margin-bottom:20px; }
        .filter-tab { flex:1; text-align:center; padding:12px; background:#1e2a3a; border-radius:20px; color:#a5b4cb; text-decoration:none; }
        .filter-tab.active { background:#fbbf24; color:#0b1424; }
        .withdrawal-item { background:#1e2a3a; border-radius:20px; padding:16px; margin-bottom:12px; display:flex; align-items:center; gap:12px; }
        .withdrawal-icon { width:45px; height:45px; border-radius:23px; background:#273649; display:flex; align-items:center; justify-content:center; color:#fbbf24; }
        .withdrawal-details { flex:1; }
        .withdrawal-amount { color:white; font-weight:600; font-size:1.1rem; }
        .withdrawal-date { color:#a5b4cb; font-size:0.8rem; }
        .withdrawal-status { padding:4px 12px; border-radius:20px; font-size:0.8rem; }
        .status-success { background:#163a30; color:#4ade80; }
        .status-pending { background:#5b4a1a; color:#fbbf24; }
        .status-failed { background:#2d1f1f; color:#f87171; }
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
            <h2>Withdrawals</h2>
            <a href="withdraw.php" style="color:#fbbf24;"><i class="fas fa-plus-circle"></i></a>
        </div>
        
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-value">ETB <?= number_format($total_withdrawn, 2) ?></div>
                <div class="stat-label">Total Withdrawn</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">ETB <?= number_format($pending_withdrawals, 2) ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        
        <div class="filter-tabs">
            <a href="?filter=all" class="filter-tab <?= !isset($_GET['filter']) || $_GET['filter']=='all' ? 'active' : '' ?>">All</a>
            <a href="?filter=pending" class="filter-tab <?= isset($_GET['filter']) && $_GET['filter']=='pending' ? 'active' : '' ?>">Pending</a>
            <a href="?filter=success" class="filter-tab <?= isset($_GET['filter']) && $_GET['filter']=='success' ? 'active' : '' ?>">Success</a>
        </div>
        
        <?php 
        $filter = $_GET['filter'] ?? 'all';
        $filtered = $withdrawals;
        if($filter != 'all') {
            $filtered = array_filter($withdrawals, fn($w) => $w['status'] == $filter);
        }
        
        if(empty($filtered)): 
        ?>
        <div style="text-align:center; color:#a5b4cb; padding:40px 0;">
            <i class="fas fa-history" style="font-size:3rem; margin-bottom:20px;"></i>
            <p>No withdrawal history</p>
            <a href="withdraw.php" style="color:#fbbf24; text-decoration:none; margin-top:20px; display:block;">Request Withdrawal →</a>
        </div>
        <?php else: ?>
            <?php foreach ($filtered as $w): ?>
            <a href="transaction_details.php?id=<?= $w['id'] ?>&type=withdrawal" style="text-decoration:none;">
                <div class="withdrawal-item">
                    <div class="withdrawal-icon">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div class="withdrawal-details">
                        <div class="withdrawal-amount">ETB <?= number_format($w['amount'], 2) ?></div>
                        <div class="withdrawal-date"><?= date('M d, Y H:i', strtotime($w['created_at'])) ?></div>
                    </div>
                    <div class="withdrawal-status status-<?= $w['status'] ?>">
                        <?= ucfirst($w['status']) ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
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