<?php
require_once 'includes/auth.php';
$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Generate unique referral code if not exists
if (empty($user['referral_code'])) {
    $referral_code = 'SM' . strtoupper(substr(md5($user_id . time()), 0, 6));
    $update = $pdo->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
    $update->execute([$referral_code, $user_id]);
    $user['referral_code'] = $referral_code;
}

$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/smi_investment";
$invite_link = $base_url . "/register.php?ref=" . $user['referral_code'];

// Get referral statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as total, 
                       SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as last_30_days,
                       SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active
                       FROM users WHERE referred_by = ?");
$stmt->execute([$user_id]);
$referral_stats = $stmt->fetch();

// Get recent referrals
$recent = $pdo->prepare("SELECT full_name, created_at, is_active FROM users WHERE referred_by = ? ORDER BY created_at DESC LIMIT 5");
$recent->execute([$user_id]);
$recent_referrals = $recent->fetchAll();

// Calculate total referral earnings
$total_earnings = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM referral_earnings WHERE user_id = ?");
$total_earnings->execute([$user_id]);
$referral_earnings = $total_earnings->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Team - Smi Investment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* (all existing styles remain the same) */
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter', sans-serif; }
        body { background:#0b1424; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; box-shadow:0 25px 50px -12px rgba(0,0,0,0.8); margin:0 auto; transition: all 0.3s ease; }
        .page-header { margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.8rem; margin-bottom:5px; }
        .page-header p { color:#a5b4cb; font-size:0.9rem; }
        
        /* Invitation Card */
        .invite-card { background:#1e2a3a; border-radius:24px; padding:20px; margin-bottom:25px; border:1px solid #2d3a4b; box-shadow:0 5px 0 #0f172a; }
        .invite-title { color:#fbbf24; font-weight:600; margin-bottom:15px; display:flex; align-items:center; gap:8px; }
        .invite-code-box { background:#0f1a28; border-radius:16px; padding:15px; margin-bottom:15px; text-align:center; }
        .invite-code-label { color:#a5b4cb; font-size:0.8rem; margin-bottom:5px; }
        .invite-code { color:#fbbf24; font-size:1.8rem; font-weight:700; letter-spacing:2px; margin-bottom:10px; word-break: break-word; }
        .invite-link { background:#0f1a28; border-radius:40px; padding:12px 15px; margin-bottom:15px; display:flex; align-items:center; justify-content:space-between; border:1px solid #2d3a4b; flex-wrap: wrap; gap:10px; }
        .invite-link-text { color:#a5b4cb; font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; min-width: 150px; }
        .copy-btn { background:#273649; color:#fbbf24; border:none; padding:8px 20px; border-radius:30px; font-weight:600; cursor:pointer; transition:0.2s; display:flex; align-items:center; gap:5px; white-space: nowrap; }
        .copy-btn:hover { background:#3d4a5f; transform:scale(1.02); }
        .copy-btn.copied { background:#163a30; color:#4ade80; }
        .share-buttons { display:flex; gap:10px; margin-top:15px; }
        .share-btn { flex:1; padding:12px; border-radius:30px; border:none; font-weight:600; cursor:pointer; transition:0.2s; display:flex; align-items:center; justify-content:center; gap:8px; }
        .share-wa { background:#25D366; color:white; }
        .share-tg { background:#0088cc; color:white; }
        .share-btn:hover { transform:translateY(-2px); opacity:0.9; }
        
        /* Stats Cards */
        .stats-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:25px; }
        .stat-card { background:#1e2a3a; border-radius:18px; padding:15px; text-align:center; border:1px solid #2d3a4b; }
        .stat-value { color:#fbbf24; font-size:1.4rem; font-weight:700; }
        .stat-label { color:#a5b4cb; font-size:0.7rem; margin-top:5px; }
        
        /* Earnings Card */
        .earnings-card { background:linear-gradient(135deg,#1e4b5e,#12303e); border-radius:20px; padding:18px; margin-bottom:25px; display:flex; justify-content:space-between; align-items:center; border:1px solid #fbbf24; flex-wrap: wrap; gap:10px; }
        .earnings-label { color:#a5b4cb; font-size:0.9rem; }
        .earnings-value { color:#fbbf24; font-size:1.6rem; font-weight:700; }
        
        /* Referrals List */
        .referrals-section { margin-bottom:20px; }
        .section-title { color:white; font-size:1.2rem; margin-bottom:15px; display:flex; align-items:center; gap:8px; }
        .referral-item { background:#1e2a3a; border-radius:18px; padding:15px; margin-bottom:10px; display:flex; align-items:center; justify-content:space-between; border:1px solid #2d3a4b; flex-wrap: wrap; gap:10px; }
        .referral-info { display:flex; align-items:center; gap:12px; }
        .referral-avatar { width:40px; height:40px; background:#273649; border-radius:20px; display:flex; align-items:center; justify-content:center; color:#fbbf24; }
        .referral-details h4 { color:white; font-size:1rem; }
        .referral-details p { color:#a5b4cb; font-size:0.8rem; }
        .referral-badge { padding:4px 12px; border-radius:20px; font-size:0.7rem; font-weight:600; }
        .badge-active { background:#163a30; color:#4ade80; }
        .badge-pending { background:#5b4a1a; color:#fbbf24; }
        .empty-state { text-align:center; color:#a5b4cb; padding:30px 0; }
        .empty-state i { font-size:3rem; color:#2d3a4b; margin-bottom:15px; }
        
        /* Telegram Channel Card (New) */
        .telegram-channel-card {
            background: linear-gradient(135deg,#1e4b5e,#12303e);
            border-radius: 24px;
            padding: 18px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #0088cc;
            box-shadow: 0 5px 0 #0f172a;
        }
        .telegram-channel-info {
            display: flex;
            flex-direction: column;
        }
        .telegram-channel-label {
            color: #a5b4cb;
            font-size: 0.8rem;
            margin-bottom: 5px;
        }
        .telegram-channel-name {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .telegram-channel-name i {
            color: #0088cc;
        }
        .telegram-join-btn {
            background: #0088cc;
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.2s;
            white-space: nowrap;
        }
        .telegram-join-btn:hover {
            background: #006699;
            transform: translateY(-2px);
        }
        
        /* Bottom Navigation */
        .bottom-nav { position:absolute; bottom:0; left:0; right:0; background:#0f1a28; display:flex; justify-content:space-around; padding:12px 16px 20px; border-top:1px solid #263340; border-radius:30px 30px 0 0; }
        .nav-item { display:flex; flex-direction:column; align-items:center; color:#6b7e99; font-size:0.7rem; text-decoration:none; transition:0.2s; flex:1; }
        .nav-item i { font-size:1.4rem; margin-bottom:4px; }
        .nav-item.active { color:#fbbf24; }
        .nav-item:hover { color:#fbbf24; transform:translateY(-2px); }
        
        /* Responsive (all existing media queries) */
        @media screen and (min-width: 600px) and (max-width: 1024px) {
            .phone-frame { max-width:700px; border-radius:40px; padding:30px 30px 90px; }
            .stats-grid { gap:15px; }
            .stat-value { font-size:1.6rem; }
            .stat-label { font-size:0.8rem; }
            .invite-code { font-size:2rem; }
            .copy-btn { padding:10px 25px; font-size:0.9rem; }
            .share-btn { font-size:1rem; }
            .bottom-nav { padding:15px 30px 25px; }
            .nav-item span { font-size:0.8rem; }
            .nav-item i { font-size:1.6rem; }
        }
        @media screen and (min-width: 1025px) and (max-width: 1440px) {
            body { padding:40px; }
            .phone-frame { max-width:900px; border-radius:50px; padding:40px 40px 100px; }
            .invite-card { padding:30px; }
            .invite-code { font-size:2.2rem; }
            .stats-grid { gap:20px; }
            .stat-card { padding:20px; }
            .stat-value { font-size:2rem; }
            .stat-label { font-size:0.9rem; }
            .earnings-card { padding:25px; }
            .earnings-value { font-size:2rem; }
            .bottom-nav { padding:15px 40px 25px; max-width:900px; left:50%; transform:translateX(-50%); }
        }
        @media screen and (min-width: 1441px) {
            body { padding:50px; }
            .phone-frame { max-width:1200px; border-radius:60px; padding:50px 50px 120px; }
            .invite-card { padding:35px; }
            .invite-code { font-size:2.5rem; }
            .stats-grid { gap:25px; }
            .stat-card { padding:25px; }
            .stat-value { font-size:2.2rem; }
            .stat-label { font-size:1rem; }
            .earnings-card { padding:30px; }
            .earnings-value { font-size:2.5rem; }
            .bottom-nav { max-width:1200px; padding:20px 50px 30px; left:50%; transform:translateX(-50%); }
            .nav-item span { font-size:0.9rem; }
            .nav-item i { font-size:1.8rem; }
        }
        @media screen and (max-width: 399px) {
            .phone-frame { padding:20px 15px 80px; }
            .invite-code { font-size:1.5rem; }
            .invite-link { flex-direction:column; align-items:stretch; }
            .invite-link-text { white-space:normal; text-align:center; margin-bottom:5px; }
            .copy-btn { justify-content:center; width:100%; }
            .share-buttons { flex-direction:column; }
            .stats-grid { grid-template-columns:1fr; }
            .earnings-card { flex-direction:column; text-align:center; }
            .referral-item { flex-direction:column; text-align:center; }
            .referral-info { flex-direction:column; }
            .telegram-channel-card { flex-direction:column; text-align:center; gap:10px; }
            .bottom-nav { padding:10px 10px 15px; }
            .nav-item i { font-size:1.2rem; }
            .nav-item span { font-size:0.6rem; }
        }
        @media screen and (orientation: landscape) and (max-height: 600px) {
            body { padding:20px; align-items:flex-start; }
            .phone-frame { max-width:700px; padding:20px 20px 70px; }
            .invite-card { padding:15px; }
            .stats-grid { grid-template-columns:repeat(3,1fr); }
            .bottom-nav { padding:8px 20px 15px; }
        }
        @media screen and (min-height: 1000px) {
            body { align-items:flex-start; padding-top:50px; padding-bottom:50px; }
        }
        @media print {
            body { background:white; padding:0; }
            .phone-frame { box-shadow:none; background:white; color:black; max-width:100%; }
            .bottom-nav, .copy-btn, .share-buttons, .telegram-channel-card { display:none; }
        }
    </style>
</head>
<body>
    <div class="phone-frame">
        <div class="page-header">
            <h2>Team Reports</h2>
            <p>Build your network and earn more</p>
        </div>
        
        <!-- Invitation Card -->
        <div class="invite-card">
            <div class="invite-title">
                <i class="fas fa-user-plus"></i>
                Invite friend with link shared
            </div>
            
            <div class="invite-code-box">
                <div class="invite-code-label">Your Referral Code</div>
                <div class="invite-code"><?= htmlspecialchars($user['referral_code']) ?></div>
            </div>
            
            <div class="invite-link">
                <span class="invite-link-text" id="inviteLink"><?= htmlspecialchars($invite_link) ?></span>
                <button class="copy-btn" id="copyBtn" onclick="copyLink()">
                    <i class="fas fa-copy"></i> COPY LINK
                </button>
            </div>
            
            <div class="share-buttons">
                <button class="share-btn share-wa" onclick="shareWhatsApp()">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </button>
                <button class="share-btn share-tg" onclick="shareTelegram()">
                    <i class="fab fa-telegram"></i> Telegram
                </button>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $referral_stats['total'] ?? 0 ?></div>
                <div class="stat-label">Total Referrals</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $referral_stats['active'] ?? 0 ?></div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $referral_stats['last_30_days'] ?? 0 ?></div>
                <div class="stat-label">Last 30 Days</div>
            </div>
        </div>
        
        <!-- Total Earnings Card (if you have referral commissions) -->
        <?php if ($referral_earnings > 0): ?>
        <div class="earnings-card">
            <span class="earnings-label">Total Referral Earnings</span>
            <span class="earnings-value">ETB <?= number_format($referral_earnings, 2) ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Recent Referrals -->
        <div class="referrals-section">
            <div class="section-title">
                <i class="fas fa-history" style="color:#fbbf24;"></i>
                Recent Referrals
            </div>
            
            <?php if (empty($recent_referrals)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>No referrals yet</p>
                    <p style="font-size:0.8rem; margin-top:5px;">Share your invite link to get started</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_referrals as $referral): ?>
                <div class="referral-item">
                    <div class="referral-info">
                        <div class="referral-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="referral-details">
                            <h4><?= htmlspecialchars($referral['full_name']) ?></h4>
                            <p><?= date('M d, Y', strtotime($referral['created_at'])) ?></p>
                        </div>
                    </div>
                    <span class="referral-badge <?= $referral['is_active'] ? 'badge-active' : 'badge-pending' ?>">
                        <?= $referral['is_active'] ? 'Active' : 'Pending' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Official Telegram Channel Card (New) -->
        <div class="telegram-channel-card">
            <div class="telegram-channel-info">
                <span class="telegram-channel-label">Official Channel</span>
                <span class="telegram-channel-name">
                    <i class="fab fa-telegram"></i> Smi Investment
                </span>
            </div>
            <a href="https://t.me/smiptf_2" target="_blank" rel="noopener noreferrer" class="telegram-join-btn">
                <i class="fab fa-telegram"></i> Join Channel
            </a>
        </div>
        
        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <a href="home.php" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="product.php" class="nav-item"><i class="fas fa-cube"></i><span>Product</span></a>
            <a href="official.php" class="nav-item"><i class="fas fa-bullhorn"></i><span>Official</span></a>
            <a href="team.php" class="nav-item active"><i class="fas fa-users"></i><span>Team</span></a>
            <a href="profile.php" class="nav-item"><i class="fas fa-user"></i><span>Mine</span></a>
        </div>
    </div>
    
    <script>
    function copyLink() {
        var copyText = document.getElementById("inviteLink").innerText;
        navigator.clipboard.writeText(copyText).then(function() {
            var copyBtn = document.getElementById("copyBtn");
            copyBtn.innerHTML = '<i class="fas fa-check"></i> COPIED!';
            copyBtn.classList.add('copied');
            
            setTimeout(function() {
                copyBtn.innerHTML = '<i class="fas fa-copy"></i> COPY LINK';
                copyBtn.classList.remove('copied');
            }, 2000);
        }, function() {
            alert('Failed to copy link');
        });
    }
    
    function shareWhatsApp() {
        var link = document.getElementById("inviteLink").innerText;
        var text = encodeURIComponent("Join me on Smi Investment and start earning! Use my referral link: " + link);
        window.open("https://wa.me/?text=" + text, "_blank");
    }
    
    function shareTelegram() {
        var link = document.getElementById("inviteLink").innerText;
        var text = encodeURIComponent("Join me on Smi Investment and start earning! Use my referral link: " + link);
        window.open("https://t.me/share/url?url=" + encodeURIComponent(link) + "&text=" + text, "_blank");
    }
    </script>
</body>
</html>