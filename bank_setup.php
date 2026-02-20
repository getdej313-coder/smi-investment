<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_name = $_POST['account_name'] ?? '';
    $account_number = $_POST['account_number'] ?? '';
    $bank_name = $_POST['bank_name'] ?? '';
    $branch = $_POST['branch'] ?? '';
    
    // Save to database (you need to create a bank_accounts table)
    $_SESSION['bank_success'] = "Bank account saved successfully";
}

// Get saved bank accounts (placeholder)
$accounts = [
    ['bank' => 'Commercial Bank of Ethiopia', 'name' => 'John Doe', 'number' => '1000234567890', 'branch' => 'Head Office', 'default' => true],
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bank Setup - Smi Investment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.6rem; }
        .back-btn { color:#fbbf24; text-decoration:none; font-size:1.2rem; }
        .info-box { background:#1e2a3a; border-radius:20px; padding:16px; margin-bottom:24px; border:1px solid #fbbf24; display:flex; gap:12px; }
        .info-icon { color:#fbbf24; font-size:1.5rem; }
        .info-text { color:#a5b4cb; font-size:0.9rem; flex:1; }
        .bank-card { background:#1e2a3a; border-radius:24px; padding:18px; margin-bottom:16px; border:1px solid #2d3a4b; position:relative; }
        .bank-default { position:absolute; top:10px; right:10px; background:#fbbf24; color:#0b1424; padding:4px 12px; border-radius:20px; font-size:0.7rem; font-weight:600; }
        .bank-logo { width:50px; height:50px; background:#273649; border-radius:25px; display:flex; align-items:center; justify-content:center; margin-bottom:10px; }
        .bank-logo i { color:#fbbf24; font-size:1.5rem; }
        .bank-name { color:white; font-weight:600; font-size:1.1rem; margin-bottom:5px; }
        .bank-details { color:#a5b4cb; font-size:0.9rem; margin-bottom:3px; }
        .bank-actions { display:flex; gap:10px; margin-top:15px; }
        .bank-btn { flex:1; padding:10px; border-radius:20px; text-align:center; text-decoration:none; font-size:0.9rem; }
        .btn-edit { background:#273649; color:white; }
        .btn-delete { background:#2d1f1f; color:#f87171; }
        .btn-primary { background:#fbbf24; color:#0b1424; border:none; border-radius:40px; padding:16px; width:100%; font-weight:600; cursor:pointer; }
        .form-group { margin-bottom:16px; }
        .form-group label { color:#a5b4cb; display:block; margin-bottom:8px; }
        .form-group input { width:100%; padding:16px; background:#1e2a3a; border:1px solid #2d3a4b; border-radius:20px; color:white; }
        .success { color:#166534; background:#dcfce7; padding:12px; border-radius:30px; margin-bottom:20px; }
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
            <h2>Bank Setup</h2>
            <a href="#" onclick="showAddForm()" style="color:#fbbf24;"><i class="fas fa-plus-circle"></i></a>
        </div>
        
        <div class="info-box">
            <div class="info-icon"><i class="fas fa-shield-alt"></i></div>
            <div class="info-text">Your bank information is encrypted and secure. Withdrawals will be sent to your default bank account.</div>
        </div>
        
        <?php if(isset($_SESSION['bank_success'])): ?>
            <div class="success"><i class="fas fa-check-circle"></i> <?= $_SESSION['bank_success'] ?></div>
            <?php unset($_SESSION['bank_success']); ?>
        <?php endif; ?>
        
        <!-- Add Bank Form (hidden by default) -->
        <div id="addForm" style="display: none; background:#1e2a3a; border-radius:24px; padding:20px; margin-bottom:20px;">
            <h3 style="color:white; margin-bottom:15px;">Add New Bank Account</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Account Holder Name</label>
                    <input type="text" name="account_name" required>
                </div>
                <div class="form-group">
                    <label>Account Number</label>
                    <input type="text" name="account_number" required>
                </div>
                <div class="form-group">
                    <label>Bank Name</label>
                    <input type="text" name="bank_name" required>
                </div>
                <div class="form-group">
                    <label>Branch</label>
                    <input type="text" name="branch">
                </div>
                <button type="submit" class="btn-primary">Save Account</button>
            </form>
        </div>
        
        <!-- Saved Banks -->
        <h3 style="color:white; margin-bottom:15px;">Your Bank Accounts</h3>
        
        <?php foreach ($accounts as $acc): ?>
        <div class="bank-card">
            <?php if($acc['default']): ?>
            <span class="bank-default">Default</span>
            <?php endif; ?>
            <div class="bank-logo">
                <i class="fas fa-university"></i>
            </div>
            <div class="bank-name"><?= $acc['bank'] ?></div>
            <div class="bank-details"><?= $acc['name'] ?></div>
            <div class="bank-details">Account: <?= $acc['number'] ?></div>
            <div class="bank-details">Branch: <?= $acc['branch'] ?></div>
            <div class="bank-actions">
                <a href="#" class="bank-btn btn-edit">Edit</a>
                <a href="#" class="bank-btn btn-delete">Delete</a>
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
    
    <script>
        function showAddForm() {
            document.getElementById('addForm').style.display = 'block';
        }
    </script>
</body>
</html>