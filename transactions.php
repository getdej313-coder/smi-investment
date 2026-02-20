<?php
require_once 'auth.php';

// Get all transactions with user details
$type_filter = $_GET['type'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

$query = "
    (SELECT 
        'recharge' as type,
        r.id,
        r.user_id,
        u.full_name,
        u.phone,
        r.amount,
        r.status,
        r.created_at,
        r.transaction_id,
        b.bank_name
    FROM recharges r
    JOIN users u ON r.user_id = u.id
    JOIN banks b ON r.bank_id = b.id)
    
    UNION ALL
    
    (SELECT 
        'withdrawal' as type,
        w.id,
        w.user_id,
        u.full_name,
        u.phone,
        w.amount,
        w.status,
        w.created_at,
        NULL as transaction_id,
        'Withdrawal' as bank_name
    FROM withdrawals w
    JOIN users u ON w.user_id = u.id)
";

$conditions = [];
if ($type_filter != 'all') {
    $conditions[] = "type = '$type_filter'";
}
if ($status_filter != 'all') {
    $conditions[] = "status = '$status_filter'";
}
if (!empty($search)) {
    $conditions[] = "(full_name LIKE '%$search%' OR phone LIKE '%$search%' OR id LIKE '%$search%')";
}

if (!empty($conditions)) {
    $query = "SELECT * FROM ($query) as all_tx WHERE " . implode(' AND ', $conditions);
}

$query .= " ORDER BY created_at DESC";
$transactions = $pdo->query($query)->fetchAll();

// Pagination
$page = $_GET['page'] ?? 1;
$per_page = 20;
$total = count($transactions);
$pages = ceil($total / $per_page);
$offset = ($page - 1) * $per_page;
$transactions = array_slice($transactions, $offset, $per_page);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transaction History - Admin</title>
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
        
        .filters {
            background:#1e2a3a;
            border-radius:30px;
            padding:20px;
            margin-bottom:20px;
            display:flex;
            gap:15px;
            flex-wrap:wrap;
            align-items:center;
        }
        .filter-group {
            display:flex;
            gap:10px;
            flex-wrap:wrap;
        }
        .filter-select {
            padding:10px 20px;
            background:#0f1a28;
            border:1px solid #2d3a4b;
            border-radius:30px;
            color:white;
        }
        .search-box {
            flex:1;
            min-width:250px;
            display:flex;
            gap:10px;
        }
        .search-box input {
            flex:1;
            padding:10px 20px;
            background:#0f1a28;
            border:1px solid #2d3a4b;
            border-radius:30px;
            color:white;
        }
        .search-btn {
            padding:10px 25px;
            background:#fbbf24;
            border:none;
            border-radius:30px;
            color:#0b1424;
            cursor:pointer;
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
        .type-recharge { color:#4ade80; }
        .type-withdrawal { color:#f87171; }
        
        .status {
            padding:5px 15px;
            border-radius:20px;
            font-size:0.9rem;
            display:inline-block;
        }
        .status-pending { background:#5b4a1a; color:#fbbf24; }
        .status-completed, .status-success { background:#163a30; color:#4ade80; }
        .status-failed { background:#2d1f1f; color:#f87171; }
        
        .btn-view {
            padding:5px 15px;
            background:#273649;
            color:white;
            text-decoration:none;
            border-radius:20px;
            font-size:0.9rem;
        }
        
        .pagination {
            display:flex;
            justify-content:center;
            gap:10px;
            margin-top:20px;
        }
        .page-link {
            padding:8px 15px;
            background:#1e2a3a;
            color:white;
            text-decoration:none;
            border-radius:10px;
        }
        .page-link.active {
            background:#fbbf24;
            color:#0b1424;
        }
        
        @media screen and (max-width: 768px) {
            table { display:block; overflow-x:auto; }
            .filters { flex-direction:column; }
            .search-box { width:100%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <a href="index.php"><i class="fas fa-arrow-left"></i></a>
                Transaction History
            </h1>
            <div>
                <span style="color:#a5b4cb;">Total: </span>
                <span style="color:#fbbf24; font-weight:700;"><?= $total ?></span>
            </div>
        </div>
        
        <form method="GET" class="filters">
            <div class="filter-group">
                <select name="type" class="filter-select">
                    <option value="all" <?= $type_filter == 'all' ? 'selected' : '' ?>>All Types</option>
                    <option value="recharge" <?= $type_filter == 'recharge' ? 'selected' : '' ?>>Recharges</option>
                    <option value="withdrawal" <?= $type_filter == 'withdrawal' ? 'selected' : '' ?>>Withdrawals</option>
                </select>
                
                <select name="status" class="filter-select">
                    <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="success" <?= $status_filter == 'success' ? 'selected' : '' ?>>Success</option>
                    <option value="failed" <?= $status_filter == 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
            </div>
            
            <div class="search-box">
                <input type="text" name="search" placeholder="Search by name, phone or ID..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
            </div>
        </form>
        
        <table>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>User</th>
                <th>Phone</th>
                <th>Bank</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
                <th>Transaction ID</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($transactions as $tx): ?>
            <tr>
                <td>#<?= $tx['id'] ?></td>
                <td>
                    <span class="type-<?= $tx['type'] ?>">
                        <i class="fas fa-<?= $tx['type'] == 'recharge' ? 'arrow-up' : 'arrow-down' ?>"></i>
                        <?= ucfirst($tx['type']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($tx['full_name']) ?></td>
                <td><?= htmlspecialchars($tx['phone']) ?></td>
                <td><?= htmlspecialchars($tx['bank_name']) ?></td>
                <td style="color:#fbbf24;">ETB <?= number_format($tx['amount'], 2) ?></td>
                <td>
                    <span class="status status-<?= $tx['status'] ?>"><?= ucfirst($tx['status']) ?></span>
                </td>
                <td><?= date('M d, H:i', strtotime($tx['created_at'])) ?></td>
                <td><?= $tx['transaction_id'] ?: '-' ?></td>
                <td>
                    <a href="transaction_detail.php?id=<?= $tx['id'] ?>&type=<?= $tx['type'] ?>" class="btn-view">
                        <i class="fas fa-eye"></i> View
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <?php if($pages > 1): ?>
        <div class="pagination">
            <?php for($i=1; $i<=$pages; $i++): ?>
            <a href="?page=<?= $i ?>&type=<?= $type_filter ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>" 
               class="page-link <?= $i==$page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>