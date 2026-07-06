<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Wallets</title>
    <link rel="stylesheet" href="styles.css">
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
            <a class="nav-link" href="main.php">Home</a>
            <a class="nav-link active" href="wallets.php">Wallets</a>
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link" href="reports.php">Reports</a>
            <a class="nav-link" href="settings.php">Settings</a>
        </nav>
        <div class="auth-box">
            <a class="profile-link" href="profile.php" aria-label="Open profile">
                <span class="status-dot"></span>
                <div>
                    <strong>Maria Santos</strong>
                    <small>maria@perahp.test</small>
                </div>
            </a>
            <button class="mini-button" id="logoutButton" style="margin-left:auto;">Logout</button>
        </div>
    </aside>

    <div class="page">
        <header class="topbar">
            <div>
                <h1>Wallets</h1>
                <small style="color:var(--muted);">Manage balances across supported currencies</small>
            </div>
            <div class="top-actions">
                <button class="icon-button" id="menuButton" aria-label="Open menu">
                    <span></span><span></span><span></span>
                </button>
                <button class="ghost-button" id="printButton">Print</button>
            </div>
        </header>

        <section class="overview-band">
            <div class="overview-copy">
                <p class="eyebrow">Wallet Center</p>
                <h2>Your balances are separated from the home page.</h2>
                <p>Review each wallet, compare PHP base values, and check the conversion rates used by the transaction and exchange pages.</p>
            </div>
            <div class="readiness-card">
                <h2 style="font-size:1.1rem;">Wallet status</h2>
                <div class="progress-meter"><span style="width:100%;"></span></div>
                <div class="readiness-list">
                    <div><span>Active wallets</span><span>5</span></div>
                    <div><span>Base currency</span><span>PHP</span></div>
                    <div><span>Rate source</span><span>Stored mock rates</span></div>
                </div>
            </div>
        </section>

        <section class="metric-grid">
            <div class="metric-card main-metric">
                <span>Total Balance</span>
                <strong id="totalBalance">PHP 0.00</strong>
                <small>All wallets combined</small>
            </div>
            <div class="metric-card">
                <span>Primary Wallet</span>
                <strong>PHP</strong>
                <small>Default settlement currency</small>
            </div>
            <div class="metric-card">
                <span>Foreign Wallets</span>
                <strong>4</strong>
                <small>USD, EUR, JPY, SGD</small>
            </div>
            <div class="metric-card">
                <span>Wallet Health</span>
                <strong>Good</strong>
                <small>No blocked balances</small>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div><p class="eyebrow">Wallets</p><h2>Multi-currency balances</h2></div>
                <span class="badge neutral">PHP base</span>
            </div>
            <div class="wallet-grid" id="walletGrid"></div>
        </section>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Exchange Rates</p><h2>Conversion table</h2></div>
                    <span class="badge neutral">Reference</span>
                </div>
                <div class="rate-grid compact-rates" id="rateGrid"></div>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Wallet Actions</p><h2>Common tasks</h2></div>
                    <span class="badge success">Ready</span>
                </div>
                <div class="settings-list">
                    <div><span>Send from wallet</span><a class="mini-button" href="transactions.php#send-money">Open transfer</a></div>
                    <div><span>Convert funds</span><a class="mini-button" href="exchange.php">Open exchange</a></div>
                    <div><span>Review activity</span><a class="mini-button" href="transactions.php">Open ledger</a></div>
                </div>
            </article>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script src="script.js"></script>
</body>
</html>

