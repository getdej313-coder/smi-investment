<?php
session_start();
require_once 'includes/auth.php';

// Check if success message exists
if (!isset($_SESSION['recharge_success'])) {
    header("Location: home.php");
    exit;
}

// Clear the session variable
unset($_SESSION['recharge_success']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Success - Smi Investment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px; }
        .success-card { max-width:350px; width:100%; background:#101b2b; border-radius:36px; padding:40px 25px; text-align:center; border:2px solid #4ade80; animation: scaleUp 0.3s ease; }
        
        @keyframes scaleUp {
            from { transform:scale(0.8); opacity:0; }
            to { transform:scale(1); opacity:1; }
        }
        
        .success-icon { width:100px; height:100px; background:#163a30; border-radius:50%; margin:0 auto 25px; display:flex; align-items:center; justify-content:center; border:3px solid #4ade80; }
        .success-icon i { color:#4ade80; font-size:3rem; }
        
        .success-title { color:white; font-size:1.8rem; font-weight:700; margin-bottom:15px; }
        .success-message { color:#a5b4cb; margin-bottom:30px; line-height:1.6; }
        
        .home-btn { display:inline-block; background:linear-gradient(105deg,#1e4b5e,#12303e); border:none; border-radius:40px; padding:16px 40px; color:white; text-decoration:none; font-weight:600; transition:0.2s; border:1px solid #fbbf24; }
        .home-btn:hover { transform:translateY(-2px); background:linear-gradient(105deg,#1b5f72,#164c5e); }
        
        .balance-update { background:#1e2a3a; border-radius:20px; padding:20px; margin:20px 0; }
        .balance-label { color:#a5b4cb; font-size:0.9rem; }
        .balance-value { color:#fbbf24; font-size:2rem; font-weight:700; }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <div class="success-title">Success!</div>
        
        <div class="success-message">
            Your recharge request has been submitted successfully.<br>
            Your balance will be updated within a few minutes.
        </div>
        
        <div class="balance-update">
            <div class="balance-label">New Balance</div>
            <div class="balance-value">ETB <?= number_format($_SESSION['balance'] ?? 0, 2) ?></div>
        </div>
        
        <a href="home.php" class="home-btn">
            <i class="fas fa-home"></i> Go to Home
        </a>
    </div>
</body>
</html>