<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Exchange</title>
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
            <a class="nav-link" href="wallets.php">Wallets</a>
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link active" href="exchange.php">Exchange</a>
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
                <h1>Exchange</h1>
                <small style="color:var(--muted);">Convert funds between wallet currencies</small>
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
                <p class="eyebrow">Currency Exchange</p>
                <h2>Move money between wallets without leaving the Exchange page.</h2>
                <p>The conversion form, current rates, and wallet balances are now grouped in their own workspace.</p>
            </div>
            <div class="readiness-card">
                <h2 style="font-size:1.1rem;">Exchange readiness</h2>
                <div class="progress-meter"><span style="width:92%;"></span></div>
                <div class="readiness-list">
                    <div><span>Available pairs</span><span>PHP, USD, EUR, JPY, SGD</span></div>
                    <div><span>Validation</span><span>Amount and balance checks</span></div>
                    <div><span>Rate source</span><span>Stored mock rates</span></div>
                </div>
            </div>
        </section>

        <section class="grid two-columns">
            <article class="panel" id="exchange">
                <div class="panel-heading">
                    <div><p class="eyebrow">Currency Exchange</p><h2>Convert between wallets</h2></div>
                    <span class="badge neutral">Stored rates</span>
                </div>
                <form class="form-stack" id="exchangeForm">
                    <div class="form-row">
                        <label>Amount<input type="number" id="exchangeAmount" min="1" value="50"></label>
                        <label>From<select id="exchangeFrom"></select></label>
                        <label>To<select id="exchangeTo"></select></label>
                    </div>
                    <div class="conversion-preview">
                        <span>Exchange preview</span>
                        <strong id="exchangePreview">PHP 0.00</strong>
                        <small>Mock rates are managed by admin in this static version.</small>
                    </div>
                    <button class="primary-button" type="submit">Exchange funds</button>
                </form>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Balances</p><h2>Wallets available</h2></div>
                    <span class="badge success">Live mock data</span>
                </div>
                <div class="wallet-grid compact-wallets" id="walletGrid"></div>
            </article>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div><p class="eyebrow">Exchange Rates</p><h2>Admin-managed conversion table</h2></div>
                <span class="badge neutral">PHP reference</span>
            </div>
            <div class="rate-grid" id="rateGrid"></div>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script src="script.js"></script>
</body>
</html>
