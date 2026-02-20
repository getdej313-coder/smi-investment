<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (password_verify($current, $user['password'])) {
        if ($new === $confirm && strlen($new) >= 6) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hashed, $user_id]);
            $message = ['type' => 'success', 'text' => 'Password updated successfully!'];
        } else {
            $message = ['type' => 'error', 'text' => 'New passwords do not match or are too short'];
        }
    } else {
        $message = ['type' => 'error', 'text' => 'Current password is incorrect'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Smi Investment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; }
        .page-header { display:flex; align-items:center; gap:20px; margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.6rem; }
        .back-btn { color:#fbbf24; text-decoration:none; font-size:1.2rem; }
        .form-card { background:#1e2a3a; border-radius:24px; padding:20px; border:1px solid #2d3a4b; }
        .form-group { margin-bottom:20px; }
        .form-group label { color:#a5b4cb; display:block; margin-bottom:8px; }
        .form-group input { width:100%; padding:15px; background:#0f1a28; border:1px solid #2d3a4b; border-radius:15px; color:white; }
        .reset-btn { background:#fbbf24; color:#0b1424; border:none; padding:15px; border-radius:30px; width:100%; font-weight:600; cursor:pointer; }
        .message { padding:15px; border-radius:15px; margin-bottom:20px; }
        .success { background:#163a30; color:#4ade80; }
        .error { background:#2d1f1f; color:#f87171; }
        .bottom-nav { position:absolute; bottom:0; left:0; right:0; background:#0f1a28; display:flex; justify-content:space-around; padding:12px 16px 20px; border-top:1px solid #263340; border-radius:30px 30px 0 0; }
        .nav-item { display:flex; flex-direction:column; align-items:center; color:#6b7e99; font-size:0.7rem; text-decoration:none; }
        .nav-item i { font-size:1.4rem; margin-bottom:4px; }
        .nav-item.active { color:#fbbf24; }
    </style>
</head>
<body>
    <div class="phone-frame">
        <div class="page-header">
            <a href="profile.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            <h2>Reset Password</h2>
        </div>
        
        <?php if ($message): ?>
        <div class="message <?= $message['type'] ?>">
            <i class="fas <?= $message['type'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= $message['text'] ?>
        </div>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="reset-btn">Update Password</button>
            </form>
        </div>
        
        <div class="bottom-nav">
            <a href="home.php" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="product.php" class="nav-item"><i class="fas fa-cube"></i><span>Product</span></a>
            <a href="official.php" class="nav-item"><i class="fas fa-bullhorn"></i><span>Official</span></a>
            <a href="team.php" class="nav-item"><i class="fas fa-users"></i><span>Team</span></a>
            <a href="profile.php" class="nav-item active"><i class="fas fa-user"></i><span>Mine</span></a>
        </div>
    </div>
</body>
</html>