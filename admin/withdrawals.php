<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Withdrawals</title>
    <style>
        body { background:#0b1424; color:white; font-family:Inter; padding:20px; }
        .container { max-width:1200px; margin:auto; }
        table { width:100%; border-collapse:collapse; background:#1e2a3a; border-radius:20px; overflow:hidden; }
        th, td { padding:15px; text-align:left; border-bottom:1px solid #2d3a4b; }
        th { background:#0f1a28; color:#fbbf24; }
        .btn { padding:8px 15px; border-radius:10px; text-decoration:none; color:white; margin-right:5px; }
        .btn-approve { background:#22c55e; }
        .btn-reject { background:#ef4444; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Withdrawal Requests</h1>
        <table>
            <tr>
                <th>ID</th><th>User</th><th>Phone</th><th>Amount</th><th>Status</th><th>Date</th><th>Actions</th>
            </tr>
            <?php
            require_once __DIR__ . '/../config/database.php';
            $withdrawals = $pdo->query("SELECT w.*, u.full_name, u.phone FROM withdrawals w JOIN users u ON w.user_id = u.id ORDER BY w.created_at DESC")->fetchAll();
            foreach ($withdrawals as $w):
            ?>
            <tr>
                <td><?= $w['id'] ?></td>
                <td><?= htmlspecialchars($w['full_name']) ?></td>
                <td><?= htmlspecialchars($w['phone']) ?></td>
                <td>ETB <?= number_format($w['amount'], 2) ?></td>
                <td><?= $w['status'] ?></td>
                <td><?= $w['created_at'] ?></td>
                <td>
                    <?php if ($w['status'] == 'pending'): ?>
                    <a href="approve_withdrawal.php?id=<?= $w['id'] ?>" class="btn btn-approve">Approve</a>
                    <a href="reject_withdrawal.php?id=<?= $w['id'] ?>" class="btn btn-reject">Reject</a>
                    <?php else: ?>
                    -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
