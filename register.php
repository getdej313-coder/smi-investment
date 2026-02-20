<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if there's a referral code in URL
$ref_code = $_GET['ref'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $otp = $_POST['otp'] ?? ''; // not validated
    $invitation_code = $_POST['invitation_code'] ?? '';

    $errors = [];
    if (empty($full_name)) $errors[] = "Full name is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required";
    if (empty($phone)) $errors[] = "Phone required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm) $errors[] = "Passwords do not match";

    // Validate invitation code if provided
    $referred_by = null;
    if (!empty($invitation_code)) {
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE referral_code = ?");
        $stmt->execute([$invitation_code]);
        $referrer = $stmt->fetch();
        if ($referrer) {
            $referred_by = $referrer['id'];
        } else {
            $errors[] = "Invalid invitation code";
        }
    }

    if (empty($errors)) {
        // Check if phone or email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email or phone already registered";
        } else {
            // Generate unique referral code for new user
            $new_ref_code = 'SM' . strtoupper(substr(md5($email . time()), 0, 6));
            
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, plan, referral_code, referred_by, created_at) VALUES (?, ?, ?, ?, 'Basic', ?, ?, NOW())");
            
            if ($stmt->execute([$full_name, $email, $phone, $hashed, $new_ref_code, $referred_by])) {
                $new_user_id = $pdo->lastInsertId();
                
                // If user was referred, create referral earning record (optional)
                if ($referred_by) {
                    // You can add referral bonus logic here
                    // For example, give 50 ETB bonus to referrer
                    $bonus_amount = 50; // Adjust as needed
                    $pdo->prepare("INSERT INTO referral_earnings (user_id, referred_user_id, amount, created_at) VALUES (?, ?, ?, NOW())")
                        ->execute([$referred_by, $new_user_id, $bonus_amount]);
                    
                    // You could also give bonus to new user
                    // $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([20, $new_user_id]);
                }
                
                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['registration_success'] = true;
                redirect('home.php');
            } else {
                $errors[] = "Registration failed, try again";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Smi Investment - Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Inter', sans-serif; }
        body { 
            background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%); 
            min-height:100vh; 
            display:flex; 
            align-items:center; 
            justify-content:center; 
            padding:1.5rem; 
        }
        
        .card { 
            background: rgba(255,255,255,0.95); 
            max-width:500px; 
            width:100%; 
            border-radius:2.5rem; 
            padding:2.5rem 2rem; 
            box-shadow:0 30px 60px rgba(0,0,0,0.3);
            position: relative; /* for loader overlay */
        }
        
        .brand { 
            text-align:center; 
            margin-bottom:2rem; 
        }
        
        .brand h1 { 
            font-size:2.2rem; 
            background: linear-gradient(135deg,#1e4b5e,#0f2c3a); 
            -webkit-background-clip:text; 
            background-clip:text; 
            color:transparent; 
            display:inline-flex; 
            align-items:center; 
            gap:10px; 
        }
        
        .brand h1 i { 
            color:#d4a017; 
            font-size:2rem; 
            background:#0f2c3a; 
            padding:10px; 
            border-radius:50%; 
        }
        
        .brand p {
            color:#4a6572;
            margin-top:5px;
        }
        
        .invite-badge {
            background: #f0f5f9;
            border: 1px dashed #d4a017;
            border-radius: 40px;
            padding: 12px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }
        
        .invite-badge i {
            color: #d4a017;
            font-size: 1.2rem;
        }
        
        .invite-badge span {
            color: #1e3b4a;
            font-weight: 600;
        }
        
        .invite-code {
            background: #d4a017;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-left: auto;
        }
        
        .input-group { 
            margin-bottom:1.5rem; 
        }
        
        .input-group label { 
            display:block; 
            font-weight:600; 
            color:#1e3b4a; 
            margin-bottom:6px; 
        }
        
        .input-group label i {
            color: #d4a017;
            margin-right: 5px;
        }
        
        .input-wrapper { 
            display:flex; 
            align-items:center; 
            background:white; 
            border:1.5px solid #e0e9ef; 
            border-radius:18px; 
            padding:0 16px; 
            transition: 0.2s;
        }
        
        .input-wrapper:focus-within {
            border-color: #d4a017;
            box-shadow: 0 0 0 3px rgba(212,160,23,0.1);
        }
        
        .input-wrapper input { 
            width:100%; 
            border:none; 
            padding:16px 0; 
            font-size:1rem; 
            background:transparent; 
            outline:none; 
        }
        
        .phone-prefix { 
            background:#f0f5f9; 
            padding:8px 16px; 
            border-radius:40px; 
            font-weight:600; 
            color:#1e4b5e; 
            margin-right:12px; 
        }
        
        .otp-row { 
            display:flex; 
            gap:12px; 
        }
        
        .otp-row .send-btn { 
            flex:0 0 100px;
            background:#eef3f7; 
            border:1.5px solid #cbd8e2; 
            border-radius:18px; 
            font-weight:600; 
            color:#1e4b5e; 
            cursor:pointer; 
            display:flex; 
            align-items:center; 
            justify-content:center; 
            gap:8px;
            transition: 0.2s;
        }
        
        .otp-row .send-btn:hover {
            background: #d4a017;
            color: white;
            border-color: #b3860f;
        }
        
        .register-btn { 
            width:100%; 
            background:linear-gradient(105deg,#1e4b5e,#12303e); 
            border:none; 
            border-radius:40px; 
            padding:18px; 
            color:white; 
            font-size:1.2rem; 
            font-weight:700; 
            cursor:pointer; 
            margin-top:20px;
            transition: 0.2s;
            border: 1px solid #d4a017;
        }
        
        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(0,0,0,0.3);
        }
        
        .login-link { 
            text-align:center; 
            margin-top:2rem; 
            color:#365f6e; 
        }
        
        .login-link a { 
            color:#d4a017; 
            font-weight:700; 
            text-decoration:none;
            margin-left: 5px;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .error { 
            color:#b91c1c; 
            background:#fee2e2; 
            padding:12px; 
            border-radius:30px; 
            margin-bottom:20px; 
            text-align:center;
        }
        
        .error-list { 
            color:#b91c1c; 
            background:#fee2e2; 
            padding:12px 20px; 
            border-radius:30px; 
            margin-bottom:20px; 
            list-style:disc inside; 
        }
        
        .success-message {
            color:#166534;
            background:#dcfce7;
            padding:12px;
            border-radius:30px;
            margin-bottom:20px;
            text-align:center;
        }
        
        .terms {
            text-align: center;
            color: #94a3b8;
            font-size: 0.8rem;
            margin-top: 20px;
        }
        
        .terms a {
            color: #d4a017;
            text-decoration: none;
        }
        
        /* Loader overlay */
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
            z-index: 100;
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
        
        @media screen and (max-width: 480px) {
            .card {
                padding: 2rem 1.5rem;
            }
            
            .otp-row {
                flex-direction: column;
            }
            
            .otp-row .send-btn {
                flex: 1;
                padding: 12px;
            }
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
            <p>Create your investment account</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <ul class="error-list">
                <?php foreach($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        
        <!-- Show invitation badge if referral code exists -->
        <?php if (!empty($ref_code)): ?>
            <div class="invite-badge">
                <i class="fas fa-gift"></i>
                <span>You've been invited!</span>
                <span class="invite-code">Code: <?= htmlspecialchars($ref_code) ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="registerForm">
            <!-- Hidden field for invitation code -->
            <input type="hidden" name="invitation_code" id="invitation_code" value="<?= htmlspecialchars($ref_code) ?>">
            
            <div class="input-group">
                <label><i class="fas fa-user"></i> User Name</label>
                <div class="input-wrapper">
                    <input type="text" name="full_name" placeholder="Enter user name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                </div>
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <div class="input-wrapper">
                    <input type="email" name="email" placeholder="john@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-phone-alt"></i> Phone number</label>
                <div class="input-wrapper">
                    <span class="phone-prefix"><i class="fas fa-flag"></i> +251</span>
                    <input type="tel" name="phone" placeholder="91 234 5678" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                </div>
            </div>
            
            <!-- Manual invitation code entry (optional) -->
            <div class="input-group">
                <label><i class="fas fa-ticket-alt"></i> Invitation Code (Optional)</label>
                <div class="input-wrapper">
                    <i class="fas fa-gift" style="color:#d4a017;"></i>
                    <input type="text" name="manual_invitation" id="manual_invitation" placeholder="Enter invitation code if you have one" value="<?= htmlspecialchars($_POST['manual_invitation'] ?? '') ?>">
                </div>
                <small style="color:#94a3b8; display:block; margin-top:5px;">Enter invitation code if you were invited by a friend</small>
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-key" style="color:#d4a017;"></i>
                    <input type="password" name="password" placeholder="Minimum 6 characters" required>
                </div>
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-check-circle"></i> Confirm Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-check-double" style="color:#d4a017;"></i>
                    <input type="password" name="confirm_password" placeholder="··········" required>
                </div>
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-mobile-alt"></i> OTP verification</label>
                <div class="otp-row">
                    <div class="input-wrapper">
                        <i class="fas fa-qrcode" style="color:#d4a017;"></i>
                        <input type="text" name="otp" placeholder="4A1OPP" value="<?= htmlspecialchars($_POST['otp'] ?? '') ?>">
                    </div>
                    <button type="button" class="send-btn" id="sendOtpBtn"><i class="fas fa-paper-plane"></i> Send</button>
                </div>
            </div>
            
            <div class="terms">
                By registering, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
            </div>
            
            <button type="submit" class="register-btn"><i class="fas fa-user-plus"></i> Create Account</button>
            
            <div class="login-link">
                Already have an account? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login Now</a>
            </div>
        </form>
    </div>
    
    <script>
    // OTP Generation
    document.getElementById('sendOtpBtn').addEventListener('click', function(e) {
        e.preventDefault();
        // Generate random 6-digit OTP
        const otp = Math.floor(100000 + Math.random() * 900000);
        document.querySelector('input[name="otp"]').value = otp;
        alert('🔐 Your OTP is: ' + otp);
        
        // Visual feedback
        this.innerHTML = '<i class="fas fa-check"></i> Sent!';
        this.style.background = '#163a30';
        this.style.color = '#4ade80';
        this.style.borderColor = '#2c7a5a';
        
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-paper-plane"></i> Send';
            this.style.background = '#eef3f7';
            this.style.color = '#1e4b5e';
            this.style.borderColor = '#cbd8e2';
        }, 2000);
    });
    
    // Manual invitation code handling
    document.getElementById('manual_invitation').addEventListener('input', function() {
        document.getElementById('invitation_code').value = this.value;
    });
    
    // Auto-format phone number
    document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 3) {
            value = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6, 10);
        }
        this.value = value;
    });

    // Form submission with 5-second loader delay
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Stop immediate submission

        const form = this;
        const loader = document.getElementById('loaderOverlay');
        const submitBtn = form.querySelector('button[type="submit"]');

        // --- Client-side validation (reusing existing checks) ---
        const password = form.querySelector('input[name="password"]').value;
        const confirm = form.querySelector('input[name="confirm_password"]').value;
        const phone = form.querySelector('input[name="phone"]').value;
        const otp = form.querySelector('input[name="otp"]').value;
        const fullName = form.querySelector('input[name="full_name"]').value.trim();
        const email = form.querySelector('input[name="email"]').value.trim();

        if (!fullName) {
            alert('Full name is required');
            return false;
        }
        if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
            alert('Valid email required');
            return false;
        }
        if (!phone) {
            alert('Phone required');
            return false;
        }
        if (password.length < 6) {
            alert('Password must be at least 6 characters');
            return false;
        }
        if (password !== confirm) {
            alert('Passwords do not match');
            return false;
        }
        if (!/^\d{9,}$/.test(phone.replace(/\s/g, ''))) {
            alert('Please enter a valid phone number');
            return false;
        }
        if (!otp) {
            alert('Please generate OTP first');
            return false;
        }

        // --- Show loader and disable button ---
        loader.style.display = 'flex';
        submitBtn.disabled = true;

        // Wait 5 seconds, then submit the form
        setTimeout(function() {
            form.submit();
        }, 5000);
    });
    </script>
</body>

</html>

