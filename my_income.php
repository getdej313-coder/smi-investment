<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get income by source
$investment_income = $pdo->prepare("SELECT SUM(amount) as total FROM daily_earnings WHERE user_id = ? AND status = 'paid'");
$investment_income->execute([$user_id]);
$investment_total = $investment_income->fetch()['total'] ?? 0;

$bonus_income = 0; // You can add bonus table later
$referral_income = 0; // Add referral system later

// Get monthly income for chart
$monthly = $pdo->prepare("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total 
    FROM daily_earnings 
    WHERE user_id = ? AND status = 'paid' 
    GROUP BY month 
    ORDER BY month DESC 
    LIMIT 6
");
$monthly->execute([$user_id]);
$monthly_data = $monthly->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Income - Smi Investment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.6rem; }
        .back-btn { color:#fbbf24; text-decoration:none; font-size:1.2rem; }
        .total-income-card { background:linear-gradient(135deg,#1e4b5e,#12303e); border-radius:30px; padding:25px; margin-bottom:30px; text-align:center; }
        .total-label { color:#a5b4cb; font-size:0.9rem; }
        .total-value { color:white; font-size:2.5rem; font-weight:700; margin:10px 0; }
        .total-period { color:#fbbf24; font-size:0.9rem; }
        .stats-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:30px; }
        .stat-card { background:#1e2a3a; border-radius:20px; padding:20px; text-align:center; border:1px solid #2d3a4b; }
        .stat-icon { width:50px; height:50px; background:#273649; border-radius:25px; display:flex; align-items:center; justify-content:center; color:#fbbf24; margin:0 auto 10px; }
        .stat-value { color:white; font-size:1.3rem; font-weight:700; }
        .stat-label { color:#a5b4cb; font-size:0.8rem; }
        .income-source { background:#1e2a3a; border-radius:24px; padding:18px; margin-bottom:15px; }
        .source-header { display:flex; justify-content:space-between; margin-bottom:10px; }
        .source-name { color:white; font-weight:600; }
        .source-amount { color:#fbbf24; font-weight:700; }
        .progress-bar { height:6px; background:#0f1a28; border-radius:3px; }
        .progress-fill { height:6px; background:#fbbf24; border-radius:3px; width:0%; }
        .chart-container { background:#1e2a3a; border-radius:24px; padding:18px; margin-top:20px; }
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
            <h2>My Income</h2>
            <a href="#" style="color:#fbbf24;"><i class="fas fa-share-alt"></i></a>
        </div>
        
        <div class="total-income-card">
            <div class="total-label">Total Lifetime Income</div>
            <div class="total-value">ETB <?= number_format($user['total_earned'], 2) ?></div>
            <div class="total-period">+ ETB <?= number_format($investment_total, 2) ?> this month</div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-coins"></i></div>
                <div class="stat-value">ETB <?= number_format($investment_total, 2) ?></div>
                <div class="stat-label">Investment Income</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-gift"></i></div>
                <div class="stat-value">ETB 0.00</div>
                <div class="stat-label">Bonus Income</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-value">ETB 0.00</div>
                <div class="stat-label">Referral Income</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="stat-value"><?= $user['plan'] ?></div>
                <div class="stat-label">Current Plan</div>
            </div>
        </div>
        
        <h3 style="color:white; margin-bottom:15px;">Income Sources</h3>
        
        <div class="income-source">
            <div class="source-header">
                <span class="source-name">Investment Returns</span>
                <span class="source-amount">ETB <?= number_format($investment_total, 2) ?></span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 75%;"></div>
            </div>
        </div>
        
        <div class="income-source">
            <div class="source-header">
                <span class="source-name">Bonuses</span>
                <span class="source-amount">ETB 0.00</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 0%;"></div>
            </div>
        </div>
        
        <div class="income-source">
            <div class="source-header">
                <span class="source-name">Referrals</span>
                <span class="source-amount">ETB 0.00</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: 0%;"></div>
            </div>
        </div>
        
        <div class="chart-container">
            <canvas id="incomeChart" style="width:100%; height:200px;"></canvas>
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
    
    <script>
        const ctx = document.getElementById('incomeChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column(array_reverse($monthly_data), 'month')) ?>,
                datasets: [{
                    label: 'Monthly Income',
                    data: <?= json_encode(array_column(array_reverse($monthly_data), 'total')) ?>,
                    borderColor: '#fbbf24',
                    backgroundColor: 'rgba(251, 191, 36, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { grid: { color: '#2d3a4b' }, ticks: { color: '#a5b4cb' } },
                    x: { grid: { display: false }, ticks: { color: '#a5b4cb' } }
                }
            }
        });
    </script>
</body>
</html>