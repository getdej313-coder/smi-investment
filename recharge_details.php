<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Check if recharge data exists
if (!isset($_SESSION['recharge_data'])) {
    header("Location: recharge.php");
    exit;
}

$recharge_data = $_SESSION['recharge_data'];
$expires_at = strtotime($recharge_data['expires_at']);
$time_remaining = $expires_at - time();
$minutes = floor($time_remaining / 60);
$seconds = $time_remaining % 60;

// Default bank account
$default_account = [
    'bank_name' => 'CBE',
    'account_name' => 'Getasew Dejenie Admasie',
    'account_number' => '1000451463447'
];

// Handle payment confirmation
if (isset($_POST['confirm_payment'])) {
    $transaction_id = $_POST['transaction_id'] ?? '';
    
    // Insert recharge record
    $stmt = $pdo->prepare("INSERT INTO recharges (user_id, amount, bank_id, transaction_id, status, expires_at) VALUES (?, ?, ?, ?, 'pending', ?)");
    $stmt->execute([$user_id, $recharge_data['amount'], $recharge_data['bank_id'], $transaction_id, $recharge_data['expires_at']]);
    
    // Clear session data
    unset($_SESSION['recharge_data']);
    
    // Set success message
    $_SESSION['recharge_success'] = true;
    header("Location: recharge_success.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Deposit Confirmation - Smi Investment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; box-shadow:0 25px 50px -12px rgba(0,0,0,0.8); }
        
        .page-header { display:flex; align-items:center; gap:20px; margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.8rem; }
        .back-btn { color:#fbbf24; text-decoration:none; font-size:1.2rem; }
        
        .timer-card { background:#1e2a3a; border-radius:20px; padding:15px; margin-bottom:20px; text-align:center; border:1px solid #fbbf24; }
        .timer-label { color:#a5b4cb; font-size:0.9rem; }
        .timer-value { color:#fbbf24; font-size:2rem; font-weight:700; font-family:monospace; }
        
        .amount-card { background:#1e2a3a; border-radius:20px; padding:20px; margin-bottom:20px; text-align:center; }
        .amount-label { color:#a5b4cb; font-size:0.9rem; }
        .amount-value { color:white; font-size:2.5rem; font-weight:700; margin:10px 0; }
        
        .warning-box { background:#2d1f1f; border-radius:15px; padding:15px; margin-bottom:20px; display:flex; gap:10px; border-left:4px solid #f87171; }
        .warning-icon { color:#f87171; font-size:1.2rem; }
        .warning-text { color:#fecaca; font-size:0.9rem; line-height:1.4; }
        
        .bank-details { background:#1e2a3a; border-radius:20px; padding:20px; margin-bottom:20px; }
        .detail-row { display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-bottom:1px solid #2d3a4b; }
        .detail-row:last-child { border-bottom:none; }
        .detail-label { color:#a5b4cb; }
        .detail-value { color:white; font-weight:500; }
        .copy-btn { background:#273649; color:#fbbf24; border:none; padding:5px 15px; border-radius:20px; cursor:pointer; font-size:0.8rem; transition:0.2s; }
        .copy-btn:hover { background:#3d4a5f; }
        
        .upload-section { background:#1e2a3a; border-radius:20px; padding:20px; margin-bottom:20px; }
        .upload-btn { display:block; background:#273649; border:2px dashed #fbbf24; border-radius:20px; padding:20px; text-align:center; color:#a5b4cb; cursor:pointer; transition:0.2s; }
        .upload-btn:hover { background:#2d3a4b; }
        .upload-btn i { font-size:2rem; color:#fbbf24; margin-bottom:10px; display:block; }
        
        .form-group { margin-bottom:20px; }
        .form-group input { width:100%; padding:16px; background:#1e2a3a; border:1px solid #2d3a4b; border-radius:20px; color:white; font-size:1rem; }
        .form-group input:focus { outline:none; border-color:#fbbf24; }
        
        .payment-btn { width:100%; background:linear-gradient(105deg,#1e4b5e,#12303e); border:none; border-radius:40px; padding:18px; color:white; font-size:1.2rem; font-weight:700; cursor:pointer; transition:0.2s; border:1px solid #fbbf24; }
        .payment-btn:hover { transform:translateY(-2px); background:linear-gradient(105deg,#1b5f72,#164c5e); }
        
        .bottom-nav { position:absolute; bottom:0; left:0; right:0; background:#0f1a28; display:flex; justify-content:space-around; padding:12px 16px 20px; border-top:1px solid #263340; border-radius:30px 30px 0 0; }
        .nav-item { display:flex; flex-direction:column; align-items:center; color:#6b7e99; font-size:0.7rem; text-decoration:none; }
        .nav-item i { font-size:1.4rem; margin-bottom:4px; }
        .nav-item.active { color:#fbbf24; }
        
        @media screen and (max-width: 399px) {
            .detail-row { flex-direction:column; gap:10px; text-align:center; }
        }
    </style>
</head>
<body>
    <div class="phone-frame">
        <div class="page-header">
            <a href="recharge_confirm.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            <h2>Deposit</h2>
            <div style="width:24px;"></div>
        </div>
        
        <!-- Expiry Timer -->
        <div class="timer-card">
            <div class="timer-label">Expire In:</div>
            <div class="timer-value" id="timer"><?= sprintf("%02d:%02d", $minutes, $seconds) ?></div>
        </div>
        
        <!-- Total Amount -->
        <div class="amount-card">
            <div class="amount-label">Total Amount to be Paid</div>
            <div class="amount-value">ETB <?= number_format($recharge_data['amount'], 2) ?></div>
        </div>
        
        <!-- Warning Messages -->
        <div class="warning-box">
            <div class="warning-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="warning-text">
                ⚠️ Please complete the payment within 15 minutes to avoid transaction failure<br>
                ⚠️ This account is for one-time use only
            </div>
        </div>
        
        <!-- Bank Details -->
        <div class="bank-details">
            <div class="detail-row">
                <span class="detail-label">Bank Name</span>
                <span class="detail-value"><?= $default_account['bank_name'] ?></span>
                <button class="copy-btn" onclick="copyText('<?= $default_account['bank_name'] ?>')">COPY</button>
            </div>
            <div class="detail-row">
                <span class="detail-label">Account Name</span>
                <span class="detail-value"><?= $default_account['account_name'] ?></span>
                <button class="copy-btn" onclick="copyText('<?= $default_account['account_name'] ?>')">COPY</button>
            </div>
            <div class="detail-row">
                <span class="detail-label">Account Number</span>
                <span class="detail-value"><?= $default_account['account_number'] ?></span>
                <button class="copy-btn" onclick="copyText('<?= $default_account['account_number'] ?>')">COPY</button>
            </div>
            <div class="detail-row">
                <span class="detail-label">Final Pay Amount</span>
                <span class="detail-value" style="color:#fbbf24; font-weight:700;">ETB <?= number_format($recharge_data['amount'], 2) ?></span>
                <span></span>
            </div>
        </div>
        
        <!-- Upload Payment Slip -->
        <div class="upload-section">
            <div class="upload-btn" onclick="document.getElementById('slipUpload').click()">
                <i class="fas fa-cloud-upload-alt"></i>
                Upload Payment Slip
                <span style="display:block; font-size:0.8rem; margin-top:5px;">Click to upload (JPG, PNG)</span>
            </div>
            <input type="file" id="slipUpload" accept="image/*" style="display:none;" onchange="showFileName(this)">
            <div id="fileName" style="color:#a5b4cb; font-size:0.8rem; margin-top:10px; text-align:center;"></div>
        </div>
        
        <!-- Payment Form -->
        <form method="POST" id="paymentForm">
            <div class="form-group">
                <input type="text" name="transaction_id" placeholder="Enter Payment Transaction ID" required>
            </div>
            
            <button type="submit" name="confirm_payment" class="payment-btn" onclick="return validateForm()">
                <i class="fas fa-check-circle"></i> I have made the payment
            </button>
        </form>
        
        <div class="bottom-nav">
            <a href="home.php" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="product.php" class="nav-item"><i class="fas fa-cube"></i><span>Product</span></a>
            <a href="official.php" class="nav-item"><i class="fas fa-bullhorn"></i><span>Official</span></a>
            <a href="team.php" class="nav-item"><i class="fas fa-users"></i><span>Team</span></a>
            <a href="profile.php" class="nav-item"><i class="fas fa-user"></i><span>Mine</span></a>
        </div>
    </div>
    
    <script>
        // Timer countdown
        let timeLeft = <?= $time_remaining ?>;
        const timerElement = document.getElementById('timer');
        
        function updateTimer() {
            if (timeLeft <= 0) {
                timerElement.textContent = "00:00";
                window.location.href = "recharge.php?expired=1";
                return;
            }
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            timeLeft--;
        }
        
        setInterval(updateTimer, 1000);
        
        // Copy to clipboard function
        function copyText(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Copied to clipboard: ' + text);
            }, function() {
                alert('Failed to copy');
            });
        }
        
        // Show file name when uploaded
        function showFileName(input) {
            if (input.files && input.files[0]) {
                document.getElementById('fileName').textContent = 'Selected: ' + input.files[0].name;
            }
        }
        
        // Validate form before submit
        function validateForm() {
            const transactionId = document.querySelector('input[name="transaction_id"]').value;
            if (!transactionId) {
                alert('Please enter transaction ID');
                return false;
            }
            return confirm('Have you made the payment? Click OK to confirm.');
        }
    </script>
</body>
</html>