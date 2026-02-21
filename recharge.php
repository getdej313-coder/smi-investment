<?php
require_once 'config/session.php';
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get available banks
$banks = $pdo->query("SELECT * FROM banks WHERE is_active = 1")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'] ?? 0;
    $bank_id = $_POST['bank_id'] ?? 0;
    
    if ($amount < 100) {
        $error = "Minimum recharge amount is ETB 100";
    } elseif (!$bank_id) {
        $error = "Please select a bank";
    } else {
        // Store in session for confirmation
        $_SESSION['recharge_data'] = [
            'amount' => $amount,
            'bank_id' => $bank_id,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+15 minutes'))
        ];
        header("Location: recharge_confirm.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recharge - Smi Investment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }
        body { background:#0b1424; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px; }
        
        /* Main container - fully responsive */
        .phone-frame { 
            max-width:400px; 
            width:100%; 
            background:#101b2b; 
            border-radius:36px; 
            padding:24px 20px 80px; 
            position:relative; 
            box-shadow:0 25px 50px -12px rgba(0,0,0,0.8); 
            margin:0 auto;
            transition: all 0.3s ease;
        }
        
        .page-header { 
            display:flex; 
            align-items:center; 
            gap:20px; 
            margin-bottom:30px; 
        }
        .page-header h2 { 
            color:white; 
            font-size:1.8rem; 
        }
        .back-btn { 
            color:#fbbf24; 
            text-decoration:none; 
            font-size:1.2rem; 
            width:40px;
            height:40px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#1e2a3a;
            border-radius:12px;
            transition:0.2s;
            border:1px solid #2d3a4b;
        }
        .back-btn:hover { 
            background:#273649; 
            transform:scale(1.05);
        }
        
        .balance-card { 
            background:#1e2a3a; 
            border-radius:20px; 
            padding:20px; 
            margin-bottom:25px; 
            text-align:center; 
            border:1px solid #fbbf24;
            box-shadow:0 6px 0 #0f172a;
        }
        .balance-label { 
            color:#a5b4cb; 
            font-size:0.9rem; 
        }
        .balance-value { 
            color:#fbbf24; 
            font-size:2rem; 
            font-weight:700; 
            margin:10px 0; 
        }
        
        .form-group { 
            margin-bottom:20px; 
        }
        .form-group label { 
            color:#a5b4cb; 
            display:block; 
            margin-bottom:8px; 
            font-weight:500; 
        }
        .form-group input, .form-group select { 
            width:100%; 
            padding:16px; 
            background:#1e2a3a; 
            border:1px solid #2d3a4b; 
            border-radius:20px; 
            color:white; 
            font-size:1rem; 
            transition:0.2s;
        }
        .form-group input:focus, .form-group select:focus { 
            outline:none; 
            border-color:#fbbf24; 
            box-shadow:0 0 0 3px rgba(251,191,36,0.1);
        }
        
        .bank-options { 
            display:grid; 
            grid-template-columns:1fr 1fr; 
            gap:12px; 
            margin-bottom:20px; 
        }
        .bank-option { 
            background:#1e2a3a; 
            border:2px solid #2d3a4b; 
            border-radius:20px; 
            padding:15px; 
            text-align:center; 
            cursor:pointer; 
            transition:0.2s;
            box-shadow:0 4px 0 #0f172a;
        }
        .bank-option.selected { 
            border-color:#fbbf24; 
            background:#273649; 
            transform:translateY(-2px);
            box-shadow:0 6px 0 #0f172a;
        }
        .bank-option i { 
            font-size:2rem; 
            color:#fbbf24; 
            margin-bottom:10px; 
        }
        .bank-option span { 
            display:block; 
            color:white; 
            font-weight:600; 
        }
        
        .proceed-btn { 
            width:100%; 
            background:linear-gradient(105deg,#1e4b5e,#12303e); 
            border:none; 
            border-radius:40px; 
            padding:18px; 
            color:white; 
            font-size:1.2rem; 
            font-weight:700; 
            cursor:pointer; 
            transition:0.2s; 
            border:1px solid #fbbf24; 
            box-shadow:0 6px 0 #0f172a;
        }
        .proceed-btn:hover { 
            transform:translateY(-2px); 
            background:linear-gradient(105deg,#1b5f72,#164c5e); 
        }
        .proceed-btn:disabled { 
            opacity:0.6; 
            cursor:not-allowed; 
            transform:none; 
        }
        
        .info-box { 
            background:#1e2a3a; 
            border-radius:20px; 
            padding:15px; 
            margin:20px 0; 
            display:flex; 
            gap:12px; 
            border-left:4px solid #fbbf24; 
        }
        .info-icon { 
            color:#fbbf24; 
            font-size:1.2rem; 
        }
        .info-text { 
            color:#a5b4cb; 
            font-size:0.9rem; 
            line-height:1.4; 
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
        
        /* Loader overlay */
        .loader-container {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(16,27,43,0.9);
            border-radius: 36px;
            align-items: center;
            justify-content: center;
            z-index: 100;
            backdrop-filter: blur(2px);
        }
        .loader {
            width: 44.8px;
            height: 44.8px;
            color: #fbbf24;
            position: relative;
            background: radial-gradient(11.2px,currentColor 94%,#0000);
        }
        .loader:before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: radial-gradient(10.08px at bottom right,#0000 94%,currentColor) top left,
                        radial-gradient(10.08px at bottom left ,#0000 94%,currentColor) top right,
                        radial-gradient(10.08px at top    right,#0000 94%,currentColor) bottom left,
                        radial-gradient(10.08px at top    left ,#0000 94%,currentColor) bottom right;
            background-size: 22.4px 22.4px;
            background-repeat: no-repeat;
            animation: loader 1.5s infinite cubic-bezier(0.3,1,0,1);
        }
        @keyframes loader {
            33% { inset: -11.2px; transform: rotate(0deg); }
            66% { inset: -11.2px; transform: rotate(90deg); }
            100% { inset: 0; transform: rotate(90deg); }
        }
        
        /* ===== RESPONSIVE BREAKPOINTS ===== */
        
        /* Small mobile devices (below 400px) */
        @media screen and (max-width: 399px) {
            body { padding: 10px; }
            .phone-frame { padding: 20px 15px 80px; }
            .bank-options { grid-template-columns:1fr; }
            .page-header h2 { font-size:1.5rem; }
            .balance-value { font-size:1.8rem; }
            .proceed-btn { font-size:1rem; padding:16px; }
            .nav-item i { font-size:1.2rem; }
            .nav-item span { font-size:0.6rem; }
        }
        
        /* Tablet styles (600px - 1024px) */
        @media screen and (min-width: 600px) and (max-width: 1024px) {
            body { padding:30px; background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%); }
            .phone-frame { max-width: 500px; border-radius: 40px; }
            .bank-options { grid-template-columns: repeat(2, 1fr); }
            .bottom-nav { padding: 15px 30px 25px; }
            .nav-item i { font-size:1.6rem; }
            .nav-item span { font-size:0.8rem; }
        }
        
        /* Desktop styles (1025px and above) */
        @media screen and (min-width: 1025px) {
            body { padding: 40px; background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%); }
            .phone-frame { max-width: 450px; border-radius: 40px; }
            .bank-options { grid-template-columns: repeat(2, 1fr); }
            .bottom-nav { padding: 15px 30px 25px; }
            .nav-item i { font-size:1.5rem; }
        }
        
        /* Landscape mode */
        @media screen and (orientation: landscape) and (max-height: 600px) {
            body { padding: 10px; }
            .phone-frame { max-width: 600px; padding: 15px 15px 70px; }
            .balance-card { padding: 10px; margin-bottom: 15px; }
            .form-group { margin-bottom: 10px; }
            .bank-options { grid-template-columns: repeat(4, 1fr); }
        }
        
        /* High-resolution screens */
        @media screen and (min-width: 1440px) {
            .phone-frame { max-width: 500px; }
        }
    </style>
</head>
<body>
    <div class="phone-frame">
        <!-- Loader overlay -->
        <div class="loader-container" id="loaderOverlay">
            <div class="loader"></div>
        </div>

        <div class="page-header">
            <a href="home.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            <h2>Recharge</h2>
            <div style="width:24px;"></div> <!-- spacer -->
        </div>
        
        <div class="balance-card">
            <div class="balance-label">Current Balance</div>
            <div class="balance-value">ETB <?= number_format($user['balance'], 2) ?></div>
        </div>
        
        <?php if (isset($error)): ?>
            <div style="background:#2d1f1f; color:#f87171; padding:15px; border-radius:20px; margin-bottom:20px; text-align:center;">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="rechargeForm">
            <div class="info-box">
                <div class="info-icon"><i class="fas fa-info-circle"></i></div>
                <div class="info-text">Minimum recharge amount is ETB 100. Your balance will be updated immediately after payment confirmation!</div>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-university" style="color:#fbbf24; margin-right:5px;"></i> Select Bank</label>
                <div class="bank-options">
                    <?php foreach ($banks as $index => $bank): ?>
                    <div class="bank-option <?= $index == 0 ? 'selected' : '' ?>" onclick="selectBank(<?= $bank['id'] ?>, this)">
                        <i class="fas fa-university"></i>
                        <span><?= htmlspecialchars($bank['bank_name']) ?></span>
                        <input type="radio" name="bank_id" value="<?= $bank['id'] ?>" <?= $index == 0 ? 'checked' : '' ?> style="display:none;">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>           

            <div class="form-group">
                <label><i class="fas fa-coins" style="color:#fbbf24; margin-right:5px;"></i> Enter Amount (ETB)</label>
                <input type="number" name="amount" min="100" step="100" placeholder="e.g., 1000" required>
            </div>
            
            <button type="submit" class="proceed-btn" id="submitBtn">Proceed to Payment</button>
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
    function selectBank(bankId, element) {
        // Remove selected class from all options
        document.querySelectorAll('.bank-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        // Add selected class to clicked option
        element.classList.add('selected');
        // Check the radio button
        element.querySelector('input[type="radio"]').checked = true;
    }

    // Form submission with loader (after confirm)
    document.getElementById('rechargeForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const loader = document.getElementById('loaderOverlay');
        const submitBtn = document.getElementById('submitBtn');

        // Client-side validation
        const amount = form.amount.value;
        const bankSelected = document.querySelector('input[name="bank_id"]:checked');

        if (!bankSelected) {
            alert('Please select a bank');
            return;
        }
        if (!amount || amount < 100) {
            alert('Amount must be at least ETB 100');
            return;
        }

        // Show loader and disable button
        loader.style.display = 'flex';
        submitBtn.disabled = true;

        // Wait 5 seconds for visual feedback, then submit
        setTimeout(function() {
            form.submit(); // This will redirect to recharge_confirm.php
        }, 5000);
    });
    </script>
</body>

</html>
