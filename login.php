<?php
// login.php
// Start output buffering at the VERY beginning
ob_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = $_POST['password'] ?? '';
    if (!empty($phone) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch(); 
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['balance'] = $user['balance'];
            
            // Clear the output buffer and redirect
            ob_end_clean(); // This clears any output before the header
            header("Location: home.php");
            exit;
        } else {
            $error = "Invalid phone or password";
        }
    } else {
        $error = "Please fill all fields";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem; }
        .card { background: rgba(255,255,255,0.95); max-width:480px; width:100%; border-radius:2.5rem; padding:2.5rem 2rem; box-shadow:0 30px 60px rgba(0,0,0,0.3); position: relative; }
        .brand { text-align:center; margin-bottom:2rem; }
        .brand h1 { font-size:2.2rem; background: linear-gradient(135deg,#1e4b5e,#0f2c3a); -webkit-background-clip:text; background-clip:text; color:transparent; display:inline-flex; align-items:center; gap:10px; }
        .brand h1 i { color:#d4a017; font-size:2rem; background:#0f2c3a; padding:10px; border-radius:50%; }
        .input-group { margin-bottom:1.5rem; }
        .input-group label { display:block; font-weight:600; color:#1e3b4a; margin-bottom:6px; }
        .input-wrapper { display:flex; align-items:center; background:white; border:1.5px solid #e0e9ef; border-radius:18px; padding:0 16px; }
        .input-wrapper input { width:100%; border:none; padding:16px 0; font-size:1rem; background:transparent; outline:none; }
        .phone-prefix { background:#f0f5f9; padding:8px 16px; border-radius:40px; font-weight:600; color:#1e4b5e; margin-right:12px; }
        .login-btn { width:100%; background:linear-gradient(105deg,#1e4b5e,#12303e); border:none; border-radius:40px; padding:18px; color:white; font-size:1.2rem; font-weight:700; cursor:pointer; margin-top:20px; transition:0.2s; }
        .login-btn:hover { transform:translateY(-2px); background:linear-gradient(105deg,#1b5f72,#164c5e); }
        .register-link { text-align:center; margin-top:2rem; color:#365f6e; }
        .register-link a { color:#d4a017; font-weight:700; text-decoration:none; }
        .error { color:#b91c1c; background:#fee2e2; padding:12px; border-radius:30px; margin-bottom:20px; text-align:center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">
            <h1><i class="fas fa-chart-line"></i>user login</h1>
        </div> 
        <?php if (!empty($error)): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="input-group">
                <label><i class="fas fa-phone-alt"></i> Phone number</label>
                <div class="input-wrapper">
                    <span class="phone-prefix"><i class="fas fa-flag"></i> +251</span>
                    <input type="tel" name="phone" placeholder="Enter phone number" value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" required>
                </div>
            </div>
            <div class="input-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-key" style="color:#b0c4d1;"></i>
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>
            </div>
            <button type="submit" class="login-btn"><i class="fas fa-sign-in-alt"></i> Login</button>
            <div class="register-link">
                Don't have an account? <a href="register.php"><i class="fas fa-user-plus"></i> Register Now</a>
            </div>
        </form>
    </div>
</body>
</html>
<?php
// End output buffering
ob_end_flush();
?>
