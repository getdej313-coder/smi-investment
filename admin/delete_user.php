<?php
require_once 'auth.php';

$id = $_GET['id'] ?? 0;

if (!$id) {
    header("Location: users.php?error=Invalid user ID");
    exit;
}

// Get user details for confirmation
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: users.php?error=User not found");
    exit;
}

// Get statistics about user's data
$stats = [
    'investments' => $pdo->prepare("SELECT COUNT(*) FROM user_investments WHERE user_id = ?"),
    'recharges' => $pdo->prepare("SELECT COUNT(*) FROM recharges WHERE user_id = ?"),
    'withdrawals' => $pdo->prepare("SELECT COUNT(*) FROM withdrawals WHERE user_id = ?"),
    'earnings' => $pdo->prepare("SELECT COUNT(*) FROM daily_earnings WHERE user_id = ?")
];

foreach ($stats as $key => $stmt) {
    $stmt->execute([$id]);
    $stats[$key] = $stmt->fetchColumn();
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm_delete'])) {
        // Start transaction
        $pdo->beginTransaction();
        try {
            // Delete all related data in correct order
            $pdo->prepare("DELETE FROM daily_earnings WHERE user_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM user_investments WHERE user_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM recharges WHERE user_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM withdrawals WHERE user_id = ?")->execute([$id]);
            
            // Finally delete the user
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
            
            $pdo->commit();
            
            // Log the action (optional)
            error_log("Admin " . $_SESSION['admin_id'] . " deleted user ID: " . $id);
            
            header("Location: users.php?success=User and all associated data deleted successfully");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to delete user: " . $e->getMessage();
            error_log("Delete user error: " . $e->getMessage());
        }
    } elseif (isset($_POST['cancel'])) {
        header("Location: users.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete User - Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        body {
            background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .delete-container {
            width: 100%;
            max-width: 550px;
            animation: slideUp 0.4s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .delete-card {
            background: #101b2b;
            border-radius: 40px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
            border: 1px solid #2d3a4b;
            position: relative;
            overflow: hidden;
        }
        
        .delete-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f87171, #ef4444, #dc2626);
        }
        
        .warning-icon {
            width: 90px;
            height: 90px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            border: 3px solid #ef4444;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(239, 68, 68, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }
        
        .warning-icon i {
            color: #ef4444;
            font-size: 3.5rem;
        }
        
        h2 {
            color: white;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .warning-subtitle {
            color: #94a3b8;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1rem;
        }
        
        .user-summary {
            background: #1e2a3a;
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #2d3a4b;
        }
        
        .user-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #2d3a4b;
        }
        
        .user-avatar {
            width: 60px;
            height: 60px;
            background: #273649;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .user-avatar i {
            color: #fbbf24;
            font-size: 2rem;
        }
        
        .user-title h3 {
            color: white;
            font-size: 1.3rem;
            margin-bottom: 5px;
        }
        
        .user-title p {
            color: #fbbf24;
            font-size: 0.9rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .info-item {
            background: #0f1a28;
            border-radius: 16px;
            padding: 15px;
        }
        
        .info-label {
            color: #94a3b8;
            font-size: 0.8rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .info-label i {
            color: #fbbf24;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .info-value.small {
            font-size: 1rem;
            color: #fbbf24;
        }
        
        .stats-section {
            background: #0f1a28;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .stats-title {
            color: #fbbf24;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .stat-box {
            background: #1e2a3a;
            border-radius: 14px;
            padding: 12px;
            text-align: center;
        }
        
        .stat-number {
            color: #fbbf24;
            font-size: 1.4rem;
            font-weight: 700;
            line-height: 1.2;
        }
        
        .stat-label {
            color: #94a3b8;
            font-size: 0.8rem;
        }
        
        .warning-box {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #ef4444;
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 25px;
        }
        
        .warning-box-title {
            color: #ef4444;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .warning-box-list {
            color: #fecaca;
            font-size: 0.95rem;
            list-style: none;
            padding-left: 25px;
        }
        
        .warning-box-list li {
            margin-bottom: 8px;
            position: relative;
        }
        
        .warning-box-list li:before {
            content: "•";
            color: #ef4444;
            font-weight: bold;
            position: absolute;
            left: -15px;
        }
        
        .error-message {
            background: #2d1f1f;
            color: #f87171;
            padding: 15px;
            border-radius: 20px;
            margin-bottom: 20px;
            border: 1px solid #b45353;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 16px;
            border-radius: 40px;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-delete {
            background: #dc2626;
            color: white;
            border: 1px solid #ef4444;
        }
        
        .btn-delete:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(220, 38, 38, 0.5);
        }
        
        .btn-cancel {
            background: #1e2a3a;
            color: white;
            border: 1px solid #2d3a4b;
        }
        
        .btn-cancel:hover {
            background: #273649;
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .note {
            text-align: center;
            color: #4b5563;
            font-size: 0.85rem;
            margin-top: 20px;
        }
        
        @media screen and (max-width: 500px) {
            .delete-card {
                padding: 25px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .user-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="delete-container">
        <div class="delete-card">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <h2>Delete User Account</h2>
            <p class="warning-subtitle">This action cannot be undone</p>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <!-- User Summary -->
            <div class="user-summary">
                <div class="user-header">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-title">
                        <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                        <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                    </div>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-id-card"></i> User ID</div>
                        <div class="info-value">#<?= $user['id'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-phone"></i> Phone</div>
                        <div class="info-value small"><?= htmlspecialchars($user['phone']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-wallet"></i> Balance</div>
                        <div class="info-value">ETB <?= number_format($user['balance'], 2) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-calendar"></i> Joined</div>
                        <div class="info-value small"><?= date('M d, Y', strtotime($user['created_at'])) ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Data Statistics -->
            <div class="stats-section">
                <div class="stats-title">
                    <i class="fas fa-database"></i>
                    Associated Data to be Deleted
                </div>
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-number"><?= $stats['investments'] ?></div>
                        <div class="stat-label">Active Investments</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?= $stats['recharges'] ?></div>
                        <div class="stat-label">Recharge Records</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?= $stats['withdrawals'] ?></div>
                        <div class="stat-label">Withdrawal Records</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?= $stats['earnings'] ?></div>
                        <div class="stat-label">Daily Earnings</div>
                    </div>
                </div>
            </div>
            
            <!-- Warning Box -->
            <div class="warning-box">
                <div class="warning-box-title">
                    <i class="fas fa-skull-crosswind"></i>
                    Permanent Deletion Warning
                </div>
                <ul class="warning-box-list">
                    <li>All user personal information will be permanently removed</li>
                    <li>Investment history and earnings will be lost</li>
                    <li>Recharge and withdrawal records will be deleted</li>
                    <li>This action cannot be reversed or recovered</li>
                    <li>User will lose access to their account permanently</li>
                </ul>
            </div>
            
            <!-- Action Buttons -->
            <form method="POST" id="deleteForm">
                <div class="action-buttons">
                    <button type="submit" name="confirm_delete" class="btn btn-delete" 
                            onclick="return confirm('FINAL WARNING: Are you absolutely sure you want to permanently delete this user? This action CANNOT be undone.')">
                        <i class="fas fa-trash-alt"></i>
                        Permanently Delete
                    </button>
                    <button type="submit" name="cancel" class="btn btn-cancel">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                </div>
            </form>
            
            <div class="note">
                <i class="fas fa-shield-alt"></i>
                This action is logged for security purposes
            </div>
        </div>
    </div>
    
    <!-- Optional: Add confirmation modal for extra safety -->
    <script>
    document.getElementById('deleteForm').addEventListener('submit', function(e) {
        if (e.submitter && e.submitter.name === 'confirm_delete') {
            if (!confirm('⚠️ FINAL WARNING: This will permanently delete all user data. Type "DELETE" to confirm.')) {
                e.preventDefault();
                return false;
            }
        }
    });
    </script>
</body>
</html>
