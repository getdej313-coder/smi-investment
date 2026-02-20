<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Partner Agreement - Smi Investment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#0b1424; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:16px; }
        .phone-frame { max-width:400px; width:100%; background:#101b2b; border-radius:36px; padding:24px 20px 80px; position:relative; }
        .page-header { display:flex; align-items:center; gap:20px; margin-bottom:20px; }
        .page-header h2 { color:white; font-size:1.6rem; }
        .back-btn { color:#fbbf24; text-decoration:none; font-size:1.2rem; }
        .agreement-card { background:#1e2a3a; border-radius:24px; padding:20px; margin-bottom:16px; border:1px solid #2d3a4b; }
        .agreement-title { color:#fbbf24; font-size:1.1rem; margin-bottom:15px; }
        .agreement-text { color:#a5b4cb; line-height:1.6; margin-bottom:15px; }
        .signature-box { background:#0f1a28; padding:20px; border-radius:15px; margin-top:20px; }
        .accept-btn { background:#fbbf24; color:#0b1424; border:none; padding:15px; border-radius:30px; width:100%; font-weight:600; cursor:pointer; margin-top:20px; }
        .bottom-nav { position:absolute; bottom:0; left:0; right:0; background:#0f1a28; display:flex; justify-content:space-around; padding:12px 16px 20px; border-top:1px solid #263340; border-radius:30px 30px 0 0; }
        .nav-item { display:flex; flex-direction:column; align-items:center; color:#6b7e99; font-size:0.7rem; text-decoration:none; }
        .nav-item i { font-size:1.4rem; margin-bottom:4px; }
        .nav-item.active { color:#fbbf24; }
    </style>
</head>
<body>
    <div class="phone-frame">
        <div class="page-header">
            <a href="profile.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            <h2>Partner Agreement</h2>
        </div>
        
        <div class="agreement-card">
            <div class="agreement-title">Terms & Conditions</div>
            <div class="agreement-text">
                By becoming a partner of Smi Investment, you agree to:
                <br><br>
                1. Provide accurate and truthful information
                <br>
                2. Comply with all investment regulations
                <br>
                3. Not engage in fraudulent activities
                <br>
                4. Maintain confidentiality of your account
                <br>
                5. Accept all risks associated with investments
                <br><br>
                This agreement is legally binding and governed by the laws of Ethiopia.
            </div>
            
            <div class="signature-box">
                <div style="color:#fbbf24; margin-bottom:10px;">Agreed on: <?= date('F d, Y') ?></div>
                <div style="color:white;">Partner ID: #<?= $_SESSION['user_id'] ?></div>
            </div>
            
            <button class="accept-btn" onclick="alert('Agreement accepted!')"><i class="fas fa-check-circle"></i> Accept Agreement</button>
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