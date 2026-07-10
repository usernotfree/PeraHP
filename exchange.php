<?php
require_once __DIR__ . "/auth.php";
require_login();
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exchange - PeraHP</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="main.php"><span class="brand-mark">PHP</span><div><strong>PeraHP</strong><small>Digital wallet</small></div></a>
        <nav class="nav-list">
            <a class="nav-link" href="main.php">Dashboard</a>
            <a class="nav-link" href="wallets.php">Wallets</a>
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link active" href="exchange.php">Exchange</a>
            <a class="nav-link" href="reports.php">Reports</a>
            <a class="nav-link" href="settings.php">Settings</a>
        </nav>
        <div class="auth-box">
            <span class="status-dot"></span>
            <div><strong><?php echo e($user["name"]); ?></strong><small><?php echo e($user["email"]); ?></small></div>
            <a class="mini-button logout-link" href="logout.php" style="margin-left:auto;">Logout</a>
        </div>
    </aside>

    <div class="page">
        <header class="topbar">
            <div><h1>Currency Exchange</h1><small style="color:var(--muted);">Convert funds between your wallets</small></div>
            <button class="icon-button" id="menuButton"><span></span><span></span><span></span></button>
        </header>

        <section class="panel" id="exchange">
            <div class="panel-heading">
                <div><p class="eyebrow">Currency Exchange</p><h2>Convert between wallets</h2></div>
            </div>
            
            <form class="form-stack" id="exchangeForm">
                <div class="form-row">
                    <label>Amount <input type="number" id="exchangeAmount" min="1" placeholder="0.00"></label>
                    <label>From <select id="exchangeFrom"></select></label>
                    <label>To <select id="exchangeTo"></select></label>
                </div>
                
                <div class="conversion-preview">
                    <span>Exchange preview</span>
                    <strong id="exchangePreview">PHP 0.00</strong>
                    <small id="dynamicRateDisplay" style="display:block; margin-top:5px; color:var(--muted);">
                        Select currencies to see the current rate
                    </small>
                </div>
                
                <button class="primary-button" type="submit">Exchange funds</button>
            </form>

            <div class="static-rates" style="margin-top: 40px; padding: 25px; border: 1px solid var(--line); border-radius: 8px; background: #fff;">
                <h3 style="font-size: 1.1rem; margin-bottom: 20px; color: var(--text);">Reference Exchange Rates</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div style="padding: 15px; background: #f3f4f6; border-radius: 8px; font-size: 1rem;"><strong>1 USD = PHP 58.50</strong></div>
                    <div style="padding: 15px; background: #f3f4f6; border-radius: 8px; font-size: 1rem;"><strong>1 EUR = PHP 63.20</strong></div>
                    <div style="padding: 15px; background: #f3f4f6; border-radius: 8px; font-size: 1rem;"><strong>1 JPY = PHP 0.39</strong></div>
                    <div style="padding: 15px; background: #f3f4f6; border-radius: 8px; font-size: 1rem;"><strong>1 SGD = PHP 43.40</strong></div>
                </div>
            </div>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script src="script.js"></script>
</body>
</html>
