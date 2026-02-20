<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Telegram Service - Smi Investment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; }
        .page-header { display:flex; align-items:center; gap:20px; margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.6rem; }
        .back-btn { color:#fbbf24; text-decoration:none; font-size:1.2rem; }
        .telegram-card { background:#1e2a3a; border-radius:24px; padding:30px; text-align:center; border:1px solid #2d3a4b; }
        .telegram-icon { font-size:5rem; color:#0088cc; margin-bottom:20px; }
        .telegram-title { color:white; font-size:1.3rem; margin-bottom:15px; }
        .telegram-desc { color:#a5b4cb; margin-bottom:25px; line-height:1.6; }
        .join-btn { display:inline-block; background:#0088cc; color:white; padding:15px 40px; border-radius:40px; text-decoration:none; font-weight:600; margin-bottom:20px; transition:0.2s; }
        .join-btn:hover { background:#006699; transform:translateY(-2px); }
        .qr-code { width:150px; height:150px; background:#0f1a28; margin:20px auto; border-radius:20px; display:flex; align-items:center; justify-content:center; cursor:pointer; }
        .qr-code i { font-size:5rem; color:#fbbf24; }
        .qr-code:hover { background:#1e2a3a; }
        .bottom-nav { position:absolute; bottom:0; left:0; right:0; background:#0f1a28; display:flex; justify-content:space-around; padding:12px 16px 20px; border-top:1px solid #263340; border-radius:30px 30px 0 0; }
        .nav-item { display:flex; flex-direction:column; align-items:center; color:#6b7e99; font-size:0.7rem; text-decoration:none; }
        .nav-item i { font-size:1.4rem; margin-bottom:4px; }
        .nav-item.active { color:#fbbf24; }
        .nav-item:hover { color:#fbbf24; }
        .link-note { color:#a5b4cb; font-size:0.8rem; margin-top:10px; }
    </style>
</head>
<body>
    <div class="phone-frame">
        <div class="page-header">
            <a href="profile.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            <h2>Telegram Service</h2>
        </div>
        
        <div class="telegram-card">
            <div class="telegram-icon">
                <i class="fab fa-telegram"></i>
            </div>
            <div class="telegram-title">Join Our Telegram Channel</div>
            <div class="telegram-desc">
                Get instant updates on new investments, bonus offers, and important announcements directly on Telegram.
            </div>
            
            <!-- Active Telegram Link - Opens in new tab -->
            <a href="https://t.me/smiptf_2" target="_blank" rel="noopener noreferrer" class="join-btn">
                <i class="fab fa-telegram"></i> Join Channel
            </a>
            
            <!-- QR Code also links to Telegram -->
            <a href="https://t.me/smiptf_2" target="_blank" rel="noopener noreferrer" style="text-decoration:none;">
                <div class="qr-code">
                    <i class="fas fa-qrcode"></i>
                </div>
            </a>
            <div style="color:#a5b4cb; font-size:0.9rem; margin-top:10px;">Click QR to join via Telegram</div>
            
            <div class="link-note">
                <i class="fas fa-external-link-alt"></i> t.me/smiptf_2
            </div>
        </div>
        
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