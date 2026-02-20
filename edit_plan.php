<?php
require_once 'auth.php';

$id = $_GET['id'] ?? 0;

// Get plan details
$stmt = $pdo->prepare("SELECT * FROM recharge_plans WHERE id = ?");
$stmt->execute([$id]);
$plan = $stmt->fetch();

if (!$plan) {
    header("Location: plans.php");
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE recharge_plans SET name=?, level=?, amount=?, daily_return=?, period_days=?, description=? WHERE id=?");
    $stmt->execute([
        $_POST['name'],
        $_POST['level'],
        $_POST['amount'],
        $_POST['daily_return'],
        $_POST['period_days'],
        $_POST['description'],
        $id
    ]);
    header("Location: plans.php?success=Plan updated successfully");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Plan - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; color:white; padding:20px; }
        .container { max-width:600px; margin:auto; }
        
        .edit-card {
            background:#1e2a3a;
            border-radius:30px;
            padding:40px;
        }
        
        h1 {
            color:#fbbf24;
            margin-bottom:30px;
        }
        
        .form-group {
            margin-bottom:20px;
        }
        
        .form-group label {
            display:block;
            color:#a5b4cb;
            margin-bottom:5px;
        }
        
        .form-group input {
            width:100%;
            padding:12px;
            background:#0f1a28;
            border:1px solid #2d3a4b;
            border-radius:15px;
            color:white;
        }
        
        .btn-save {
            background:#fbbf24;
            color:#0b1424;
            border:none;
            padding:15px 30px;
            border-radius:30px;
            cursor:pointer;
            width:100%;
            font-weight:600;
        }
        
        .btn-cancel {
            display:block;
            text-align:center;
            margin-top:15px;
            color:#a5b4cb;
            text-decoration:none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="edit-card">
            <h1><a href="plans.php" style="color:#fbbf24; margin-right:15px;"><i class="fas fa-arrow-left"></i></a> Edit Plan</h1>
            
            <form method="POST">
                <div class="form-group">
                    <label>Plan Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($plan['name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Level</label>
                    <input type="number" name="level" value="<?= $plan['level'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Amount (ETB)</label>
                    <input type="number" step="0.01" name="amount" value="<?= $plan['amount'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Daily Return (ETB)</label>
                    <input type="number" step="0.01" name="daily_return" value="<?= $plan['daily_return'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Period (Days)</label>
                    <input type="number" name="period_days" value="<?= $plan['period_days'] ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" value="<?= htmlspecialchars($plan['description']) ?>">
                </div>
                
                <button type="submit" class="btn-save">Update Plan</button>
                <a href="plans.php" class="btn-cancel">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>