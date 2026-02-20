<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get incentive programs
$incentives = [
    ['name' => 'Monthly Top Investor', 'prize' => '5000 ETB', 'rank' => 3, 'progress' => 8500, 'target' => 10000, 'icon' => 'fa-trophy'],
    ['name' => 'Referral King', 'prize' => '3000 ETB', 'rank' => 5, 'progress' => 8, 'target' => 20, 'icon' => 'fa-crown'],
    ['name' => 'Team Builder', 'prize' => '2000 ETB', 'rank' => 2, 'progress' => 15, 'target' => 30, 'icon' => 'fa-people-arrows'],
    ['name' => 'Early Bird', 'prize' => '1000 ETB', 'progress' => 3, 'target' => 5, 'icon' => 'fa-sun'],
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Incentive Programs - Smi Investment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.6rem; }
        .back-btn { color:#fbbf24; text-decoration:none; font-size:1.2rem; }
        .stats-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:24px; }
        .stat-card { background:#1e2a3a; border-radius:20px; padding:16px; text-align:center; border:1px solid #2d3a4b; }
        .stat-value { color:#fbbf24; font-size:1.4rem; font-weight:700; }
        .stat-label { color:#a5b4cb; font-size:0.8rem; }
        .incentive-card { background:#1e2a3a; border-radius:24px; padding:18px; margin-bottom:16px; border:1px solid #2d3a4b; box-shadow:0 5px 0 #0f172a; }
        .incentive-header { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
        .incentive-icon { width:50px; height:50px; background:#273649; border-radius:25px; display:flex; align-items:center; justify-content:center; color:#fbbf24; font-size:1.5rem; }
        .incentive-info { flex:1; }
        .incentive-name { color:white; font-weight:600; font-size:1.1rem; }
        .incentive-prize { color:#fbbf24; font-weight:700; }
        .rank-badge { background:#5b4a1a; color:#fbbf24; padding:4px 12px; border-radius:20px; font-size:0.8rem; }
        .progress-info { display:flex; justify-content:space-between; margin:10px 0; color:#a5b4cb; font-size:0.9rem; }
        .progress-bar { height:10px; background:#0f1a28; border-radius:5px; margin:10px 0; }
        .progress-fill { height:10px; background:#fbbf24; border-radius:5px; width:0%; }
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
            <h2>Incentives</h2>
            <div style="width:24px;"></div>
        </div>
        
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-value">#<?= rand(1,10) ?></div>
                <div class="stat-label">Global Rank</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= rand(100,500) ?></div>
                <div class="stat-label">Points Earned</div>
            </div>
        </div>
        
        <h3 style="color:white; margin-bottom:16px;">Active Programs</h3>
        
        <?php foreach ($incentives as $incentive): 
            $progress = ($incentive['progress'] / $incentive['target']) * 100;
        ?>
        <div class="incentive-card">
            <div class="incentive-header">
                <div class="incentive-icon"><i class="fas <?= $incentive['icon'] ?>"></i></div>
                <div class="incentive-info">
                    <div class="incentive-name"><?= $incentive['name'] ?></div>
                    <div class="incentive-prize">🏆 Prize: <?= $incentive['prize'] ?></div>
                </div>
                <?php if(isset($incentive['rank'])): ?>
                <span class="rank-badge">Rank #<?= $incentive['rank'] ?></span>
                <?php endif; ?>
            </div>
            
            <div class="progress-info">
                <span>Progress: <?= $incentive['progress'] ?>/<?= $incentive['target'] ?></span>
                <span><?= round($progress) ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
            </div>
        </div>
        <?php endforeach; ?>
        
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