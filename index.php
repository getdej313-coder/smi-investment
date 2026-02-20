<?php
require_once 'auth.php';

// Get counts with error handling
try {
    $users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0;
    $pending_withdrawals = $pdo->query("SELECT COUNT(*) FROM withdrawals WHERE status='pending'")->fetchColumn() ?: 0;
    $pending_recharges = $pdo->query("SELECT COUNT(*) FROM recharges WHERE status='pending'")->fetchColumn() ?: 0;
    $total_recharges = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM recharges WHERE status='completed'")->fetchColumn() ?: 0;
    $total_withdrawals = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE status='success'")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $users = $pending_withdrawals = $pending_recharges = $total_recharges = $total_withdrawals = 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Smi Investment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; color:white; padding:20px; }
        .container { max-width:1400px; margin:auto; }
        
        .header { 
            display:flex; 
            justify-content:space-between; 
            align-items:center; 
            margin-bottom:40px; 
            flex-wrap:wrap; 
            gap:15px; 
            background:#1e2a3a;
            padding:20px 30px;
            border-radius:30px;
            border:1px solid #2d3a4b;
        }
        .header h1 { 
            color:#fbbf24;
            font-size:1.8rem;
        }
        .header h1 i {
            margin-right:10px;
        }
        .nav a { 
            color:#fbbf24; 
            margin-left:20px; 
            text-decoration:none;
            padding:8px 15px;
            border-radius:20px;
            transition:0.2s;
        }
        .nav a:hover {
            background:#273649;
        }
        .nav a i {
            margin-right:5px;
        }
        
        .stats-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(200px,1fr));
            gap:20px;
            margin-bottom:30px;
        }
        .stat-card {
            background:#1e2a3a;
            border-radius:24px;
            padding:25px;
            border:1px solid #2d3a4b;
            box-shadow:0 5px 0 #0f172a;
        }
        .stat-label {
            color:#a5b4cb;
            font-size:0.9rem;
            margin-bottom:10px;
        }
        .stat-value {
            color:#fbbf24;
            font-size:2rem;
            font-weight:700;
        }
        .stat-small {
            color:#4ade80;
            font-size:0.9rem;
            margin-top:5px;
        }
        
        .action-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(250px,1fr));
            gap:20px;
            margin-bottom:40px;
        }
        .action-card {
            background:#1e2a3a;
            border-radius:24px;
            padding:25px;
            text-align:center;
            border:1px solid #2d3a4b;
            transition:0.2s;
            text-decoration:none;
            display:block;
        }
        .action-card:hover {
            transform:translateY(-5px);
            border-color:#fbbf24;
            background:#273649;
        }
        .action-icon {
            width:70px;
            height:70px;
            background:#273649;
            border-radius:35px;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0 auto 15px;
        }
        .action-icon i {
            color:#fbbf24;
            font-size:2rem;
        }
        .action-title {
            color:white;
            font-size:1.2rem;
            font-weight:600;
            margin-bottom:5px;
        }
        .action-desc {
            color:#a5b4cb;
            font-size:0.9rem;
        }
        .badge {
            background:#fbbf24;
            color:#0b1424;
            padding:2px 8px;
            border-radius:20px;
            font-size:0.8rem;
            margin-left:5px;
        }
        
        .recent-section {
            background:#1e2a3a;
            border-radius:30px;
            padding:25px;
            margin-top:30px;
            border:1px solid #2d3a4b;
        }
        .section-header {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:20px;
        }
        .section-header h3 {
            color:white;
            font-size:1.3rem;
        }
        .view-all {
            color:#fbbf24;
            text-decoration:none;
        }
        
        @media screen and (max-width: 768px) {
            .header {
                flex-direction:column;
                text-align:center;
            }
            .nav a {
                display:inline-block;
                margin:5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Back Button -->
        <div class="header">
            <div style="display:flex; align-items:center; gap:20px;">
                <a href="../home.php" style="color:#fbbf24; font-size:1.2rem; text-decoration:none;">
                    <i class="fas fa-arrow-left"></i> Back to Site
                </a>
                <h1><i class="fas fa-chart-line"></i> Admin Dashboard</h1>
            </div>
            <div class="nav">
                <a href="users.php"><i class="fas fa-users"></i> Users</a>
                <a href="withdrawals.php"><i class="fas fa-history"></i> Withdrawals</a>
                <a href="recharges.php"><i class="fas fa-arrow-up"></i> Recharges</a>
                <a href="plans.php"><i class="fas fa-cubes"></i> Plans</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?= $users ?></div>
                <div class="stat-small">Active investors</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending Withdrawals</div>
                <div class="stat-value"><?= $pending_withdrawals ?></div>
                <div class="stat-small">Need approval</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending Recharges</div>
                <div class="stat-value"><?= $pending_recharges ?></div>
                <div class="stat-small">Awaiting confirmation</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Recharges</div>
                <div class="stat-value">ETB <?= number_format($total_recharges, 2) ?></div>
                <div class="stat-small">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Withdrawals</div>
                <div class="stat-value">ETB <?= number_format($total_withdrawals, 2) ?></div>
                <div class="stat-small">Processed</div>
            </div>
        </div>
        
        <!-- Quick Action Cards -->
        <div class="action-grid">
            <a href="recharges.php?status=pending" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="action-title">
                    Approve Recharges
                    <?php if($pending_recharges > 0): ?>
                        <span class="badge"><?= $pending_recharges ?></span>
                    <?php endif; ?>
                </div>
                <div class="action-desc">Verify and approve user recharges</div>
            </a>
            
            <a href="withdrawals.php?status=pending" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="action-title">
                    Approve Withdrawals
                    <?php if($pending_withdrawals > 0): ?>
                        <span class="badge"><?= $pending_withdrawals ?></span>
                    <?php endif; ?>
                </div>
                <div class="action-desc">Process withdrawal requests</div>
            </a>
            
            <a href="transactions.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="action-title">Transaction History</div>
                <div class="action-desc">View all user transactions</div>
            </a>
            
            <a href="users.php" class="action-card">
                <div class="action-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="action-title">Manage Users</div>
                <div class="action-desc">Add, edit or remove users</div>
            </a>
        </div>
        
        <!-- Recent Transactions -->
        <div class="recent-section">
            <div class="section-header">
                <h3><i class="fas fa-clock" style="color:#fbbf24; margin-right:10px;"></i> Recent Activity</h3>
                <a href="transactions.php" class="view-all">View All <i class="fas fa-chevron-right"></i></a>
            </div>
            
            <?php
            // Get recent transactions
            $recent = $pdo->query("
                (SELECT 'recharge' as type, id, user_id, amount, status, created_at FROM recharges)
                UNION ALL
                (SELECT 'withdrawal' as type, id, user_id, amount, status, created_at FROM withdrawals)
                ORDER BY created_at DESC LIMIT 10
            ")->fetchAll();
            
            if(empty($recent)): ?>
                <p style="color:#a5b4cb; text-align:center; padding:20px;">No recent transactions</p>
            <?php else: ?>
                <table style="width:100%; border-collapse:collapse;">
                    <tr style="color:#a5b4cb; border-bottom:1px solid #2d3a4b;">
                        <th style="padding:10px; text-align:left;">Type</th>
                        <th style="padding:10px; text-align:left;">User ID</th>
                        <th style="padding:10px; text-align:left;">Amount</th>
                        <th style="padding:10px; text-align:left;">Status</th>
                        <th style="padding:10px; text-align:left;">Date</th>
                    </tr>
                    <?php foreach($recent as $tx): ?>
                    <tr style="border-bottom:1px solid #2d3a4b;">
                        <td style="padding:10px;">
                            <?php if($tx['type'] == 'recharge'): ?>
                                <span style="color:#4ade80;"><i class="fas fa-arrow-up"></i> Recharge</span>
                            <?php else: ?>
                                <span style="color:#f87171;"><i class="fas fa-arrow-down"></i> Withdrawal</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:10px; color:white;">#<?= $tx['user_id'] ?></td>
                        <td style="padding:10px; color:#fbbf24;">ETB <?= number_format($tx['amount'], 2) ?></td>
                        <td style="padding:10px;">
                            <span style="padding:3px 10px; border-radius:15px; font-size:0.8rem; 
                                <?= $tx['status'] == 'completed' || $tx['status'] == 'success' ? 'background:#163a30; color:#4ade80;' : 
                                   ($tx['status'] == 'pending' ? 'background:#5b4a1a; color:#fbbf24;' : 
                                   'background:#2d1f1f; color:#f87171;') ?>">
                                <?= ucfirst($tx['status']) ?>
                            </span>
                        </td>
                        <td style="padding:10px; color:#a5b4cb;"><?= date('M d, H:i', strtotime($tx['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>