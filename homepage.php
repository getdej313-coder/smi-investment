<?php
require_once 'includes/auth.php'; // redirects to login if not logged in
$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get live withdrawals
$withdrawals = $pdo->query("SELECT phone_masked, amount, status FROM withdrawals ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Smi Investment - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Copy the exact styles from the home page provided earlier */
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; box-shadow:0 25px 50px -12px rgba(0,0,0,0.8); }
        .status-bar { display:flex; justify-content:space-between; color:#94a3b8; font-size:0.85rem; margin-bottom:24px; }
        .welcome-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
        .greeting h2 { color:white; font-size:1.6rem; }
        .greeting p { color:#9ca8b9; font-size:0.9rem; }
        .profile-icon { background:#1e2a3a; width:48px; height:48px; border-radius:24px; display:flex; align-items:center; justify-content:center; color:#fbbf24; border:1px solid #334155; }
        .action-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:28px; }
        .action-item { background:#1e2a3a; border-radius:20px; padding:14px 4px; text-align:center; color:white; border:1px solid #2d3a4b; box-shadow:0 6px 0 #0f172a; }
        .action-item i { font-size:1.6rem; color:#fbbf24; margin-bottom:6px; display:block; }
        .action-item span { font-size:0.8rem; font-weight:500; }
        .shortcut-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:28px; }
        .shortcut-item { text-align:center; color:#b9c7da; font-size:0.75rem; }
        .shortcut-item i { font-size:1.3rem; background:#1e2a3a; padding:12px; border-radius:30px; color:#fbbf24; margin-bottom:6px; display:inline-block; width:48px; height:48px; line-height:24px; border:1px solid #334155; }
        .recent-card { background:#1e2a3a; border-radius:26px; padding:18px; margin-bottom:28px; border:1px solid #2d3a4b; box-shadow:0 8px 0 #0f172a; }
        .recent-title { color:#a5b4cb; font-size:0.8rem; margin-bottom:12px; }
        .transaction-row { display:flex; align-items:center; justify-content:space-between; color:white; }
        .tx-left { display:flex; align-items:center; gap:12px; }
        .tx-icon { background:#2d3e54; width:46px; height:46px; border-radius:23px; display:flex; align-items:center; justify-content:center; color:#fbbf24; }
        .tx-info h4 { font-size:1rem; }
        .tx-info p { font-size:0.8rem; color:#9aaec5; }
        .tx-amount .value { font-size:1.3rem; font-weight:700; color:#fbbf24; }
        .section-header { display:flex; justify-content:space-between; color:white; margin-bottom:16px; }
        .withdrawal-list { display:flex; flex-direction:column; gap:12px; margin-bottom:30px; }
        .withdrawal-item { background:#1e2a3a; border-radius:24px; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; border:1px solid #2d3a4b; box-shadow:0 4px 0 #0f172a; }
        .withdrawal-left { display:flex; align-items:center; gap:12px; }
        .phone-mask { font-size:1rem; font-weight:600; color:white; background:#273649; padding:8px 14px; border-radius:40px; }
        .details .amount { font-size:1.2rem; font-weight:700; color:white; }
        .status { background:#163a30; padding:6px 14px; border-radius:40px; color:#76e5b0; font-size:0.8rem; font-weight:600; border:1px solid #2c7a5a; }
        .bottom-nav { position:absolute; bottom:0; left:0; right:0; background:#0f1a28; display:flex; justify-content:space-around; padding:12px 16px 20px; border-top:1px solid #263340; border-radius:30px 30px 0 0; }
        .nav-item { display:flex; flex-direction:column; align-items:center; color:#6b7e99; font-size:0.7rem; }
        .nav-item i { font-size:1.4rem; margin-bottom:4px; }
        .nav-item.active { color:#fbbf24; }
    </style>
</head>
<body>
    <div class="phone-frame">
        <div class="status-bar"><span>9:41</span><div><i class="fas fa-signal"></i><i class="fas fa-wifi"></i><i class="fas fa-battery-full"></i></div></div>
        <div class="welcome-row">
            <div class="greeting">
                <h2>Home</h2>
                <p><i class="fas fa-wallet"></i> Balance: <strong>ETB <?= number_format($user['balance'], 2) ?></strong></p>
            </div>
            <div class="profile-icon"><i class="fas fa-user"></i></div>
        </div>

        <!-- Action grid -->
        <div class="action-grid">
            <div class="action-item"><i class="fas fa-arrow-up"></i><span>Recharge</span></div>
            <div class="action-item"><i class="fas fa-arrow-down"></i><span>Withdraw</span></div>
            <div class="action-item"><i class="fas fa-gift"></i><span>Bonus</span></div>
            <div class="action-item"><i class="fas fa-fire"></i><span>Incentive</span></div>
        </div>

        <!-- Shortcuts -->
        <div class="shortcut-grid">
            <div class="shortcut-item"><i class="fas fa-box"></i><span>My Product</span></div>
            <div class="shortcut-item"><i class="fas fa-history"></i><span>Record</span></div>
            <div class="shortcut-item"><i class="fas fa-coins"></i><span>My Income</span></div>
            <div class="shortcut-item"><i class="fas fa-university"></i><span>Bank setup</span></div>
        </div>

        <!-- Recent recharge -->
        <div class="recent-card">
            <div class="recent-title"><i class="fas fa-clock"></i> RECENT ACTIVITY</div>
            <div class="transaction-row">
                <div class="tx-left">
                    <div class="tx-icon"><i class="fas fa-mobile-alt"></i></div>
                    <div class="tx-info">
                        <h4>78****5409</h4>
                        <p><i class="far fa-clock"></i> 01:03:28 · Recharge</p>
                    </div>
                </div>
                <div class="tx-amount"><div class="value">ETB 2060</div><div class="label">completed</div></div>
            </div>
        </div>

        <!-- Live Withdrawals -->
        <div class="section-header"><h3><i class="fas fa-stream"></i> Live Withdrawals</h3><i class="fas fa-chevron-right"></i></div>
        <div class="withdrawal-list">
            <?php foreach ($withdrawals as $w): ?>
            <div class="withdrawal-item">
                <div class="withdrawal-left">
                    <span class="phone-mask"><?= $w['phone_masked'] ?></span>
                    <div class="details"><span class="amount">ETB <?= number_format($w['amount'], 2) ?></span></div>
                </div>
                <div class="status"><i class="fas fa-check-circle"></i> <?= ucfirst($w['status']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Bottom Nav -->
        <div class="bottom-nav">
            <div class="nav-item active"><i class="fas fa-home"></i><span>Home</span></div>
            <div class="nav-item"><i class="fas fa-cube"></i><span>Product</span></div>
            <div class="nav-item"><i class="fas fa-bullhorn"></i><span>Official</span></div>
            <div class="nav-item"><i class="fas fa-users"></i><span>Team</span></div>
            <div class="nav-item"><i class="fas fa-user"></i><span>Mine</span></div>
        </div>
    </div>
</body>
</html>