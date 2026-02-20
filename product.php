<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Get user balance
$stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch all active plans
$plans = $pdo->query("SELECT * FROM recharge_plans WHERE is_active = 1 ORDER BY amount")->fetchAll();

// Get user's active investments
$investments = $pdo->prepare("SELECT ui.*, rp.name as plan_name FROM user_investments ui JOIN recharge_plans rp ON ui.plan_id = rp.id WHERE ui.user_id = ? AND ui.status = 'active' ORDER BY ui.start_date DESC");
$investments->execute([$user_id]);
$active_investments = $investments->fetchAll();

// Handle investment purchase
if (isset($_GET['invest']) && is_numeric($_GET['invest'])) {
    $plan_id = $_GET['invest'];
    $stmt = $pdo->prepare("SELECT * FROM recharge_plans WHERE id = ? AND is_active = 1");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch();
    
    if ($plan) {
        if ($plan['amount'] > $user['balance']) {
            $error = "Insufficient balance. Please recharge first.";
        } else {
            // Begin transaction
            $pdo->beginTransaction();
            try {
                // Deduct from user balance
                $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?")->execute([$plan['amount'], $user_id]);
                
                // Create investment record
                $stmt = $pdo->prepare("INSERT INTO user_investments (user_id, plan_id, amount, daily_return, period_days, start_date) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$user_id, $plan_id, $plan['amount'], $plan['daily_return'], $plan['period_days']]);
                $investment_id = $pdo->lastInsertId();
                
                // Create daily earning records
                for ($day = 1; $day <= $plan['period_days']; $day++) {
                    $payout_date = date('Y-m-d', strtotime("+$day days"));
                    $stmt = $pdo->prepare("INSERT INTO daily_earnings (user_id, investment_id, amount, day_number, payout_date) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$user_id, $investment_id, $plan['daily_return'], $day, $payout_date]);
                }
                
                $pdo->commit();
                $success = "Investment successful! You will receive daily returns starting tomorrow.";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Investment failed. Please try again.";
            }
        }
    }
}

// Process daily earnings
if (!isset($_SESSION['daily_processed_today'])) {
    $today = date('Y-m-d');
    $pending_earnings = $pdo->prepare("SELECT de.*, ui.user_id FROM daily_earnings de JOIN user_investments ui ON de.investment_id = ui.id WHERE de.payout_date = ? AND de.status = 'pending'");
    $pending_earnings->execute([$today]);
    $earnings = $pending_earnings->fetchAll();
    
    foreach ($earnings as $earning) {
        // Add to user balance
        $pdo->prepare("UPDATE users SET balance = balance + ?, total_earned = total_earned + ? WHERE id = ?")->execute([$earning['amount'], $earning['amount'], $earning['user_id']]);
        
        // Mark as paid
        $pdo->prepare("UPDATE daily_earnings SET status = 'paid' WHERE id = ?")->execute([$earning['id']]);
        
        // Update investment progress
        $pdo->prepare("UPDATE user_investments SET days_completed = days_completed + 1, total_earned = total_earned + ?, last_payout_date = NOW() WHERE id = ?")->execute([$earning['amount'], $earning['investment_id']]);
    }
    
    // Mark investments as completed
    $pdo->query("UPDATE user_investments SET status = 'completed' WHERE DATE_ADD(start_date, INTERVAL period_days DAY) < NOW() AND status = 'active'");
    
    $_SESSION['daily_processed_today'] = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Investment Plans - Smi Investment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { 
            margin:0; 
            padding:0; 
            box-sizing:border-box; 
            font-family:'Inter',sans-serif; 
        }
        
        body { 
            background:#0b1424; 
            min-height:100vh; 
            display:flex; 
            align-items:center; 
            justify-content:center; 
            padding:16px; 
        }
        
        .phone-frame { 
            max-width:400px; 
            width:100%; 
            background:#101b2b; 
            border-radius:36px; 
            padding:24px 20px 80px; 
            position:relative; 
            box-shadow:0 25px 50px -12px rgba(0,0,0,0.8);
            margin:0 auto;
        }
        
        .page-header { 
            display:flex; 
            justify-content:space-between; 
            align-items:center; 
            margin-bottom:20px; 
        }
        
        .page-header h2 { 
            color:white; 
            font-size:1.6rem; 
        }
        
        .back-btn { 
            color:#fbbf24; 
            text-decoration:none; 
            font-size:1.2rem;
            transition:0.2s;
        }
        
        .back-btn:hover {
            transform:translateX(-3px);
        }
        
        .home-icon {
            color:#fbbf24; 
            text-decoration:none;
            transition:0.2s;
        }
        
        .home-icon:hover {
            transform:scale(1.1);
        }
        
        .balance-card { 
            background:#1e2a3a; 
            border-radius:20px; 
            padding:16px; 
            margin-bottom:24px; 
            border:1px solid #2d3a4b; 
            box-shadow:0 5px 0 #0f172a; 
            display:flex; 
            justify-content:space-between; 
            align-items:center; 
        }
        
        .balance-label { 
            color:#a5b4cb; 
        }
        
        .balance-value { 
            color:#fbbf24; 
            font-size:1.4rem; 
            font-weight:700; 
        }
        
        .plan-card { 
            background:#1e2a3a; 
            border-radius:24px; 
            padding:18px; 
            margin-bottom:16px; 
            border:1px solid #2d3a4b; 
            box-shadow:0 5px 0 #0f172a; 
            transition:0.2s; 
        }
        
        .plan-card:hover { 
            transform:translateY(-2px); 
            background:#273649;
        }
        
        .plan-header { 
            display:flex; 
            justify-content:space-between; 
            align-items:center; 
            margin-bottom:12px; 
        }
        
        .plan-name { 
            color:#fbbf24; 
            font-size:1.3rem; 
            font-weight:700; 
        }
        
        .plan-level { 
            background:#273649; 
            padding:4px 12px; 
            border-radius:20px; 
            color:#fbbf24; 
            font-size:0.8rem; 
        }
        
        .plan-amount { 
            color:white; 
            font-size:1.2rem; 
            margin-bottom:8px; 
        }
        
        .plan-details { 
            background:#0f1a28; 
            border-radius:16px; 
            padding:12px; 
            margin:12px 0; 
        }
        
        .detail-row { 
            display:flex; 
            justify-content:space-between; 
            margin-bottom:8px; 
            color:#a5b4cb; 
        }
        
        .detail-row span:last-child { 
            color:#fbbf24; 
            font-weight:600; 
        }
        
        .total-return { 
            background:#163a30; 
            border-radius:12px; 
            padding:10px; 
            text-align:center; 
            color:#4ade80; 
            font-weight:600; 
            margin-bottom:12px; 
        }
        
        .invest-btn { 
            display:block; 
            background:linear-gradient(105deg,#1e4b5e,#12303e); 
            border:none; 
            border-radius:40px; 
            padding:14px; 
            color:white; 
            text-align:center; 
            text-decoration:none; 
            font-weight:600;
            transition:0.2s;
        }
        
        .invest-btn:hover { 
            background:linear-gradient(105deg,#1b5f72,#164c5e);
            transform:translateY(-2px);
        }
        
        .investments-section { 
            margin-top:30px; 
        }
        
        .section-title { 
            color:white; 
            font-size:1.2rem; 
            margin-bottom:16px; 
            display:flex; 
            align-items:center; 
            gap:8px; 
        }
        
        .investment-item { 
            background:#1e2a3a; 
            border-radius:18px; 
            padding:14px; 
            margin-bottom:12px; 
            border:1px solid #2d3a4b;
            transition:0.2s;
        }
        
        .investment-item:hover {
            background:#273649;
        }
        
        .investment-header { 
            display:flex; 
            justify-content:space-between; 
            margin-bottom:8px; 
        }
        
        .investment-name { 
            color:#fbbf24; 
            font-weight:600; 
        }
        
        .investment-status { 
            background:#273649; 
            padding:2px 10px; 
            border-radius:20px; 
            font-size:0.8rem; 
            color:#4ade80; 
        }
        
        .progress-bar { 
            background:#0f1a28; 
            height:8px; 
            border-radius:4px; 
            margin:10px 0; 
        }
        
        .progress-fill { 
            background:#fbbf24; 
            height:8px; 
            border-radius:4px; 
            width:0%; 
        }
        
        .success { 
            color:#166534; 
            background:#dcfce7; 
            padding:12px; 
            border-radius:30px; 
            margin-bottom:20px; 
        }
        
        .error { 
            color:#b91c1c; 
            background:#fee2e2; 
            padding:12px; 
            border-radius:30px; 
            margin-bottom:20px; 
        }
        
        .bottom-nav { 
            position:absolute; 
            bottom:0; 
            left:0; 
            right:0; 
            background:#0f1a28; 
            display:flex; 
            justify-content:space-around; 
            padding:12px 16px 20px; 
            border-top:1px solid #263340; 
            border-radius:30px 30px 0 0; 
        }
        
        .nav-item { 
            display:flex; 
            flex-direction:column; 
            align-items:center; 
            color:#6b7e99; 
            font-size:0.7rem; 
            text-decoration:none; 
            transition:0.2s;
        }
        
        .nav-item i { 
            font-size:1.4rem; 
            margin-bottom:4px; 
        }
        
        .nav-item.active { 
            color:#fbbf24; 
        }
        
        .nav-item:hover { 
            color:#fbbf24;
            transform:translateY(-2px);
        }

        /* ===== RESPONSIVE BREAKPOINTS ===== */

        /* Tablet Styles (600px - 1024px) */
        @media screen and (min-width: 600px) and (max-width: 1024px) {
            body { 
                padding:30px; 
                background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%);
            }
            
            .phone-frame { 
                max-width: 700px; 
                border-radius: 40px; 
                padding: 30px 30px 90px; 
            }
            
            .plan-card { 
                padding: 20px; 
            }
            
            .plan-name { 
                font-size: 1.5rem; 
            }
            
            .plan-amount { 
                font-size: 1.4rem; 
            }
            
            .plan-details { 
                padding: 15px; 
            }
            
            .detail-row { 
                font-size: 1rem; 
            }
            
            .bottom-nav { 
                padding: 15px 30px 25px; 
            }
            
            .nav-item span { 
                font-size: 0.8rem; 
            }
            
            .nav-item i { 
                font-size: 1.6rem; 
            }
        }

        /* Desktop Styles (1025px - 1440px) */
        @media screen and (min-width: 1025px) and (max-width: 1440px) {
            body { 
                padding: 40px; 
                background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%);
            }
            
            .phone-frame { 
                max-width: 900px; 
                border-radius: 50px; 
                padding: 40px 40px 100px; 
            }
            
            .plans-grid { 
                display: grid; 
                grid-template-columns: repeat(2, 1fr); 
                gap: 20px; 
            }
            
            .plan-card { 
                margin-bottom: 0; 
                height: fit-content; 
            }
            
            .balance-card { 
                max-width: 600px; 
                margin-left: auto; 
                margin-right: auto; 
            }
            
            .investments-section { 
                margin-top: 40px; 
            }
            
            .bottom-nav { 
                padding: 15px 40px 25px; 
                max-width: 900px; 
                left: 50%; 
                transform: translateX(-50%); 
                border-radius: 30px 30px 0 0; 
            }
        }

        /* Large Desktop Styles (1441px and above) */
        @media screen and (min-width: 1441px) {
            body { 
                padding: 50px; 
                background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%);
            }
            
            .phone-frame { 
                max-width: 1200px; 
                border-radius: 60px; 
                padding: 50px 50px 120px; 
            }
            
            .plans-grid { 
                display: grid; 
                grid-template-columns: repeat(3, 1fr); 
                gap: 25px; 
            }
            
            .plan-card { 
                margin-bottom: 0; 
                padding: 25px; 
            }
            
            .plan-name { 
                font-size: 1.8rem; 
            }
            
            .plan-amount { 
                font-size: 1.6rem; 
            }
            
            .plan-details { 
                padding: 18px; 
            }
            
            .detail-row { 
                font-size: 1.1rem; 
            }
            
            .balance-card { 
                max-width: 800px; 
                margin-left: auto; 
                margin-right: auto; 
                padding: 20px; 
            }
            
            .balance-value { 
                font-size: 2rem; 
            }
            
            .investments-section { 
                margin-top: 50px; 
            }
            
            .investment-item { 
                padding: 18px; 
            }
            
            .bottom-nav { 
                max-width: 1200px; 
                padding: 20px 50px 30px; 
                left: 50%; 
                transform: translateX(-50%); 
            }
            
            .nav-item span { 
                font-size: 0.9rem; 
            }
            
            .nav-item i { 
                font-size: 1.8rem; 
            }
        }

        /* Small Mobile Devices (below 400px) */
        @media screen and (max-width: 399px) {
            .phone-frame { 
                padding: 20px 15px 80px; 
            }
            
            .page-header h2 { 
                font-size: 1.4rem; 
            }
            
            .plan-card { 
                padding: 15px; 
            }
            
            .plan-name { 
                font-size: 1.2rem; 
            }
            
            .plan-amount { 
                font-size: 1.1rem; 
            }
            
            .plan-details { 
                padding: 10px; 
            }
            
            .detail-row { 
                font-size: 0.9rem; 
            }
            
            .total-return { 
                font-size: 0.9rem; 
            }
            
            .invest-btn { 
                padding: 12px; 
                font-size: 0.9rem; 
            }
            
            .bottom-nav { 
                padding: 10px 10px 15px; 
            }
            
            .nav-item i { 
                font-size: 1.2rem; 
            }
            
            .nav-item span { 
                font-size: 0.6rem; 
            }
        }

        /* Landscape Mode */
        @media screen and (orientation: landscape) and (max-height: 600px) {
            body { 
                padding: 20px; 
                align-items: flex-start;
            }
            
            .phone-frame { 
                max-width: 700px; 
                padding: 20px 20px 70px; 
            }
            
            .plans-grid { 
                display: grid; 
                grid-template-columns: repeat(2, 1fr); 
                gap: 15px; 
            }
            
            .plan-card { 
                margin-bottom: 0; 
            }
            
            .bottom-nav { 
                padding: 8px 20px 15px; 
            }
        }

        /* Fix for very tall screens */
        @media screen and (min-height: 1000px) {
            body { 
                align-items: flex-start; 
                padding-top: 50px; 
                padding-bottom: 50px; 
            }
        }

        /* Print Styles */
        @media print {
            body { 
                background: white; 
                padding: 0; 
            }
            
            .phone-frame { 
                box-shadow: none; 
                background: white; 
                color: black; 
                max-width: 100%;
            }
            
            .bottom-nav, 
            .invest-btn, 
            .back-btn, 
            .home-icon { 
                display: none; 
            }
            
            .plan-card { 
                break-inside: avoid; 
                border: 1px solid #ccc; 
                box-shadow: none; 
            }
        }
    </style>
</head>
<body>
    <div class="phone-frame">
        <div class="page-header">
            <a href="home.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            <h2>Investment Plans</h2>
            <a href="home.php" class="home-icon"><i class="fas fa-home"></i></a>
        </div>
        
        <!-- User Balance -->
        <div class="balance-card">
            <span class="balance-label">Your Balance</span>
            <span class="balance-value">ETB <?= number_format($user['balance'], 2) ?></span>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>
        
        <!-- Plans Grid for Desktop -->
        <div class="plans-grid">
            <?php foreach ($plans as $plan): 
                $total_return = $plan['daily_return'] * $plan['period_days'];
                $profit_percentage = (($total_return - $plan['amount']) / $plan['amount']) * 100;
            ?>
            <div class="plan-card">
                <div class="plan-header">
                    <span class="plan-name"><?= htmlspecialchars($plan['name']) ?></span>
                    <span class="plan-level">Level <?= $plan['level'] ?></span>
                </div>
                <div class="plan-amount">ETB <?= number_format($plan['amount'], 2) ?></div>
                <div class="plan-details">
                    <div class="detail-row"><span>Daily Return:</span><span>ETB <?= number_format($plan['daily_return'], 2) ?></span></div>
                    <div class="detail-row"><span>Period:</span><span><?= $plan['period_days'] ?> Days</span></div>
                    <div class="detail-row"><span>Total Return:</span><span>ETB <?= number_format($total_return, 2) ?></span></div>
                </div>
                <div class="total-return">🔥 <?= round($profit_percentage) ?>% Profit 🔥</div>
                <a href="?invest=<?= $plan['id'] ?>" class="invest-btn" onclick="return confirm('Invest ETB <?= number_format($plan['amount'], 2) ?> in this plan?')">Invest Now</a>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Active Investments -->
        <?php if (!empty($active_investments)): ?>
        <div class="investments-section">
            <div class="section-title">
                <i class="fas fa-chart-line" style="color:#fbbf24;"></i> Your Active Investments
            </div>
            <?php foreach ($active_investments as $inv): 
                $progress = ($inv['days_completed'] / $inv['period_days']) * 100;
            ?>
            <div class="investment-item">
                <div class="investment-header">
                    <span class="investment-name"><?= htmlspecialchars($inv['plan_name']) ?></span>
                    <span class="investment-status">Active</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:8px; color:#a5b4cb;">
                    <span>Invested: ETB <?= number_format($inv['amount'], 2) ?></span>
                    <span>Earned: ETB <?= number_format($inv['total_earned'], 2) ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:5px; color:#a5b4cb; font-size:0.9rem;">
                    <span>Day <?= $inv['days_completed'] ?>/<?= $inv['period_days'] ?></span>
                    <span>Daily: ETB <?= number_format($inv['daily_return'], 2) ?></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <a href="home.php" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="product.php" class="nav-item active"><i class="fas fa-cube"></i><span>Product</span></a>
            <a href="official.php" class="nav-item"><i class="fas fa-bullhorn"></i><span>Official</span></a>
            <a href="team.php" class="nav-item"><i class="fas fa-users"></i><span>Team</span></a>
            <a href="profile.php" class="nav-item"><i class="fas fa-user"></i><span>Mine</span></a>
        </div>
    </div>
</body>
</html>