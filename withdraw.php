<?php
require_once 'config/session.php';
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Get user balance
$stmt = $pdo->prepare("SELECT balance, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'] ?? 0;
    if ($amount <= 0) {
        $error = "Invalid amount";
    } elseif ($amount > $user['balance']) {
        $error = "Insufficient balance";
    } else {
        // Mask phone number
        $phone = $user['phone'];
        $masked = substr($phone, 0, 2) . '******' . substr($phone, -2);
        // Insert withdrawal request
        $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id, phone_masked, amount, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
        if ($stmt->execute([$user_id, $masked, $amount])) {
            $success = "Withdrawal request submitted successfully!";
        } else {
            $error = "Failed to submit request";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Withdraw - Smi Investment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-header h2 { color:white; }
        .balance { color:#fbbf24; font-size:1.4rem; margin-bottom:20px; }
        .form-group { margin-bottom:20px; }
        .form-group label { color:#a5b4cb; display:block; margin-bottom:8px; }
        .form-group input { width:100%; padding:16px; border-radius:20px; border:none; background:#1e2a3a; color:white; font-size:1rem; border:1px solid #2d3a4b; }
        .btn { width:100%; background:linear-gradient(105deg,#1e4b5e,#12303e); border:none; border-radius:40px; padding:18px; color:white; font-size:1.2rem; font-weight:700; cursor:pointer; }
        .error { color:#b91c1c; background:#fee2e2; padding:12px; border-radius:30px; margin-bottom:20px; }
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
            <h2>Withdraw</h2>
            <a href="home.php" style="color:#fbbf24;"><i class="fas fa-home"></i></a>
        </div>
        <div class="balance">Available: ETB <?= number_format($user['balance'], 2) ?></div>
        <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Amount (ETB)</label>
                <input type="number" name="amount" step="0.01" min="1" required>
            </div>
            <button type="submit" class="btn">Request Withdrawal</button>
        </form>
       
    </div>
</body>

</html>
