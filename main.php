<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Dashboard</title>
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
            <a class="nav-link active" href="main.php">Dashboard</a>
            <a class="nav-link" href="wallets.php">Wallets</a>
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
                <h1>Dashboard</h1>
                <small style="color:var(--muted);">Welcome back, Maria</small>
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
                <p class="eyebrow">Overview</p>
                <h2>Your money at a glance</h2>
                <p>Use the dashboard for quick status checks, then open each workspace from the sidebar for detailed wallet, transaction, exchange, and reporting tasks.</p>
            </div>
            <div class="readiness-card">
                <h2 style="font-size:1.1rem;">Account readiness</h2>
                <div class="progress-meter"><span style="width:76%;"></span></div>
                <div class="readiness-list">
                    <div><span>Profile</span><span>Complete</span></div>
                    <div><span>Wallets</span><span>5 active</span></div>
                    <div><span>Verification</span><span>Pending</span></div>
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
                <span>Received (Jun)</span>
                <strong id="monthlyReceived">PHP 0.00</strong>
                <small>This month</small>
            </div>
            <div class="metric-card">
                <span>Sent (Jun)</span>
                <strong id="monthlySent">PHP 0.00</strong>
                <small>This month</small>
            </div>
            <div class="metric-card">
                <span>Pending</span>
                <strong id="pendingCount">0</strong>
                <small>Requests awaiting action</small>
            </div>
        </section>

        <section class="quick-actions">
            <a class="action-tile" href="transactions.php#send-money">Send money</a>
            <a class="action-tile" href="transactions.php#requests">Request payment</a>
            <a class="action-tile" href="exchange.php">Exchange currency</a>
            <a class="action-tile" href="transactions.php">View history</a>
        </section>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Activity</p><h2>Recent activity feed</h2></div>
                    <span class="badge success">Live mock data</span>
                </div>
                <div class="activity-list" id="activityList"></div>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Modules</p><h2>Open a workspace</h2></div>
                    <span class="badge neutral">Separate pages</span>
                </div>
                <div class="module-grid dashboard-modules">
                    <a class="module-card" href="wallets.php"><span>Wallets</span><strong>Balances</strong><small>View multi-currency wallet totals.</small></a>
                    <a class="module-card" href="transactions.php"><span>Transactions</span><strong>Ledger</strong><small>Send, request, search, and filter activity.</small></a>
                    <a class="module-card" href="exchange.php"><span>Exchange</span><strong>Convert</strong><small>Move funds between wallet currencies.</small></a>
                    <a class="module-card" href="reports.php"><span>Reports</span><strong>Summaries</strong><small>Review monthly money movement.</small></a>
                </div>
            </article>
        </section>

        <section class="security-grid">
            <article class="security-card"><span>Session</span><strong>Active state shown</strong><small>The current user and session state remain visible while working.</small></article>
            <article class="security-card"><span>Roles</span><strong>User and admin split</strong><small>Administrative actions are grouped away from normal wallet tasks.</small></article>
            <article class="security-card"><span>Audit</span><strong>Activity trail ready</strong><small>Login and transaction events have clear spaces for backend records.</small></article>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script src="script.js"></script>
</body>
</html>
