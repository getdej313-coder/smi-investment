<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Investment Plans</title>
    <style>
        body { background:#0b1424; color:white; font-family:Inter; padding:20px; }
        .container { max-width:1200px; margin:auto; }
        h1 { color:#fbbf24; margin-bottom:30px; }
        table { width:100%; border-collapse:collapse; background:#1e2a3a; border-radius:20px; overflow:hidden; }
        th, td { padding:15px; text-align:left; border-bottom:1px solid #2d3a4b; }
        th { background:#0f1a28; color:#fbbf24; }
        .btn { padding:8px 15px; border-radius:10px; text-decoration:none; color:white; margin-right:5px; }
        .btn-add { background:#22c55e; }
        .btn-edit { background:#3b82f6; }
        .btn-delete { background:#ef4444; }
        .btn-toggle { background:#fbbf24; color:#000; }
        .add-form { background:#1e2a3a; padding:20px; border-radius:20px; margin-bottom:30px; }
        .form-group { margin-bottom:15px; }
        .form-group label { display:block; margin-bottom:5px; color:#a5b4cb; }
        .form-group input { width:100%; padding:10px; border-radius:10px; border:none; background:#0f1a28; color:white; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
        .submit-btn { background:#fbbf24; color:#000; border:none; padding:12px 30px; border-radius:10px; font-weight:bold; cursor:pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Investment Plans Management</h1>
        
        <!-- Add New Plan Form -->
        <div class="add-form">
            <h2 style="color:#fbbf24; margin-bottom:20px;">Add New Plan</h2>
            <form method="POST" action="save_plan.php">
                <div class="form-row">
                    <div class="form-group">
                        <label>Plan Name</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Level</label>
                        <input type="number" name="level" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Investment Amount (ETB)</label>
                        <input type="number" step="0.01" name="amount" required>
                    </div>
                    <div class="form-group">
                        <label>Daily Return (ETB)</label>
                        <input type="number" step="0.01" name="daily_return" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Period (Days)</label>
                        <input type="number" name="period_days" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description">
                    </div>
                </div>
                <button type="submit" class="submit-btn">Add Plan</button>
            </form>
        </div>
        
        <!-- Plans List -->
        <table>
            <tr>
                <th>ID</th><th>Level</th><th>Name</th><th>Amount</th><th>Daily</th><th>Period</th><th>Total</th><th>Status</th><th>Actions</th>
            </tr>
            <?php
            require_once __DIR__ . '/../config/database.php';
            $plans = $pdo->query("SELECT * FROM recharge_plans ORDER BY amount")->fetchAll();
            foreach ($plans as $plan):
            ?>
            <tr>
                <td><?= $plan['id'] ?></td>
                <td>Level <?= $plan['level'] ?></td>
                <td><?= htmlspecialchars($plan['name']) ?></td>
                <td>ETB <?= number_format($plan['amount'], 2) ?></td>
                <td>ETB <?= number_format($plan['daily_return'], 2) ?></td>
                <td><?= $plan['period_days'] ?> days</td>
                <td>ETB <?= number_format($plan['daily_return'] * $plan['period_days'], 2) ?></td>
                <td><?= $plan['is_active'] ? 'Active' : 'Inactive' ?></td>
                <td>
                    <a href="edit_plan.php?id=<?= $plan['id'] ?>" class="btn btn-edit">Edit</a>
                    <a href="toggle_plan.php?id=<?= $plan['id'] ?>" class="btn btn-toggle"><?= $plan['is_active'] ? 'Disable' : 'Enable' ?></a>
                    <a href="delete_plan.php?id=<?= $plan['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete this plan?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
