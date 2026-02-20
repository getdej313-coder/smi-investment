<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get available bonuses
$bonuses = [
    ['name' => 'Welcome Bonus', 'amount' => 50, 'requirement' => 'New Registration', 'status' => 'available', 'icon' => 'fa-gift'],
    ['name' => 'First Deposit', 'amount' => 100, 'requirement' => 'Deposit 500 ETB', 'status' => 'available', 'icon' => 'fa-coins'],
    ['name' => 'Referral Bonus', 'amount' => 200, 'requirement' => 'Refer 5 friends', 'status' => 'progress', 'progress' => 3, 'total' => 5, 'icon' => 'fa-users'],
    ['name' => 'Daily Login', 'amount' => 10, 'requirement' => 'Login 7 consecutive days', 'status' => 'claimed', 'icon' => 'fa-calendar-check'],
    ['name' => 'Investment Milestone', 'amount' => 500, 'requirement' => 'Invest 5000 ETB total', 'status' => 'locked', 'icon' => 'fa-chart-line'],
    ['name' => 'Team Bonus', 'amount' => 300, 'requirement' => 'Build team of 10 members', 'status' => 'progress', 'progress' => 6, 'total' => 10, 'icon' => 'fa-people-group'],
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bonus Offers - Smi Investment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.6rem; }
        .back-btn { color:#fbbf24; text-decoration:none; font-size:1.2rem; }
        .balance-card { background:#1e2a3a; border-radius:20px; padding:16px; margin-bottom:24px; border:1px solid #2d3a4b; box-shadow:0 5px 0 #0f172a; display:flex; justify-content:space-between; align-items:center; }
        .balance-label { color:#a5b4cb; }
        .balance-value { color:#fbbf24; font-size:1.4rem; font-weight:700; }
        .bonus-grid { display:flex; flex-direction:column; gap:16px; margin-bottom:30px; }
        .bonus-card { background:#1e2a3a; border-radius:24px; padding:18px; border:1px solid #2d3a4b; box-shadow:0 5px 0 #0f172a; }
        .bonus-header { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
        .bonus-icon { width:50px; height:50px; background:#273649; border-radius:25px; display:flex; align-items:center; justify-content:center; color:#fbbf24; font-size:1.5rem; }
        .bonus-info { flex:1; }
        .bonus-name { color:white; font-weight:600; font-size:1.1rem; }
        .bonus-amount { color:#fbbf24; font-weight:700; }
        .bonus-requirement { color:#a5b4cb; font-size:0.9rem; margin-bottom:10px; }
        .bonus-status { display:flex; align-items:center; justify-content:space-between; }
        .status-badge { padding:6px 12px; border-radius:20px; font-size:0.8rem; font-weight:600; }
        .status-available { background:#163a30; color:#4ade80; }
        .status-progress { background:#5b4a1a; color:#fbbf24; }
        .status-claimed { background:#2d3748; color:#a5b4cb; }
        .status-locked { background:#2d1f1f; color:#f87171; }
        .progress-bar { flex:1; height:8px; background:#0f1a28; border-radius:4px; margin:0 10px; }
        .progress-fill { height:8px; background:#fbbf24; border-radius:4px; width:0%; }
        .claim-btn { background:linear-gradient(105deg,#1e4b5e,#12303e); border:none; border-radius:30px; padding:10px 20px; color:white; text-decoration:none; font-size:0.9rem; }
        .claim-btn:hover { background:linear-gradient(105deg,#1b5f72,#164c5e); }
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
            <h2>Bonus Offers</h2>
            <div style="width:24px;"></div>
        </div>
        
        <div class="balance-card">
            <span class="balance-label">Available Bonuses</span>
            <span class="balance-value">ETB 150</span>
        </div>
        
        <div class="bonus-grid">
            <?php foreach ($bonuses as $bonus): ?>
            <div class="bonus-card">
                <div class="bonus-header">
                    <div class="bonus-icon"><i class="fas <?= $bonus['icon'] ?>"></i></div>
                    <div class="bonus-info">
                        <div class="bonus-name"><?= $bonus['name'] ?></div>
                        <div class="bonus-amount">ETB <?= $bonus['amount'] ?></div>
                    </div>
                </div>
                <div class="bonus-requirement">
                    <i class="fas fa-info-circle" style="color:#fbbf24; margin-right:5px;"></i>
                    <?= $bonus['requirement'] ?>
                </div>
                <div class="bonus-status">
                    <?php if($bonus['status'] == 'progress'): ?>
                        <div style="flex:1; display:flex; align-items:center; gap:10px;">
                            <span class="status-badge status-progress"><?= $bonus['progress'] ?>/<?= $bonus['total'] ?></span>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= ($bonus['progress']/$bonus['total'])*100 ?>%;"></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="status-badge status-<?= $bonus['status'] ?>">
                            <?= ucfirst($bonus['status']) ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if($bonus['status'] == 'available'): ?>
                        <a href="#" class="claim-btn">Claim</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
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