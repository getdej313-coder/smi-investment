<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Check if recharge data exists
if (!isset($_SESSION['recharge_data'])) {
    header("Location: recharge.php");
    exit;
}

$recharge_data = $_SESSION['recharge_data'];

// Get bank details
$stmt = $pdo->prepare("SELECT * FROM banks WHERE id = ?");
$stmt->execute([$recharge_data['bank_id']]);
$bank = $stmt->fetch();

// Default bank account for display
$default_bank = [
    'bank_name' => 'CBE',
    'account_name' => 'Getasew Dejenie Admasie',
    'account_number' => '1000451463447'
];

// Handle confirmation
if (isset($_POST['confirm'])) {
    // Show the recharge details page
    header("Location: recharge_details.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Confirm Recharge - Smi Investment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px; }
        .popup-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.8); display:flex; align-items:center; justify-content:center; z-index:1000; }
        .popup-card { max-width:350px; width:90%; background:#101b2b; border-radius:36px; padding:30px 25px; text-align:center; border:2px solid #fbbf24; animation: slideUp 0.3s ease; }
        
        @keyframes slideUp {
            from { transform:translateY(50px); opacity:0; }
            to { transform:translateY(0); opacity:1; }
        }
        
        .amount-circle { width:100px; height:100px; background:#1e2a3a; border-radius:50%; margin:0 auto 20px; display:flex; align-items:center; justify-content:center; border:3px solid #fbbf24; }
        .amount-circle span { color:#fbbf24; font-size:1.8rem; font-weight:700; }
        
        .bank-icon { color:#fbbf24; font-size:2rem; margin-bottom:15px; }
        
        .amount-text { color:white; font-size:2.5rem; font-weight:700; margin:10px 0; }
        .amount-text small { font-size:1rem; color:#a5b4cb; }
        
        .info-text { color:#a5b4cb; margin:20px 0; line-height:1.6; }
        
        .tip-box { background:#1e2a3a; border-radius:20px; padding:15px; margin:20px 0; display:flex; gap:10px; text-align:left; border-left:4px solid #fbbf24; }
        .tip-icon { color:#fbbf24; font-size:1.2rem; }
        .tip-content { color:#a5b4cb; font-size:0.9rem; }
        
        .confirm-btn { width:100%; background:#fbbf24; border:none; border-radius:40px; padding:16px; color:#0b1424; font-size:1.2rem; font-weight:700; cursor:pointer; transition:0.2s; margin-top:20px; }
        .confirm-btn:hover { transform:translateY(-2px); background:#e6ac1a; }
        
        .cancel-btn { width:100%; background:transparent; border:1px solid #2d3a4b; border-radius:40px; padding:16px; color:#a5b4cb; font-size:1rem; cursor:pointer; margin-top:10px; transition:0.2s; }
        .cancel-btn:hover { background:#1e2a3a; }
        
        .timer { color:#fbbf24; font-size:0.9rem; margin-top:15px; }
        .timer i { margin-right:5px; }
    </style>
</head>
<body>
    <div class="popup-overlay">
        <div class="popup-card">
            <div class="bank-icon">
                <i class="fas fa-university"></i>
            </div>
            
            <div class="amount-circle">
                <span>ETB</span>
            </div>
            
            <div class="amount-text">
                <?= number_format($recharge_data['amount'], 2) ?> <small>ETB</small>
            </div>
            
            <div class="info-text">
                <i class="fas fa-building" style="color:#fbbf24;"></i> <?= htmlspecialchars($bank['bank_name']) ?> Bank
            </div>
            
            <div class="tip-box">
                <div class="tip-icon"><i class="fas fa-lightbulb"></i></div>
                <div class="tip-content">
                    <strong>Tips</strong><br>
                    Please click on "confirm" to get the next interface!
                </div>
            </div>
            
            <form method="POST">
                <button type="submit" name="confirm" class="confirm-btn">
                    <i class="fas fa-check-circle"></i> Confirm
                </button>
            </form>
            
            <a href="recharge.php" class="cancel-btn">
                <i class="fas fa-times"></i> Cancel
            </a>
            
            <div class="timer">
                <i class="fas fa-clock"></i> Expires in: 15:00
            </div>
        </div>
    </div>
</body>
</html>