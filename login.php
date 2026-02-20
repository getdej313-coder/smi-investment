<?php
// Don't start session here - it's already in database.php
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // First check if is_admin column exists
        $checkColumn = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
        if ($checkColumn->rowCount() == 0) {
            // Column doesn't exist, create it
            $pdo->exec("ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0");
        }
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR phone = ?)");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // Check if user is admin (either is_admin=1 or first user)
            if (isset($admin['is_admin']) && $admin['is_admin'] == 1) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                header("Location: index.php");
                exit;
            } else {
                $error = "You don't have admin privileges";
            }
        } else {
            $error = "Invalid credentials";
        }
    } catch (PDOException $e) {
        $error = "Login error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Smi Investment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            background:#0b1424; 
            display:flex; 
            align-items:center; 
            justify-content:center; 
            min-height:100vh; 
            font-family:'Inter',sans-serif; 
            padding:16px;
        }
        .login-box { 
            background:#101b2b; 
            padding:40px; 
            border-radius:30px; 
            width:100%;
            max-width:400px; 
            box-shadow:0 20px 40px rgba(0,0,0,0.5);
            border:1px solid #2d3a4b;
        }
        .login-box h2 { 
            color:#fbbf24; 
            text-align:center; 
            margin-bottom:30px;
            font-size:2rem;
        }
        .login-box h2 i {
            margin-right:10px;
        }
        input { 
            width:100%; 
            padding:16px; 
            margin-bottom:20px; 
            border-radius:20px; 
            border:none; 
            background:#1e2a3a; 
            color:white;
            font-size:1rem;
            border:1px solid #2d3a4b;
        }
        input:focus {
            outline:none;
            border-color:#fbbf24;
        }
        button { 
            width:100%; 
            padding:16px; 
            background:#fbbf24; 
            border:none; 
            border-radius:20px; 
            font-weight:bold;
            font-size:1.1rem;
            cursor:pointer;
            transition:0.2s;
        }
        button:hover {
            transform:translateY(-2px);
            background:#e6ac1a;
        }
        .error { 
            color:#f87171; 
            background:#2d1f1f; 
            padding:12px; 
            border-radius:20px; 
            margin-bottom:20px; 
            text-align:center;
            border:1px solid #b45353;
        }
        .info-box {
            background:#1e2a3a;
            border-left:4px solid #fbbf24;
            padding:12px;
            margin-bottom:20px;
            color:#a5b4cb;
            font-size:0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2><i class="fas fa-lock"></i> Admin Login</h2>
        
        <div class="info-box">
            <i class="fas fa-info-circle" style="color:#fbbf24; margin-right:5px;"></i>
            Default admin: ID 1 (set is_admin=1 in database)
        </div>
        
        <?php if (isset($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" placeholder="Email or Phone" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
    </div>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</body>
</html>