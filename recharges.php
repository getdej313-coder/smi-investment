<?php
require_once 'auth.php';

// Handle approval/rejection
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $id = $_GET['approve'];
    
    // Get recharge details
    $stmt = $pdo->prepare("SELECT * FROM recharges WHERE id = ?");
    $stmt->execute([$id]);
    $recharge = $stmt->fetch();
    
    if ($recharge && $recharge['status'] == 'pending') {
        $pdo->beginTransaction();
        try {
            // Update user balance
            $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$recharge['amount'], $recharge['user_id']]);
            
            // Update recharge status
            $pdo->prepare("UPDATE recharges SET status = 'completed', completed_at = NOW() WHERE id = ?")->execute([$id]);
            
            $pdo->commit();
            $success = "Recharge approved and user balance updated";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to approve recharge";
        }
    }
}

if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    $id = $_GET['reject'];
    $pdo->prepare("UPDATE recharges SET status = 'failed' WHERE id = ?")->execute([$id]);
    $success = "Recharge rejected";
}

// Filter by status
$status_filter = $_GET['status'] ?? 'all';
$query = "SELECT r.*, u.full_name, u.phone, u.email, b.bank_name 
          FROM recharges r 
          JOIN users u ON r.user_id = u.id 
          JOIN banks b ON r.bank_id = b.id";
if ($status_filter != 'all') {
    $query .= " WHERE r.status = '$status_filter'";
}
$query .= " ORDER BY r.created_at DESC";
$recharges = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Recharges - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; color:white; padding:20px; }
        .container { max-width:1400px; margin:auto; }
        
        .header {
            background:#1e2a3a;
            padding:20px 30px;
            border-radius:30px;
            margin-bottom:30px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            flex-wrap:wrap;
            gap:15px;
        }
        .header h1 {
            color:#fbbf24;
            display:flex;
            align-items:center;
            gap:15px;
        }
        .header h1 a {
            color:#fbbf24;
            font-size:1.2rem;
        }
        
        .filter-bar {
            display:flex;
            gap:10px;
            margin-bottom:20px;
            flex-wrap:wrap;
        }
        .filter-btn {
            padding:10px 20px;
            background:#1e2a3a;
            border:1px solid #2d3a4b;
            border-radius:30px;
            color:white;
            text-decoration:none;
            transition:0.2s;
        }
        .filter-btn.active {
            background:#fbbf24;
            color:#0b1424;
            border-color:#fbbf24;
        }
        .filter-btn:hover {
            background:#273649;
        }
        
        table {
            width:100%;
            border-collapse:collapse;
            background:#1e2a3a;
            border-radius:30px;
            overflow:hidden;
        }
        th, td {
            padding:15px;
            text-align:left;
            border-bottom:1px solid #2d3a4b;
        }
        th {
            background:#0f1a28;
            color:#fbbf24;
        }
        .status {
            padding:5px 15px;
            border-radius:20px;
            font-size:0.9rem;
            display:inline-block;
        }
        .status-pending { background:#5b4a1a; color:#fbbf24; }
        .status-completed { background:#163a30; color:#4ade80; }
        .status-failed { background:#2d1f1f; color:#f87171; }
        
        .btn {
            padding:8px 15px;
            border-radius:20px;
            text-decoration:none;
            margin:0 5px;
            font-size:0.9rem;
        }
        .btn-approve { background:#163a30; color:#4ade80; border:1px solid #2c7a5a; }
        .btn-reject { background:#2d1f1f; color:#f87171; border:1px solid #b45353; }
        .btn-view { background:#273649; color:white; }
        
        .success {
            background:#163a30;
            color:#4ade80;
            padding:15px;
            border-radius:20px;
            margin-bottom:20px;
        }
        
        @media screen and (max-width: 768px) {
            table { display:block; overflow-x:auto; }
            .header { flex-direction:column; text-align:center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <a href="index.php"><i class="fas fa-arrow-left"></i></a>
                Manage Recharges
            </h1>
            <div>
                <span style="color:#a5b4cb;">Total Pending: </span>
                <span style="color:#fbbf24; font-weight:700;">
                    <?= count(array_filter($recharges, fn($r) => $r['status'] == 'pending')) ?>
                </span>
            </div>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="success"><i class="fas fa-check-circle"></i> <?= $success ?></div>
        <?php endif; ?>
        
        <div class="filter-bar">
            <a href="?status=all" class="filter-btn <?= $status_filter == 'all' ? 'active' : '' ?>">All</a>
            <a href="?status=pending" class="filter-btn <?= $status_filter == 'pending' ? 'active' : '' ?>">Pending</a>
            <a href="?status=completed" class="filter-btn <?= $status_filter == 'completed' ? 'active' : '' ?>">Completed</a>
            <a href="?status=failed" class="filter-btn <?= $status_filter == 'failed' ? 'active' : '' ?>">Failed</a>
        </div>
        
        <table>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Phone</th>
                <th>Bank</th>
                <th>Amount</th>
                <th>Transaction ID</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($recharges as $r): ?>
            <tr>
                <td>#<?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['full_name']) ?></td>
                <td><?= htmlspecialchars($r['phone']) ?></td>
                <td><?= htmlspecialchars($r['bank_name']) ?></td>
                <td style="color:#fbbf24;">ETB <?= number_format($r['amount'], 2) ?></td>
                <td><?= $r['transaction_id'] ?: '-' ?></td>
                <td>
                    <span class="status status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
                </td>
                <td><?= date('M d, H:i', strtotime($r['created_at'])) ?></td>
                <td>
                    <?php if ($r['status'] == 'pending'): ?>
                        <a href="?approve=<?= $r['id'] ?>" class="btn btn-approve" onclick="return confirm('Approve this recharge?')">
                            <i class="fas fa-check"></i> Approve
                        </a>
                        <a href="?reject=<?= $r['id'] ?>" class="btn btn-reject" onclick="return confirm('Reject this recharge?')">
                            <i class="fas fa-times"></i> Reject
                        </a>
                    <?php endif; ?>
                    <a href="transaction_detail.php?id=<?= $r['id'] ?>&type=recharge" class="btn btn-view">
                        <i class="fas fa-eye"></i> View
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>