<?php
// Start session at the VERY TOP
session_start();

require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// If user not found in database, logout
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home - Smi Investment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%); min-height:100vh; }
        .navbar { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 1rem 2rem; color: white; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: bold; }
        .logo i { color: #d4a017; margin-right: 10px; }
        .nav-links a { color: white; text-decoration: none; margin-left: 2rem; }
        .nav-links a:hover { color: #d4a017; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }
        .welcome-card { background: white; border-radius: 1.5rem; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .balance-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 2rem; }
        .balance-card { background: linear-gradient(135deg, #1e4b5e, #12303e); color: white; padding: 1.5rem; border-radius: 1rem; }
        .balance-card i { font-size: 2rem; color: #d4a017; margin-bottom: 1rem; }
        .balance-card h3 { font-size: 1rem; opacity: 0.9; margin-bottom: 0.5rem; }
        .balance-card .amount { font-size: 2rem; font-weight: bold; }
        .logout-btn { background: #d4a017; color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; cursor: pointer; text-decoration: none; }
        .logout-btn:hover { background: #b3860c; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-chart-line"></i> Smi Investment
        </div>
        <div class="nav-links">
            <a href="home.php"><i class="fas fa-home"></i> Home</a>
            <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-card">
            <h1 style="color: #1e4b5e; margin-bottom: 1rem;">Welcome back, <?= htmlspecialchars($user['full_name']) ?>! 👋</h1>
            <p style="color: #666;">Your investment dashboard is ready.</p>
        </div>

        <div class="balance-grid">
            <div class="balance-card">
                <i class="fas fa-wallet"></i>
                <h3>Main Balance</h3>
                <div class="amount">$<?= number_format($user['balance'] ?? 0, 2) ?></div>
            </div>
            
            <div class="balance-card" style="background: linear-gradient(135deg, #2c5a6e, #1a475a);">
                <i class="fas fa-chart-line"></i>
                <h3>Total Earned</h3>
                <div class="amount">$<?= number_format($user['total_earned'] ?? 0, 2) ?></div>
            </div>
            
            <div class="balance-card" style="background: linear-gradient(135deg, #3a6a7e, #245769);">
                <i class="fas fa-clock"></i>
                <h3>Pending Balance</h3>
                <div class="amount">$<?= number_format($user['pending_balance'] ?? 0, 2) ?></div>
            </div>
            
            <div class="balance-card" style="background: linear-gradient(135deg, #487a8e, #2e6779);">
                <i class="fas fa-gift"></i>
                <h3>Referral Bonus</h3>
                <div class="amount">$0.00</div>
            </div>
        </div>

        <div style="margin-top: 3rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
            <div style="background: white; padding: 1.5rem; border-radius: 1rem;">
                <h3 style="color: #1e4b5e; margin-bottom: 1rem;"><i class="fas fa-phone"></i> Your Info</h3>
                <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone']) ?></p>
                <p><strong>Member since:</strong> <?= date('M d, Y', strtotime($user['created_at'] ?? 'now')) ?></p>
                <p><strong>Referral Code:</strong> <?= htmlspecialchars($user['referral_code'] ?? 'N/A') ?></p>
            </div>
            
            <div style="background: white; padding: 1.5rem; border-radius: 1rem;">
                <h3 style="color: #1e4b5e; margin-bottom: 1rem;"><i class="fas fa-bolt"></i> Quick Actions</h3>
                <p><a href="deposit.php" style="color: #1e4b5e; text-decoration: none;">💰 Make a Deposit</a></p>
                <p><a href="withdraw.php" style="color: #1e4b5e; text-decoration: none;">💸 Withdraw Funds</a></p>
                <p><a href="invest.php" style="color: #1e4b5e; text-decoration: none;">📈 Invest Now</a></p>
            </div>
        </div>
    </div>
</body>
</html>
