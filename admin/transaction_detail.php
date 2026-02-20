<?php
require_once 'auth.php';

$id = $_GET['id'] ?? 0;
$type = $_GET['type'] ?? 'recharge';

if ($type == 'recharge') {
    $stmt = $pdo->prepare("
        SELECT r.*, u.full_name, u.email, u.phone, u.balance, b.bank_name, b.account_name, b.account_number
        FROM recharges r
        JOIN users u ON r.user_id = u.id
        JOIN banks b ON r.bank_id = b.id
        WHERE r.id = ?
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT w.*, u.full_name, u.email, u.phone, u.balance
        FROM withdrawals w
        JOIN users u ON w.user_id = u.id
        WHERE w.id = ?
    ");
}
$stmt->execute([$id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    header("Location: transactions.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transaction Details - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; color:white; padding:20px; }
        .container { max-width:800px; margin:auto; }
        
        .back-link {
            margin-bottom:20px;
            display:inline-block;
            color:#fbbf24;
            text-decoration:none;
        }
        
        .detail-card {
            background:#1e2a3a;
            border-radius:30px;
            padding:30px;
            border:1px solid #2d3a4b;
        }
        
        .header {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:30px;
        }
        .header h1 {
            color:#fbbf24;
            font-size:1.8rem;
        }
        .status {
            padding:8px 20px;
            border-radius:30px;
            font-weight:600;
        }
        .status-pending { background:#5b4a1a; color:#fbbf24; }
        .status-completed, .status-success { background:#163a30; color:#4ade80; }
        .status-failed { background:#2d1f1f; color:#f87171; }
        
        .amount-box {
            background:#0f1a28;
            border-radius:30px;
            padding:30px;
            text-align:center;
            margin-bottom:30px;
        }
        .amount-label { color:#a5b4cb; }
        .amount-value {
            color:#fbbf24;
            font-size:3rem;
            font-weight:700;
            margin:10px 0;
        }
        
        .info-grid {
            display:grid;
            grid-template-columns:repeat(2,1fr);
            gap:20px;
            margin-bottom:30px;
        }
        .info-item {
            background:#0f1a28;
            padding:20px;
            border-radius:20px;
        }
        .info-label {
            color:#a5b4cb;
            font-size:0.9rem;
            margin-bottom:5px;
        }
        .info-value {
            color:white;
            font-size:1.1rem;
            font-weight:500;
        }
        
        .user-card {
            background:#0f1a28;
            border-radius:20px;
            padding:20px;
            display:flex;
            align-items:center;
            gap:15px;
            margin-bottom:30px;
        }
        .user-icon {
            width:50px;
            height:50px;
            background:#273649;
            border-radius:25px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fbbf24;
        }
        
        .actions {
            display:flex;
            gap:15px;
        }
        .btn {
            flex:1;
            padding:15px;
            border-radius:30px;
            text-align:center;
            text-decoration:none;
            font-weight:600;
            border:none;
            cursor:pointer;
        }
        .btn-approve { background:#163a30; color:#4ade80; border:1px solid #2c7a5a; }
        .btn-reject { background:#2d1f1f; color:#f87171; border:1px solid #b45353; }
        .btn-back { background:#273649; color:white; }
        
        @media screen and (max-width: 600px) {
            .info-grid { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="transactions.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Transactions
        </a>
        
        <div class="detail-card">
            <div class="header">
                <h1>
                    <?= ucfirst($type) ?> Details
                    <span style="font-size:1rem; color:#a5b4cb; margin-left:10px;">#<?= $transaction['id'] ?></span>
                </h1>
                <span class="status status-<?= $transaction['status'] ?>">
                    <?= ucfirst($transaction['status']) ?>
                </span>
            </div>
            
            <div class="amount-box">
                <div class="amount-label">Total Amount</div>
                <div class="amount-value">ETB <?= number_format($transaction['amount'], 2) ?></div>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Date & Time</div>
                    <div class="info-value"><?= date('F d, Y H:i:s', strtotime($transaction['created_at'])) ?></div>
                </div>
                <?php if($type == 'recharge'): ?>
                <div class="info-item">
                    <div class="info-label">Bank Name</div>
                    <div class="info-value"><?= htmlspecialchars($transaction['bank_name']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Account Name</div>
                    <div class="info-value"><?= htmlspecialchars($transaction['account_name']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Account Number</div>
                    <div class="info-value"><?= htmlspecialchars($transaction['account_number']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Transaction ID</div>
                    <div class="info-value"><?= $transaction['transaction_id'] ?: 'Not provided' ?></div>
                </div>
                <?php else: ?>
                <div class="info-item">
                    <div class="info-label">Phone (Masked)</div>
                    <div class="info-value"><?= htmlspecialchars($transaction['phone_masked'] ?? 'N/A') ?></div>
                </div>
                <?php endif; ?>
                <?php if($transaction['completed_at']): ?>
                <div class="info-item">
                    <div class="info-label">Completed At</div>
                    <div class="info-value"><?= date('F d, Y H:i:s', strtotime($transaction['completed_at'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="user-card">
                <div class="user-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <div style="color:#fbbf24; font-weight:600;"><?= htmlspecialchars($transaction['full_name']) ?></div>
                    <div style="color:#a5b4cb;"><?= htmlspecialchars($transaction['email']) ?> | <?= htmlspecialchars($transaction['phone']) ?></div>
                    <div style="color:#fbbf24; margin-top:5px;">Balance: ETB <?= number_format($transaction['balance'], 2) ?></div>
                </div>
            </div>
            
            <?php if($transaction['status'] == 'pending'): ?>
            <div class="actions">
                <a href="<?= $type ?>s.php?approve=<?= $transaction['id'] ?>" class="btn btn-approve" onclick="return confirm('Approve this <?= $type ?>?')">
                    <i class="fas fa-check"></i> Approve
                </a>
                <a href="<?= $type ?>s.php?reject=<?= $transaction['id'] ?>" class="btn btn-reject" onclick="return confirm('Reject this <?= $type ?>?')">
                    <i class="fas fa-times"></i> Reject
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
