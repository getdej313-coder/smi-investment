<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
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
            redirect('home.php');
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
    <title>Smi Investment - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Base styles from original login page */
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
        .otp-row { display:flex; gap:12px; }
        .otp-row .send-btn { flex:1; background:#eef3f7; border:1.5px solid #cbd8e2; border-radius:18px; font-weight:600; color:#1e4b5e; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; }
        .login-btn { width:100%; background:linear-gradient(105deg,#1e4b5e,#12303e); border:none; border-radius:40px; padding:18px; color:white; font-size:1.2rem; font-weight:700; cursor:pointer; margin-top:20px; }
        .register-link { text-align:center; margin-top:2rem; color:#365f6e; }
        .register-link a { color:#d4a017; font-weight:700; text-decoration:none; }
        .error { color:#b91c1c; background:#fee2e2; padding:12px; border-radius:30px; margin-bottom:20px; text-align:center; }

        /* Loader styles from Uiverse.io */
        .loader-container {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.8);
            border-radius: 2.5rem;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        .loader {
            width: 44.8px;
            height: 44.8px;
            color: #554cb5;
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
    </style>
</head>
<body>
    <div class="card">
        <!-- Loader overlay -->
        <div class="loader-container" id="loaderOverlay">
            <div class="loader"></div>
        </div>

        <div class="brand">
            <h1><i class="fas fa-chart-line"></i> Smi Investment</h1>
        </div>
        <?php if (isset($error)): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>
        <form method="POST" id="loginForm">
            <div class="input-group">
                <label><i class="fas fa-phone-alt"></i> Phone number</label>
                <div class="input-wrapper">
                    <span class="phone-prefix"><i class="fas fa-flag"></i> +251</span>
                    <input type="tel" name="phone" placeholder="Enter phone number" required>
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

    <script>
        // Show loader for 5 seconds before submitting
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Stop immediate form submission
            
            const form = this;
            const loader = document.getElementById('loaderOverlay');
            
            // Basic client-side validation (optional)
            const phone = form.phone.value.trim();
            const password = form.password.value.trim();
            if (!phone || !password) {
                alert('Please fill all fields');
                return;
            }
            
            // Show loader
            loader.style.display = 'flex';
            
            // Disable the submit button to prevent double-click
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            
            // Wait 5 seconds, then submit the form
            setTimeout(function() {
                form.submit(); // This will send the data and redirect (or reload with error)
            }, 5000); // 5000ms = 5 seconds
        });
    </script>
</body>
</html>