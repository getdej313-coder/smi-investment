<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>About Us - Smi Investment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { 
            margin:0; 
            padding:0; 
            box-sizing:border-box; 
            font-family:'Inter', sans-serif; 
        }

        body { 
            background:#0b1424; 
            min-height:100vh; 
            display:flex; 
            align-items:center; 
            justify-content:center; 
            padding:16px; 
        }

        /* Main container */
        .phone-frame { 
            max-width:400px; 
            width:100%; 
            background:#101b2b; 
            border-radius:36px; 
            padding:24px 20px 80px; 
            position:relative; 
            box-shadow:0 25px 50px -12px rgba(0,0,0,0.8); 
            margin:0 auto;
        }

        /* Header */
        .page-header { 
            display:flex; 
            align-items:center; 
            gap:16px; 
            margin-bottom:20px; 
        }
        
        .page-header h2 { 
            color:white; 
            font-size:1.5rem; 
            font-weight:600; 
        }
        
        .back-btn { 
            color:#fbbf24; 
            text-decoration:none; 
            font-size:1.3rem; 
            width:40px;
            height:40px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#1e2a3a;
            border-radius:12px;
            transition:0.2s;
            border:1px solid #2d3a4b;
        }
        
        .back-btn:hover { 
            background:#273649; 
            transform:scale(1.05);
        }

        /* Company Card - Dark theme like home page cards */
        .about-card { 
            background:#1e2a3a; 
            border-radius:24px; 
            padding:20px; 
            margin-bottom:20px; 
            border:1px solid #2d3a4b; 
            text-align:center; 
            box-shadow:0 6px 0 #0f172a;
        }
        
        .logo-large { 
            font-size:3.5rem; 
            color:#fbbf24; 
            margin-bottom:15px; 
        }
        
        .company-name { 
            color:white; 
            font-size:1.5rem; 
            font-weight:700; 
            margin-bottom:10px; 
        }
        
        .company-desc { 
            color:#a5b4cb; 
            line-height:1.6; 
            margin-bottom:20px; 
            font-size:0.95rem;
            padding:0 5px;
        }
        
        .stats-grid { 
            display:grid; 
            grid-template-columns:repeat(2,1fr); 
            gap:12px; 
            margin:20px 0; 
        }
        
        .stat-item { 
            background:#0f1a28; 
            padding:15px 10px; 
            border-radius:18px; 
            border:1px solid #2d3a4b;
            box-shadow:0 4px 0 #0a121f;
        }
        
        .stat-number { 
            color:#fbbf24; 
            font-size:1.2rem; 
            font-weight:700; 
        }
        
        .stat-label { 
            color:#a5b4cb; 
            font-size:0.75rem; 
            margin-top:5px;
        }
        
        .contact-info { 
            background:#0f1a28; 
            padding:16px; 
            border-radius:20px; 
            color:#a5b4cb; 
            line-height:2;
            text-align:left;
            font-size:0.9rem;
            border:1px solid #2d3a4b;
        }
        
        .contact-info div {
            display:flex;
            align-items:center;
            gap:12px;
        }
        
        .contact-info i {
            width:24px;
            text-align:center;
            color:#fbbf24;
            font-size:1.1rem;
        }
        
        /* Contract Card - Dark theme */
        .contract-card { 
            background:#1e2a3a; 
            border-radius:24px; 
            padding:20px; 
            margin-bottom:20px; 
            border:1px solid #2d3a4b; 
            max-height:450px; 
            overflow-y:auto; 
            box-shadow:0 6px 0 #0f172a;
        }
        
        .contract-card h3 { 
            color:#fbbf24; 
            margin-bottom:15px; 
            text-align:center;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            font-size:1.2rem;
            position:sticky;
            top:0;
            background:#1e2a3a;
            padding:12px 0;
            z-index:10;
            border-bottom:2px solid #2d3a4b;
            font-weight:700;
        }
        
        /* Contract Text - White/Grey background with dark text */
        .contract-text {
            background:#f8fafc;        /* Light grey-white background */
            padding:20px;
            border-radius:18px;
            border:1px solid #e2e8f0;
            color:#1e293b;              /* Dark text for body */
            font-size:0.9rem;
            line-height:1.7;
            white-space:pre-wrap;
            word-wrap:break-word;
            font-family:'Inter', monospace;
            box-shadow:inset 0 2px 4px rgba(0,0,0,0.02);
        }
        
        .contract-text h1, 
        .contract-text h2, 
        .contract-text h3 {
            color:#0f172a;              /* Darker headings */
            margin:20px 0 12px 0;
            font-weight:700;
        }
        
        .contract-text h1 {
            font-size:1.3rem;
            border-bottom:2px solid #e2e8f0;
            padding-bottom:10px;
        }
        
        .contract-text h2 {
            font-size:1.1rem;
        }
        
        .contract-text strong {
            color:#0f172a;              /* Dark bold text */
        }
        
        .contract-text hr {
            border:1px solid #e2e8f0;
            margin:20px 0;
        }
        
        /* Custom Scrollbar */
        .contract-card::-webkit-scrollbar {
            width:6px;
        }
        
        .contract-card::-webkit-scrollbar-track {
            background:#0f1a28;
            border-radius:10px;
        }
        
        .contract-card::-webkit-scrollbar-thumb {
            background:#fbbf24;
            border-radius:10px;
        }
        
        /* Bottom Navigation */
        .bottom-nav { 
            position:absolute; 
            bottom:0; 
            left:0; 
            right:0; 
            background:#0f1a28; 
            display:flex; 
            justify-content:space-around; 
            padding:12px 16px 20px; 
            border-top:1px solid #263340; 
            border-radius:30px 30px 0 0; 
        }
        
        .nav-item { 
            display:flex; 
            flex-direction:column; 
            align-items:center; 
            color:#6b7e99; 
            font-size:0.7rem; 
            text-decoration:none; 
            transition:0.2s; 
        }
        
        .nav-item i { 
            font-size:1.4rem; 
            margin-bottom:4px; 
        }
        
        .nav-item.active { 
            color:#fbbf24; 
        }
        
        .nav-item:hover { 
            color:#fbbf24; 
            transform:translateY(-2px);
        }
        
        /* ===== RESPONSIVE BREAKPOINTS ===== */
        @media screen and (min-width: 600px) and (max-width: 1024px) {
            body { padding:30px; background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%); }
            .phone-frame { max-width: 700px; border-radius: 40px; padding: 30px 30px 90px; }
            .stats-grid { grid-template-columns: repeat(4, 1fr); }
            .bottom-nav { padding: 15px 30px 25px; }
            .nav-item span { font-size: 0.8rem; }
            .nav-item i { font-size: 1.6rem; }
        }
        @media screen and (min-width: 1025px) and (max-width: 1440px) {
            body { padding: 40px; background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%); }
            .phone-frame { max-width: 900px; border-radius: 50px; padding: 40px 40px 100px; }
            .stats-grid { grid-template-columns: repeat(4, 1fr); gap: 15px; }
            .bottom-nav { padding: 15px 40px 25px; max-width: 900px; left: 50%; transform: translateX(-50%); border-radius: 30px 30px 0 0; }
        }
        @media screen and (min-width: 1441px) {
            body { padding: 50px; background: linear-gradient(145deg, #0b1a2e 0%, #1c3a4f 100%); }
            .phone-frame { max-width: 1200px; border-radius: 60px; padding: 50px 50px 120px; }
            .stats-grid { grid-template-columns: repeat(4, 1fr); gap: 20px; }
            .bottom-nav { max-width: 1200px; padding: 20px 50px 30px; left: 50%; transform: translateX(-50%); }
            .nav-item span { font-size: 0.9rem; }
            .nav-item i { font-size: 1.8rem; }
        }
        @media screen and (max-width: 399px) {
            .phone-frame { padding: 20px 15px 80px; }
            .stats-grid { grid-template-columns: 1fr; }
            .bottom-nav { padding: 10px 10px 15px; }
            .nav-item i { font-size: 1.2rem; }
            .nav-item span { font-size: 0.6rem; }
        }
        @media screen and (orientation: landscape) and (max-height: 600px) {
            body { padding: 20px; }
            .phone-frame { max-width: 700px; padding: 20px 20px 70px; }
            .contract-card { max-height: 250px; }
            .bottom-nav { padding: 8px 20px 15px; }
        }
        @media screen and (min-height: 1000px) {
            body { align-items: flex-start; padding-top: 50px; padding-bottom: 50px; }
        }
        @media print {
            body { background: white; padding: 0; }
            .phone-frame { box-shadow: none; background: white; color: black; max-width: 100%; }
            .bottom-nav, .back-btn { display: none; }
            .about-card, .contract-card { background: white; color: black; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>
    <div class="phone-frame">
        <!-- Header -->
        <div class="page-header">
            <a href="profile.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
            <h2>About Us</h2>
        </div>
        
        <!-- Company Info Card -->
        <div class="about-card">
            <div class="logo-large"><i class="fas fa-chart-line"></i></div>
            <div class="company-name">Smi Investment</div>
            <div class="company-desc">Leading investment platform in Ethiopia, helping individuals grow their wealth through smart investment strategies.</div>
            <div class="stats-grid">
                <div class="stat-item"><div class="stat-number">5,000+</div><div class="stat-label">Active Investors</div></div>
                <div class="stat-item"><div class="stat-number">ETB 15M+</div><div class="stat-label">Total Invested</div></div>
                <div class="stat-item"><div class="stat-number">4.8/5</div><div class="stat-label">User Rating</div></div>
                <div class="stat-item"><div class="stat-number">2020</div><div class="stat-label">Founded</div></div>
            </div>
            <div class="contact-info">
                <div><i class="fas fa-map-marker-alt"></i> Addis Ababa, Ethiopia</div>
                <div><i class="fas fa-envelope"></i> info@smi_investment.com</div>
                <div><i class="fas fa-phone"></i> +251 948867799</div>
            </div>
        </div>

        <!-- Employment Contract Card with Light Background -->
        <div class="contract-card">
            <h3><i class="fas fa-file-contract"></i> Standard Employment Contract</h3>
            <div class="contract-text">
# Employment Contract

## This contract is concluded between the following parties:

**Party A information (employer):**
Company name: TYPICA Holdings, Inc.

**Party B (employee) information:**
Name: ___SMI-investment Group, .Inc____________________

ID card: __SNT/024/2020_____________________

**Contract validity period:**
Effective date: __December 20/2020_____________________

End date: _february 20/2030______________________

Contract duration: 10 year

---------------------------------------------------------

### Article 1: Nature of the contract and job description
Nature of the contract: This contract is a full-time labor contract for online work. Party B must complete the work tasks through the Internet and ensure that the work progress and quality meet the requirements of Party A.

Job responsibilities:
- Party B’s main responsibility is to promote the company and help the company increase its visibility.
- Party B must arrange work according to the time specified by Party A and ensure that it maintains close contact with the company.
- Independent time management: Party B can arrange working hours according to personal circumstances, but must ensure that tasks are completed and work results are delivered on time.
- Delivery method: Party B must send the work results through the online platform designated by Party A and ensure that each work is completed within the specified time.

---

### Article 2: Reward and Salary Structure
The rewards for both parties will be calculated based on the completion rate of daily tasks.

Deposit clause: At the time of signing this contract, Party B has paid the rental fee for the coffee estate as a work deposit (the estate generates income every day). This deposit is intended to ensure that Party B can fulfill their job responsibilities during the contract period and to ensure the authenticity and compliance of the assigned tasks.

Deposit refund: After the contract expires or when the party B decides to terminate the work early, if the party B has not committed any illegal acts during the work period, he can obtain all the profits of the estate. If the party B terminates the work early, the investment cost in the estate will be refunded. Activation conditions - If there is any violation of regulations or failure to complete the work as required, Party A has the right to deduct a certain amount from the deposit as compensation.

---

### Article 3: Rights and obligations of both parties
#### Rights of Party A:
- Party A is obliged to pay Party B’s salary on time and guarantee to provide a legal working environment and necessary equipment support.
- Party A has the right to supervise and evaluate Party B’s work progress and adjust the work content if necessary.
- If Party B violates the company’s regulations or fails to complete the work requirements, Party A has the right to terminate the contract in advance and deduct the deposit amount according to the breach of contract.

#### Rights and obligations of Party B:
- Party B must complete the work tasks in accordance with Party A’s requirements and ensure the authenticity and effectiveness of the work results.
- Party B has the right to obtain training and promotion opportunities during the contract period and receive fair remuneration and treatment.
- Party B is obliged to keep confidential all information obtained during work and shall not disclose or use the information for personal gain.

#### Termination conditions of the contract:
- If Party A needs to terminate the contract in advance, it must notify Party B in writing at least 5 days in advance and return the deposit.
- If Party B needs to terminate the contract in advance, it must notify Party B in writing at least 5 days in advance and complete all unfinished work tasks.

---

### Article 4: Contract Termination and Deposit Management
#### Contract Validity and Extension:
This contract shall take effect from the date of signing and shall be valid for 10 year. After the expiration of the contract, if both parties have no objection, the contract can be renewed through mutual agreement.

#### Violation Liability:
- If Party B fails to perform the obligations agreed in this contract, Party A has the right to terminate the contract in advance and deduct a certain amount from the deposit as liquidated damages.
- If Party A fails to pay wages on time or violates other contract terms, Party B has the right to terminate the contract immediately and request a full refund of the deposit.

#### Deposit Refund:
If Party B performs the contract until the expiration of the contract and no illegal acts occur, Party A is obliged to refund the entire deposit within 7 working days after the termination of the contract.

If Party B terminates the work or terminates the contract in advance, the deposit shall be refunded after deducting the corresponding liquidated damages.

---

### Article 5: Confidentiality and Intellectual Property Clauses
#### Confidentiality:
During the contract period and within one year after the end of the contract, Party B shall not disclose any confidential information obtained during the work, including but not limited to trade secrets, technical data and customer information.

#### Intellectual Property Ownership:
All work results (including but not limited to products, teams, technology development, etc.) formed by Party B during the work period shall become the exclusive property of Party A. Party B shall not use or transfer these rights without the consent of Party A.

#### Article 6: Dispute Resolution
Dispute Resolution Mechanism:
If a dispute arises during the performance of the contract, the parties shall first negotiate to resolve it. If the negotiation fails, the parties may submit to arbitration. Applicable Law: This contract is governed by the laws of Tanzania and all disputes must be resolved in accordance with applicable laws.

#### Article 7: Additional Provisions
Validity of the Contract:
This contract shall take effect when Party B starts work or receives remuneration from Party A, indicating that Party B has agreed to all the terms and conditions of this contract.

---

**Party A’s signature:** _____husen Bikila__________________


**Party B’s signature:** _______Josef Smith________________

**Signature date:** ________December 20/_2020______________

---

**Contract**
            </div>
        </div>

        <!-- Bottom Navigation -->
      
    </div>
</body>
</html>