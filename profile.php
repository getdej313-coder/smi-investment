<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get user's investment statistics
$stats = $pdo->prepare("SELECT 
    COUNT(*) as total_investments,
    SUM(amount) as total_invested,
    SUM(total_earned) as total_earned_from_plans,
    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_investments
    FROM user_investments WHERE user_id = ?");
$stats->execute([$user_id]);
$investment_stats = $stats->fetch();

// Get team members count (placeholder - you can implement actual referral system later)
$team_members = [
    'B' => 0,
    'C' => 0,
    'D' => 0,
    'total' => 0
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Smi Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; box-shadow:0 25px 50px -12px rgba(0,0,0,0.8); }
        
        /* Header */
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.8rem; }
        .settings-icon { color:#fbbf24; background:#1e2a3a; padding:10px; border-radius:50%; cursor:pointer; }
        
        /* User Info Card */
        .user-info-card { background:#1e2a3a; border-radius:28px; padding:18px 22px; margin-bottom:24px; border:1px solid #2d3a4b; box-shadow:0 6px 0 #0f172a; display:flex; justify-content:space-between; align-items:center; }
        .user-id .id-label { color:#9aaec5; font-size:0.8rem; }
        .user-id .id-value { color:white; font-size:1.6rem; font-weight:700; }
        .user-phone { background:#273649; padding:10px 18px; border-radius:40px; color:#fbbf24; font-size:1.2rem; font-weight:600; border:1px solid #3e5068; }
        
        /* Balance Grid */
        .balance-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:28px; }
        .balance-item { background:#1e2a3a; border-radius:22px; padding:18px 10px; text-align:center; border:1px solid #2d3a4b; box-shadow:0 5px 0 #0f172a; }
        .balance-item .amount { font-size:1.4rem; font-weight:700; color:#fbbf24; }
        .balance-item .label { font-size:0.8rem; color:#a5b4cb; margin-top:6px; }
        
        /* Team Stats */
        .team-stats { background:#1e2a3a; border-radius:24px; padding:18px; margin-bottom:28px; border:1px solid #2d3a4b; box-shadow:0 6px 0 #0f172a; }
        .team-title { color:#fbbf24; font-size:1rem; font-weight:600; margin-bottom:12px; display:flex; align-items:center; gap:8px; }
        .team-counters { display:flex; gap:15px; flex-wrap:wrap; color:white; }
        .team-counters span { background:#273649; padding:6px 18px; border-radius:40px; font-size:1rem; font-weight:600; }
        .team-counters span i { color:#fbbf24; margin-right:6px; }
        .team-total { margin-top:12px; padding-top:12px; border-top:1px solid #2d3a4b; color:#fbbf24; font-weight:700; text-align:center; }
        
        /* Options List */
        .options-list { list-style:none; margin-bottom:28px; }
        .options-list li { display:flex; align-items:center; gap:16px; background:#1e2a3a; border-radius:20px; padding:16px 20px; margin-bottom:10px; border:1px solid #2d3a4b; box-shadow:0 4px 0 #0f172a; transition:0.2s; cursor:pointer; }
        .options-list li:hover { transform:translateY(-2px); background:#273649; }
        .options-list li a { display:flex; align-items:center; gap:16px; color:#e0e7ff; text-decoration:none; width:100%; font-weight:500; }
        .options-list li i { width:24px; color:#fbbf24; font-size:1.2rem; text-align:center; }
        
        /* Checkbox styling */
        .option-checkbox { width:20px; height:20px; border:2px solid #fbbf24; border-radius:5px; margin-right:10px; display:inline-flex; align-items:center; justify-content:center; color:#fbbf24; }
        .option-checkbox.checked { background:#fbbf24; color:#0b1424; }
        .option-checkbox i { font-size:0.8rem; color:inherit; }
        
        /* Logout Button */
        .logout-btn { display:block; width:100%; background:#2d1f1f; border:1px solid #b45353; border-radius:40px; padding:16px; color:#fecaca; font-size:1.2rem; font-weight:600; text-align:center; margin-bottom:24px; box-shadow:0 6px 0 #631c1c; transition:0.1s; cursor:pointer; text-decoration:none; }
        .logout-btn:hover { transform:translateY(-2px); background:#3d2f2f; }
        .logout-btn i { margin-right:10px; color:#f87171; }
        
        /* Bottom Navigation */
        .bottom-nav { position:absolute; bottom:0; left:0; right:0; background:#0f1a28; display:flex; justify-content:space-around; padding:12px 16px 20px; border-top:1px solid #263340; border-radius:30px 30px 0 0; }
        .nav-item { display:flex; flex-direction:column; align-items:center; color:#6b7e99; font-size:0.7rem; text-decoration:none; transition:0.2s; flex:1; }
        .nav-item i { font-size:1.4rem; margin-bottom:4px; }
        .nav-item.active { color:#fbbf24; }
        .nav-item:hover { color:#fbbf24; transform:translateY(-2px); }
        
        /* Note */
        .note { color:#3c5068; font-size:0.7rem; text-align:center; margin-top:10px; }

        /* Responsive Breakpoints */
        @media screen and (min-width: 600px) and (max-width: 1024px) {
            body { padding:30px; background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%); }
            .phone-frame { max-width: 700px; border-radius: 40px; padding: 30px 30px 90px; }
            .balance-grid { grid-template-columns: repeat(3, 1fr); gap: 15px; }
            .bottom-nav { padding: 15px 30px 25px; }
            .nav-item span { font-size: 0.8rem; }
            .nav-item i { font-size: 1.6rem; }
        }

        @media screen and (min-width: 1025px) and (max-width: 1440px) {
            body { padding: 40px; background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%); }
            .phone-frame { max-width: 900px; border-radius: 50px; padding: 40px 40px 100px; }
            .balance-grid { grid-template-columns: repeat(4, 1fr); gap: 20px; }
            .bottom-nav { padding: 15px 40px 25px; max-width: 900px; left: 50%; transform: translateX(-50%); }
            .team-counters { justify-content: center; }
        }

        @media screen and (min-width: 1441px) {
            body { padding: 50px; background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%); }
            .phone-frame { max-width: 1200px; border-radius: 60px; padding: 50px 50px 120px; }
            .balance-grid { grid-template-columns: repeat(6, 1fr); gap: 25px; }
            .balance-item .amount { font-size: 1.8rem; }
            .balance-item .label { font-size: 1rem; }
            .bottom-nav { max-width: 1200px; padding: 20px 50px 30px; left: 50%; transform: translateX(-50%); }
            .nav-item span { font-size: 0.9rem; }
            .nav-item i { font-size: 1.8rem; }
        }

        @media screen and (max-width: 399px) {
            .phone-frame { padding: 20px 15px 80px; }
            .user-info-card { flex-direction: column; gap: 10px; text-align: center; }
            .bottom-nav { padding: 10px 10px 15px; }
            .nav-item i { font-size: 1.2rem; }
            .nav-item span { font-size: 0.6rem; }
        }

        @media screen and (orientation: landscape) and (max-height: 600px) {
            body { padding: 20px; }
            .phone-frame { max-width: 700px; padding: 20px 20px 70px; }
            .bottom-nav { padding: 8px 20px 15px; }
        }
    </style>
</head>
<body>
    <div class="phone-frame">
        <!-- Header -->
        <div class="page-header">
            <h2>Mine</h2>
            <div class="settings-icon">
                <i class="fas fa-cog"></i>
            </div>
        </div>

        <!-- User Info: ID and Phone -->
        <div class="user-info-card">
            <div class="user-id">
                <span class="id-label">ID</span>
                <span class="id-value"><?= $user['id'] ?></span>
            </div>
            <div class="user-phone">
                <i class="fas fa-phone-alt" style="margin-right:6px;"></i> <?= htmlspecialchars($user['phone']) ?>
            </div>
        </div>

        <!-- Six Balance Cards -->
        <div class="balance-grid">
            <div class="balance-item">
                <div class="amount">ETB <?= number_format($user['balance'] ?? 0, 2) ?></div>
                <div class="label">Today's Product Income</div>
            </div>
            <div class="balance-item">
                <div class="amount">ETB 0.00</div>
                <div class="label">Withdraw Balance</div>
            </div>
            <div class="balance-item">
                <div class="amount">ETB <?= number_format($user['total_earned'] ?? 0, 2) ?></div>
                <div class="label">Team Income</div>
            </div>
            <div class="balance-item">
                <div class="amount">ETB <?= number_format($user['balance'] ?? 0, 2) ?></div>
                <div class="label">Recharge Balance</div>
            </div>
            <div class="balance-item">
                <div class="amount">ETB 0.00</div>
                <div class="label">Total Withdraw</div>
            </div>
            <div class="balance-item">
                <div class="amount">ETB <?= number_format($user['total_earned'] ?? 0, 2) ?></div>
                <div class="label">Total Income</div>
            </div>
        </div>

        <!-- My Valid Team Member -->
        <div class="team-stats">
            <div class="team-title">
                <i class="fas fa-users"></i> My Valid Team Member
            </div>
            <div class="team-counters">
                <span><i class="fas fa-layer-group"></i> B: <?= $team_members['B'] ?></span>
                <span><i class="fas fa-copy"></i> C: <?= $team_members['C'] ?></span>
                <span><i class="fas fa-chart-pie"></i> D: <?= $team_members['D'] ?></span>
            </div>
            <div class="team-total">
                Total: <?= $team_members['total'] ?> Members
            </div>
        </div>

        <!-- Options List -->
        <ul class="options-list">
            <li>
                <a href="withdraw_info.php">
                    <span class="option-checkbox"><i class="fas fa-square"></i></span>
                    <i class="fas fa-info-circle"></i> Withdraw Information
                </a>
            </li>
            <li>
                <a href="partner_agreement.php">
                    <span class="option-checkbox checked"><i class="fas fa-check-square"></i></span>
                    <i class="fas fa-file-signature"></i> Partner Agreement
                </a>
            </li>
            <li>
                <a href="my_income.php">
                    <span class="option-checkbox checked"><i class="fas fa-check-square"></i></span>
                    <i class="fas fa-box-open"></i> MY Product Income
                </a>
            </li>
            <li>
                <a href="my_products.php">
                    <span class="option-checkbox checked"><i class="fas fa-check-square"></i></span>
                    <i class="fas fa-cubes"></i> My Product
                </a>
            </li>
            <li>
                <a href="record.php">
                    <span class="option-checkbox"><i class="fas fa-square"></i></span>
                    <i class="fas fa-history"></i> Record
                </a>
            </li>
            <li>
                <a href="about_us.php">
                    <span class="option-checkbox"><i class="fas fa-square"></i></span>
                    <i class="fas fa-building"></i> About US
                </a>
            </li>
            <li>
                <a href="reset_password.php">
                    <span class="option-checkbox checked"><i class="fas fa-check-square"></i></span>
                    <i class="fas fa-key"></i> Reset Password
                </a>
            </li>
            <li>
                <a href="telegram_service.php">
                    <span class="option-checkbox checked"><i class="fas fa-check-square"></i></span>
                    <i class="fab fa-telegram-plane"></i> Telegram Service
                </a>
            </li>
        </ul>

        <!-- Logout Button -->
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Log out
        </a>

        <!-- Bottom Navigation (Mine Active) -->
        <div class="bottom-nav">
            <a href="home.php" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="product.php" class="nav-item"><i class="fas fa-cube"></i><span>Product</span></a>
            <a href="official.php" class="nav-item"><i class="fas fa-bullhorn"></i><span>Official</span></a>
            <a href="team.php" class="nav-item"><i class="fas fa-users"></i><span>Team</span></a>
            <a href="profile.php" class="nav-item active"><i class="fas fa-user"></i><span>Mine</span></a>
        </div>       
    </div>
</body>

</html>
