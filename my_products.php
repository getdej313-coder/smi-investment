<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Get user's investments (products)
$stmt = $pdo->prepare("
    SELECT ui.*, rp.name as plan_name, rp.daily_return, rp.period_days 
    FROM user_investments ui 
    JOIN recharge_plans rp ON ui.plan_id = rp.id 
    WHERE ui.user_id = ? 
    ORDER BY ui.created_at DESC
");
$stmt->execute([$user_id]);
$investments = $stmt->fetchAll();

// Get total invested
$total_invested = array_sum(array_column($investments, 'amount'));
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Products - Smi Investment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.6rem; }
        .back-btn { color:#fbbf24; text-decoration:none; font-size:1.2rem; }
        .summary-card { background:#1e2a3a; border-radius:20px; padding:16px; margin-bottom:24px; border:1px solid #2d3a4b; display:flex; justify-content:space-around; }
        .summary-item { text-align:center; }
        .summary-value { color:#fbbf24; font-size:1.2rem; font-weight:700; }
        .summary-label { color:#a5b4cb; font-size:0.8rem; }
        .product-tabs { display:flex; gap:10px; margin-bottom:20px; }
        .tab { flex:1; text-align:center; padding:12px; background:#1e2a3a; border-radius:20px; color:#a5b4cb; text-decoration:none; }
        .tab.active { background:#fbbf24; color:#0b1424; font-weight:600; }
        .product-card { background:#1e2a3a; border-radius:24px; padding:18px; margin-bottom:16px; border:1px solid #2d3a4b; }
        .product-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
        .product-name { color:#fbbf24; font-weight:600; font-size:1.1rem; }
        .product-status { padding:4px 12px; border-radius:20px; font-size:0.8rem; }
        .status-active { background:#163a30; color:#4ade80; }
        .status-completed { background:#2d3748; color:#a5b4cb; }
        .product-details { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin:15px 0; }
        .detail-item { background:#0f1a28; padding:10px; border-radius:15px; text-align:center; }
        .detail-label { color:#a5b4cb; font-size:0.7rem; }
        .detail-value { color:white; font-weight:600; }
        .progress-bar { height:8px; background:#0f1a28; border-radius:4px; margin:10px 0; }
        .progress-fill { height:8px; background:#fbbf24; border-radius:4px; }
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
            <h2>My Products</h2>
            <a href="product.php" style="color:#fbbf24;"><i class="fas fa-plus-circle"></i></a>
        </div>
        
        <div class="summary-card">
            <div class="summary-item">
                <div class="summary-value"><?= count($investments) ?></div>
                <div class="summary-label">Total Products</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">ETB <?= number_format($total_invested, 2) ?></div>
                <div class="summary-label">Total Invested</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">
                    <?= count(array_filter($investments, fn($i) => $i['status'] == 'active')) ?>
                </div>
                <div class="summary-label">Active</div>
            </div>
        </div>
        
        <div class="product-tabs">
            <a href="?filter=all" class="tab <?= !isset($_GET['filter']) || $_GET['filter']=='all' ? 'active' : '' ?>">All</a>
            <a href="?filter=active" class="tab <?= isset($_GET['filter']) && $_GET['filter']=='active' ? 'active' : '' ?>">Active</a>
            <a href="?filter=completed" class="tab <?= isset($_GET['filter']) && $_GET['filter']=='completed' ? 'active' : '' ?>">Completed</a>
        </div>
        
        <?php 
        $filter = $_GET['filter'] ?? 'all';
        $filtered = $investments;
        if($filter == 'active') {
            $filtered = array_filter($investments, fn($i) => $i['status'] == 'active');
        } elseif($filter == 'completed') {
            $filtered = array_filter($investments, fn($i) => $i['status'] == 'completed');
        }
        
        if(empty($filtered)): 
        ?>
        <div style="text-align:center; color:#a5b4cb; padding:40px 0;">
            <i class="fas fa-box-open" style="font-size:3rem; margin-bottom:20px;"></i>
            <p>No products found</p>
            <a href="product.php" style="color:#fbbf24; text-decoration:none; margin-top:20px; display:block;">Browse Products →</a>
        </div>
        <?php else: ?>
            <?php foreach ($filtered as $inv): 
                $progress = ($inv['days_completed'] / $inv['period_days']) * 100;
            ?>
            <div class="product-card">
                <div class="product-header">
                    <span class="product-name"><?= htmlspecialchars($inv['plan_name']) ?></span>
                    <span class="product-status status-<?= $inv['status'] ?>"><?= ucfirst($inv['status']) ?></span>
                </div>
                
                <div class="product-details">
                    <div class="detail-item">
                        <div class="detail-label">Invested</div>
                        <div class="detail-value">ETB <?= number_format($inv['amount'], 2) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Earned</div>
                        <div class="detail-value">ETB <?= number_format($inv['total_earned'], 2) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Daily</div>
                        <div class="detail-value">ETB <?= number_format($inv['daily_return'], 2) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Profit</div>
                        <div class="detail-value" style="color:#4ade80;">
                            <?= $inv['total_earned'] - $inv['amount'] > 0 ? '+' : '' ?>
                            ETB <?= number_format($inv['total_earned'] - $inv['amount'], 2) ?>
                        </div>
                    </div>
                </div>
                
                <div style="display:flex; justify-content:space-between; color:#a5b4cb; font-size:0.8rem; margin-bottom:5px;">
                    <span>Day <?= $inv['days_completed'] ?>/<?= $inv['period_days'] ?></span>
                    <span><?= round($progress) ?>% Complete</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
                </div>
                
                <a href="transaction_details.php?id=<?= $inv['id'] ?>" style="display:block; text-align:center; color:#fbbf24; text-decoration:none; margin-top:15px;">
                    View Details <i class="fas fa-chevron-right"></i>
                </a>
            </div>
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