<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <style>
        body { background:#0b1424; color:white; font-family:Inter; padding:20px; }
        .container { max-width:1200px; margin:auto; }
        table { width:100%; border-collapse:collapse; background:#1e2a3a; border-radius:20px; overflow:hidden; }
        th, td { padding:15px; text-align:left; border-bottom:1px solid #2d3a4b; }
        th { background:#0f1a28; color:#fbbf24; }
        .btn { padding:8px 15px; border-radius:10px; text-decoration:none; color:white; margin-right:5px; }
        .btn-edit { background:#3b82f6; }
        .btn-delete { background:#ef4444; }
        .btn-admin { background:#fbbf24; color:#000; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Users</h1>
        <table>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Balance</th><th>Admin</th><th>Actions</th>
            </tr>
            <?php
            require_once __DIR__ . '/../config/database.php';
            $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
            foreach ($users as $user):
            ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['full_name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['phone']) ?></td>
                <td>ETB <?= number_format($user['balance'], 2) ?></td>
                <td><?= $user['is_admin'] ? 'Yes' : 'No' ?></td>
                <td>
                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-edit">Edit</a>
                    <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-delete" onclick="return confirm('Delete?')">Delete</a>
                    <?php if (!$user['is_admin']): ?>
                    <a href="make_admin.php?id=<?= $user['id'] ?>" class="btn btn-admin">Make Admin</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
