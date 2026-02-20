<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Official - Smi Investment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #0b1424;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        /* Main container – responsive max-width */
        .phone-frame {
            max-width: 400px;
            width: 100%;
            background: #101b2b;
            border-radius: 36px;
            padding: 24px 20px 80px;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.8);
            margin: 0 auto;
            transition: all 0.3s ease;
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-header h2 {
            color: white;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header h2 i {
            color: #fbbf24;
            background: #1e2a3a;
            padding: 10px;
            border-radius: 50%;
        }

        /* Official information cards */
        .info-grid {
            display: grid;
            gap: 16px;
            margin-bottom: 30px;
        }

        .info-card {
            background: #1e2a3a;
            border-radius: 24px;
            padding: 20px;
            border: 1px solid #2d3a4b;
            box-shadow: 0 5px 0 #0f172a;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: 0.2s;
        }

        .info-card:hover {
            transform: translateY(-2px);
            background: #273649;
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: #273649;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fbbf24;
            font-size: 1.5rem;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            color: #a5b4cb;
            font-size: 0.8rem;
            margin-bottom: 4px;
        }

        .info-value {
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            word-break: break-word;
        }

        .info-value a {
            color: #fbbf24;
            text-decoration: none;
        }

        .info-value a:hover {
            text-decoration: underline;
        }

        /* Bottom Navigation */
        .bottom-nav {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #0f1a28;
            display: flex;
            justify-content: space-around;
            padding: 12px 16px 20px;
            border-top: 1px solid #263340;
            border-radius: 30px 30px 0 0;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #6b7e99;
            font-size: 0.7rem;
            text-decoration: none;
            transition: 0.2s;
            flex: 1;
        }

        .nav-item i {
            font-size: 1.4rem;
            margin-bottom: 4px;
        }

        .nav-item.active {
            color: #fbbf24;
        }

        .nav-item:hover {
            color: #fbbf24;
            transform: translateY(-2px);
        }

        /* ===== RESPONSIVE BREAKPOINTS ===== */

        /* Tablet (600px - 1024px) */
        @media screen and (min-width: 600px) and (max-width: 1024px) {
            body {
                padding: 30px;
                background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%);
            }
            .phone-frame {
                max-width: 700px;
                border-radius: 40px;
                padding: 30px 30px 90px;
            }
            .page-header h2 {
                font-size: 2rem;
            }
            .info-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            .bottom-nav {
                padding: 15px 30px 25px;
            }
            .nav-item span {
                font-size: 0.8rem;
            }
            .nav-item i {
                font-size: 1.6rem;
            }
        }

        /* Desktop (1025px - 1440px) */
        @media screen and (min-width: 1025px) and (max-width: 1440px) {
            body {
                padding: 40px;
                background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%);
            }
            .phone-frame {
                max-width: 900px;
                border-radius: 50px;
                padding: 40px 40px 100px;
            }
            .info-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 25px;
            }
            .bottom-nav {
                padding: 15px 40px 25px;
                max-width: 900px;
                left: 50%;
                transform: translateX(-50%);
                border-radius: 30px 30px 0 0;
            }
        }

        /* Large Desktop (1441px and above) */
        @media screen and (min-width: 1441px) {
            body {
                padding: 50px;
                background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%);
            }
            .phone-frame {
                max-width: 1200px;
                border-radius: 60px;
                padding: 50px 50px 120px;
            }
            .info-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 30px;
            }
            .info-card {
                padding: 25px;
            }
            .info-value {
                font-size: 1.2rem;
            }
            .bottom-nav {
                max-width: 1200px;
                padding: 20px 50px 30px;
                left: 50%;
                transform: translateX(-50%);
            }
            .nav-item span {
                font-size: 0.9rem;
            }
            .nav-item i {
                font-size: 1.8rem;
            }
        }

        /* Small Mobile (below 400px) */
        @media screen and (max-width: 399px) {
            .phone-frame {
                padding: 20px 15px 80px;
            }
            .page-header h2 {
                font-size: 1.5rem;
            }
            .info-card {
                padding: 15px;
            }
            .info-icon {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }
            .info-value {
                font-size: 1rem;
            }
            .bottom-nav {
                padding: 10px 10px 15px;
            }
            .nav-item i {
                font-size: 1.2rem;
            }
            .nav-item span {
                font-size: 0.6rem;
            }
        }

        /* Landscape mode */
        @media screen and (orientation: landscape) and (max-height: 600px) {
            body {
                padding: 20px;
                align-items: flex-start;
            }
            .phone-frame {
                max-width: 700px;
                padding: 20px 20px 70px;
            }
            .info-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            .bottom-nav {
                padding: 8px 20px 15px;
            }
        }

        /* Very tall screens */
        @media screen and (min-height: 1000px) {
            body {
                align-items: flex-start;
                padding-top: 50px;
                padding-bottom: 50px;
            }
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .phone-frame {
                box-shadow: none;
                background: white;
                color: black;
                max-width: 100%;
            }
            .bottom-nav {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="phone-frame">
        <div class="page-header">
            <h2><i class="fas fa-building"></i> Official Info</h2>
        </div>

        <div class="info-grid">
            <!-- Company Registration -->
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-registered"></i></div>
                <div class="info-content">
                    <div class="info-label">Registration No</div>
                    <div class="info-value">#SNT/024/2020</div>
                </div>
            </div>

            <!-- Address -->
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="info-content">
                    <div class="info-label">Head Office</div>
                    <div class="info-value">Addis Ababa, Ethiopia</div>
                </div>
            </div>

            <!-- Email -->
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                <div class="info-content">
                    <div class="info-label">Email Support</div>
                    <div class="info-value"><a href="mailto:support@smi_investment.com">support@smi_investment.com</a></div>
                </div>
            </div>

            <!-- Phone -->
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
                <div class="info-content">
                    <div class="info-label">Customer Service</div>
                    <div class="info-value"><a href="tel:+251911234567">+251 948 868 978</a></div>
                </div>
            </div>

            <!-- Website -->
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-globe"></i></div>
                <div class="info-content">
                    <div class="info-label">Official Website</div>
                    <div class="info-value"><a href="#" target="_blank">www.smi_investment.com</a></div>
                </div>
            </div>

            <!-- License -->
            <div class="info-card">
                <div class="info-icon"><i class="fas fa-certificate"></i></div>
                <div class="info-content">
                    <div class="info-label">License</div>
                    <div class="info-value">FCA Authorized</div>
                </div>
            </div>
        </div>

        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <a href="home.php" class="nav-item"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="product.php" class="nav-item"><i class="fas fa-cube"></i><span>Product</span></a>
            <a href="official.php" class="nav-item active"><i class="fas fa-bullhorn"></i><span>Official</span></a>
            <a href="team.php" class="nav-item"><i class="fas fa-users"></i><span>Team</span></a>
            <a href="profile.php" class="nav-item"><i class="fas fa-user"></i><span>Mine</span></a>
        </div>
    </div>
</body>
</html>