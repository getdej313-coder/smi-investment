<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get investment earnings (total earned from investments)
$investment_earnings = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM daily_earnings WHERE user_id = ? AND status = 'paid'");
$investment_earnings->execute([$user_id]);
$investment_total = $investment_earnings->fetch()['total'];

// Get referral earnings
$referral_earnings = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM referral_earnings WHERE user_id = ?");
$referral_earnings->execute([$user_id]);
$referral_total = $referral_earnings->fetch()['total'];

$bonus_total = 0;

// Calculate total earnings
$total_earnings = $investment_total + $referral_total + $bonus_total;

// Get today's earnings
$today_earnings = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM daily_earnings WHERE user_id = ? AND DATE(payout_date) = CURDATE() AND status = 'paid'");
$today_earnings->execute([$user_id]);
$today_total = $today_earnings->fetch()['total'];

// Get active investments count
$active_investments = $pdo->prepare("SELECT COUNT(*) as count FROM user_investments WHERE user_id = ? AND status = 'active'");
$active_investments->execute([$user_id]);
$active_count = $active_investments->fetch()['count'];

// Get referral statistics
$ref_stats = $pdo->prepare("SELECT 
    COUNT(*) as total_referrals,
    COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) as active_referrals
    FROM users WHERE referred_by = ?");
$ref_stats->execute([$user_id]);
$referral_stats = $ref_stats->fetch();

// Get live withdrawals - ONLY SUCCESSFUL ones
$withdrawals = $pdo->query("SELECT phone_masked, amount, status FROM withdrawals WHERE status = 'success' ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Smi Investment - Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }
        body { background:#0b1424; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; box-shadow:0 25px 50px -12px rgba(0,0,0,0.8); margin:0 auto; }
        .welcome-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
        .greeting h2 { color:white; font-size:1.6rem; }
        .greeting p { color:#9ca8b9; font-size:0.9rem; }
        .profile-icon { background:#1e2a3a; width:48px; height:48px; border-radius:24px; display:flex; align-items:center; justify-content:center; color:#fbbf24; border:1px solid #334155; cursor:pointer; transition:0.2s; }
        .profile-icon:hover { transform:scale(1.05); background:#273649; }
        .profile-icon a { color:#fbbf24; text-decoration:none; }
        .balance-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; margin-bottom:20px; }
        .balance-card { background:#1e2a3a; border-radius:20px; padding:16px; border:1px solid #2d3a4b; box-shadow:0 4px 0 #0f172a; }
        .balance-label { color:#a5b4cb; font-size:0.75rem; margin-bottom:5px; }
        .balance-value { color:#fbbf24; font-size:1.3rem; font-weight:700; }
        .total-earnings-card { background:linear-gradient(135deg,#1e4b5e,#12303e); border-radius:24px; padding:20px; margin-bottom:20px; border:1px solid #fbbf24; display:flex; justify-content:space-between; align-items:center; }
        .total-earnings-label { color:#a5b4cb; font-size:0.9rem; }
        .total-earnings-value { color:#fbbf24; font-size:2rem; font-weight:700; }
        .total-earnings-sub { color:#4ade80; font-size:0.8rem; margin-top:5px; }
        .stats-row { display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; margin-bottom:20px; }
        .stat-card { background:#1e2a3a; border-radius:18px; padding:12px; text-align:center; border:1px solid #2d3a4b; }
        .stat-value { color:#fbbf24; font-size:1.2rem; font-weight:700; }
        .stat-label { color:#a5b4cb; font-size:0.7rem; margin-top:5px; }
        .action-buttons { display:flex; gap:12px; margin-bottom:20px; }
        .recharge-btn { flex:1; background:linear-gradient(105deg,#1e4b5e,#12303e); border:none; border-radius:40px; padding:16px; color:white; text-align:center; text-decoration:none; font-weight:700; font-size:1.2rem; display:flex; align-items:center; justify-content:center; gap:10px; border:1px solid #fbbf24; box-shadow:0 6px 0 #0f172a; transition:0.2s; }
        .recharge-btn:hover { transform:translateY(-2px); background:linear-gradient(105deg,#1b5f72,#164c5e); }
        .withdraw-btn { flex:1; background:#1e2a3a; border:none; border-radius:40px; padding:16px; color:white; text-align:center; text-decoration:none; font-weight:600; font-size:1.2rem; display:flex; align-items:center; justify-content:center; gap:10px; border:1px solid #2d3a4b; box-shadow:0 6px 0 #0f172a; transition:0.2s; }
        .withdraw-btn:hover { transform:translateY(-2px); background:#273649; }
        .action-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:28px; }
        .action-item { background:#1e2a3a; border-radius:20px; padding:14px 4px; text-align:center; color:white; border:1px solid #2d3a4b; box-shadow:0 6px 0 #0f172a; text-decoration:none; display:block; transition:0.2s; }
        .action-item:hover { transform:translateY(-2px); background:#273649; }
        .action-item i { font-size:1.6rem; color:#fbbf24; margin-bottom:6px; display:block; }
        .action-item span { font-size:0.8rem; font-weight:500; }
        .shortcut-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:28px; }
        .shortcut-item { text-align:center; color:#b9c7da; font-size:0.75rem; text-decoration:none; display:block; }
        .shortcut-item i { font-size:1.3rem; background:#1e2a3a; padding:12px; border-radius:30px; color:#fbbf24; margin-bottom:6px; display:inline-block; width:48px; height:48px; line-height:24px; border:1px solid #334155; transition:0.2s; }
        .shortcut-item:hover i { background:#273649; transform:scale(1.05); }
        .recent-card { background:#1e2a3a; border-radius:26px; padding:18px; margin-bottom:28px; border:1px solid #2d3a4b; box-shadow:0 8px 0 #0f172a; cursor:pointer; text-decoration:none; display:block; transition:0.2s; }
        .recent-card:hover { transform:translateY(-2px); background:#273649; }
        .recent-title { color:#a5b4cb; font-size:0.8rem; margin-bottom:12px; }
        .transaction-row { display:flex; align-items:center; justify-content:space-between; color:white; }
        .tx-left { display:flex; align-items:center; gap:12px; }
        .tx-icon { background:#2d3e54; width:46px; height:46px; border-radius:23px; display:flex; align-items:center; justify-content:center; color:#fbbf24; }
        .tx-info h4 { font-size:1rem; }
        .tx-info p { font-size:0.8rem; color:#9aaec5; }
        .tx-amount .value { font-size:1.3rem; font-weight:700; color:#fbbf24; }
        .section-header { display:flex; justify-content:space-between; color:white; margin-bottom:16px; }
        .section-header a { color:#fbbf24; text-decoration:none; }
        .withdrawal-list { display:flex; flex-direction:column; gap:12px; margin-bottom:30px; }
        .withdrawal-item { background:#1e2a3a; border-radius:24px; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; border:1px solid #2d3a4b; box-shadow:0 4px 0 #0f172a; transition:0.2s; }
        .withdrawal-item:hover { background:#273649; transform:translateY(-2px); }
        .withdrawal-left { display:flex; align-items:center; gap:12px; }
        .phone-mask { font-size:1rem; font-weight:600; color:white; background:#273649; padding:8px 14px; border-radius:40px; }
        .details .amount { font-size:1.2rem; font-weight:700; color:white; }
        .status { background:#163a30; padding:6px 14px; border-radius:40px; color:#4ade80; font-size:0.8rem; font-weight:600; border:1px solid #2c7a5a; }
        .bottom-nav { position:absolute; bottom:0; left:0; right:0; background:#0f1a28; display:flex; justify-content:space-around; padding:12px 16px 20px; border-top:1px solid #263340; border-radius:30px 30px 0 0; }
        .nav-item { display:flex; flex-direction:column; align-items:center; color:#6b7e99; font-size:0.7rem; text-decoration:none; transition:0.2s; }
        .nav-item i { font-size:1.4rem; margin-bottom:4px; }
        .nav-item.active { color:#fbbf24; }
        .nav-item:hover { color:#fbbf24; transform:translateY(-2px); }
        .view-all { color:#fbbf24; text-decoration:none; font-size:0.9rem; }
    </style>
</head>
<body>
    <div class="phone-frame">
        <div class="welcome-row">
            <div class="greeting">
                <h2>Home</h2>
                <p><i class="fas fa-wallet"></i> Balance: <strong>ETB <?= number_format($user['balance'], 2) ?></strong></p>
            </div>
            <a href="profile.php" class="profile-icon">
                <i class="fas fa-user"></i>
            </a>
        </div>

        <!-- Total Earnings Card -->
        <div class="total-earnings-card">
            <div>
                <div class="total-earnings-label">Total Lifetime Earnings</div>
                <div class="total-earnings-value">ETB <?= number_format($total_earnings, 2) ?></div>
                <div class="total-earnings-sub">ETB <?= number_format($today_total, 2) ?> earned today</div>
            </div>
            <i class="fas fa-chart-line" style="color:#fbbf24; font-size:2rem;"></i>
        </div>

        <!-- Balance Grid -->
        <div class="balance-grid">
            <div class="balance-card">
                <div class="balance-label">Today's Income</div>
                <div class="balance-value">ETB <?= number_format($today_total, 2) ?></div>
            </div>
            <div class="balance-card">
                <div class="balance-label">Investment Earnings</div>
                <div class="balance-value">ETB <?= number_format($investment_total, 2) ?></div>
            </div>
            <div class="balance-card">
                <div class="balance-label">Referral Earnings</div>
                <div class="balance-value">ETB <?= number_format($referral_total, 2) ?></div>
            </div>
            <div class="balance-card">
                <div class="balance-label">Bonus Earnings</div>
                <div class="balance-value">ETB <?= number_format($bonus_total, 2) ?></div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-value"><?= $active_count ?></div>
                <div class="stat-label">Active Investments</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $referral_stats['total_referrals'] ?? 0 ?></div>
                <div class="stat-label">Total Referrals</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $referral_stats['active_referrals'] ?? 0 ?></div>
                <div class="stat-label">Active Team</div>
            </div>
        </div>

        <!-- Recharge and Withdraw Buttons -->
        <div class="action-buttons">
            <a href="recharge.php" class="recharge-btn">
                <i class="fas fa-arrow-up"></i> Recharge
            </a>
            <a href="withdraw.php" class="withdraw-btn">
                <i class="fas fa-arrow-down"></i> Withdraw
            </a>
        </div>

        <!-- Action grid -->
        <div class="action-grid">
            <a href="bonus.php" class="action-item">
                <i class="fas fa-gift"></i>
                <span>Bonus</span>
            </a>
            <a href="incentive.php" class="action-item">
                <i class="fas fa-fire"></i>
                <span>Incentive</span>
            </a>
            <a href="my_products.php" class="action-item">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
            <a href="my_income.php" class="action-item">
                <i class="fas fa-coins"></i>
                <span>Income</span>
            </a>
        </div>

        <!-- Shortcuts -->
        <div class="shortcut-grid">
            <a href="my_products.php" class="shortcut-item">
                <i class="fas fa-box"></i>
                <span>My Product</span>
            </a>
            <a href="record.php" class="shortcut-item">
                <i class="fas fa-history"></i>
                <span>Record</span>
            </a>
            <a href="my_income.php" class="shortcut-item">
                <i class="fas fa-coins"></i>
                <span>My Income</span>
            </a>
            <a href="team.php" class="shortcut-item">
                <i class="fas fa-users"></i>
                <span>My Team</span>
            </a>
        </div>

        <!-- Recent recharge -->
        <a href="transaction_details.php?id=recent" class="recent-card">
            <div class="recent-title">
                <i class="fas fa-clock"></i> RECENT ACTIVITY
            </div>
            <div class="transaction-row">
                <div class="tx-left">
                    <div class="tx-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <div class="tx-info">
                        <h4>78****5409</h4>
                        <p><i class="far fa-clock"></i> 01:03:28 · Recharge</p>
                    </div>
                </div>
                <div class="tx-amount">
                    <div class="value">ETB 0.00</div>
                    <div class="label">completed</div>
                </div>
            </div>
        </a>

        <!-- Live Withdrawals section -->
        <div class="section-header">
            <h3><i class="fas fa-stream"></i> Live Withdrawals</h3>
            <a href="withdrawals_history.php" class="view-all">
                View All <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        
        <div class="withdrawal-list">
            <?php if (empty($withdrawals)): ?>
                <div style="text-align:center; color:#a5b4cb; padding:20px;">
                    <i class="fas fa-clock"></i> No successful withdrawals yet
                </div>
            <?php else: ?>
                <?php foreach ($withdrawals as $w): ?>
                <div class="withdrawal-item">
                    <div class="withdrawal-left">
                        <span class="phone-mask"><?= htmlspecialchars($w['phone_masked']) ?></span>
                        <div class="details">
                            <span class="amount">ETB <?= number_format($w['amount'], 2) ?></span>
                        </div>
                    </div>
                    <div class="status">
                        <i class="fas fa-check-circle"></i> <?= ucfirst($w['status']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <a href="home.php" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="product.php" class="nav-item">
                <i class="fas fa-cube"></i>
                <span>Product</span>
            </a>
            <a href="official.php" class="nav-item">
                <i class="fas fa-bullhorn"></i>
                <span>Official</span>
            </a>
            <a href="team.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Team</span>
            </a>
            <a href="profile.php" class="nav-item">
                <i class="fas fa-user"></i>
                <span>Mine</span>
            </a>
        </div>
    </div>
</body>
</html>
