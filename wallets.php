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
    <title>Wallets - PeraHP</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Remove underlines from all links */
        a { text-decoration: none; }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="main.php">
            <span class="brand-mark">PHP</span>
            <div>
                <strong>PeraHP</strong>
                <small>Digital wallet</small>
            </div>
        </a>
        <nav class="nav-list">
            <a class="nav-link" href="main.php">Dashboard</a>
            <a class="nav-link active" href="wallets.php">Wallets</a>
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link" href="reports.php">Reports</a>
            <a class="nav-link" href="settings.php">Settings</a>
        </nav>
        <div class="auth-box">
            <span class="status-dot"></span>
            <div>
                <strong><?php echo e($user["name"]); ?></strong>
                <small><?php echo e($user["email"]); ?></small>
            </div>
            <a class="mini-button logout-link" href="logout.php" style="margin-left:auto;">Logout</a>
        </div>
    </aside>

    <div class="page">
        <header class="topbar">
            <div>
                <h1>Wallets & Transfers</h1>
                <small style="color:var(--muted);">Manage your funds and initiate payments</small>
            </div>
        </header>

        <section class="panel" style="margin-bottom: 25px;">
            <div class="panel-heading">
                <div><p class="eyebrow">Wallets</p><h2>Multi-currency balances</h2></div>
                <span class="badge neutral">PHP base</span>
            </div>
            <div class="wallet-grid" id="walletGrid"></div>
        </section>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">Send Money</p>
                        <h2>Transfer with live conversion</h2>
                    </div>
                    <span class="badge success">Validated Flow</span>
                </div>
                <form id="sendForm" class="form-stack">
                    <label>Recipient Email
                        <input type="email" id="recipientEmail" placeholder="juan@perahp.test" required>
                    </label>
                    <label>Amount
                        <input type="number" id="sendAmount" placeholder="100.00" step="0.01" required>
                    </label>
                    <div class="form-row two">
                        <label>From <select id="sendFrom"></select></label>
                        <label>To <select id="sendTo"></select></label>
                    </div>
                    <div class="conversion-preview">
                        <span>Converted amount</span>
                        <strong id="sendPreview">PHP 0.00</strong>
                        <small id="sendPhpValue">PHP base value: PHP 0.00</small>
                    </div>
                    <button type="submit" class="primary-button">Send payment</button>
                </form>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">Receive & Request</p>
                        <h2>Generate payment reference</h2>
                    </div>
                    <span class="badge warning">Pending Queue</span>
                </div>
                <form id="requestForm" class="form-stack">
                    <label>Amount
                        <input type="number" id="requestAmount" placeholder="2500" step="0.01" required>
                    </label>
                    <label>Currency
                        <select id="requestCurrency"></select>
                    </label>
                    <label>Payer Email
                        <input type="email" id="payerEmail" placeholder="client@example.com" required>
                    </label>
                    <button type="submit" class="secondary-button">Generate reference</button>
                    <div class="reference-box" style="margin-top:20px;">
                        <span>Latest reference</span>
                        <strong id="referenceCode">-</strong>
                        <small id="referenceStatus">Status: Waiting</small>
                    </div>
                </form>
            </article>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script src="script.js"></script>
</body>
</html>
