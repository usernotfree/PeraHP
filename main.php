<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/wallet_data.php";
require_once __DIR__ . "/transaction_data.php";
require_login();
$user = current_user();
$userFirstName = explode(" ", trim($user["name"]))[0] ?: $user["name"];
$walletPageData = perahp_wallet_page_data($user);
$transactionPageData = perahp_transaction_page_data($user);
$pageData = array_merge($walletPageData, $transactionPageData);
$walletCount = count($walletPageData["wallets"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Digital Wallet Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        a {
            text-decoration: none;
        }
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
                    <strong><?php echo e($user["name"]); ?></strong>
                    <small><?php echo e($user["email"]); ?></small>
                </div>
            </a>
            <a class="mini-button logout-link" href="logout.php" style="margin-left:auto;">Logout</a>
        </div>
    </aside>

    <div class="page">
        <header class="topbar">
            <div>
                <h1>Dashboard</h1>
                <small style="color:var(--muted);">Welcome back, <?php echo e($userFirstName); ?></small>
            </div>
            <div class="top-actions">
                <button class="icon-button" id="menuButton">
                    <span></span><span></span><span></span>
                </button>
                <button class="ghost-button" id="printButton">🖨 Print</button>
            </div>
        </header>

        <section class="overview-band">
            <div class="overview-copy">
                <p class="eyebrow">Overview</p>
                <h2>Your money at a glance</h2>
                <p>Track your multi-currency balances and recent activity all in one place.</p>
            </div>
            <div class="readiness-card">
                <h2 style="font-size:1.1rem;">Account readiness</h2>
                <div class="progress-meter"><span style="width:76%;"></span></div>
                <div class="readiness-list">
                    <div><span>Profile</span><span>✅ Complete</span></div>
                    <div><span>Wallets</span><span><?php echo e($walletCount); ?> active</span></div>
                    <div><span>Verification</span><span>⏳ Pending</span></div>
                </div>
            </div>
        </section>

        <section class="metric-grid">
            <div class="metric-card main-metric">
                <span>💰 Total Balance</span>
                <strong id="totalBalance">PHP 0.00</strong>
                <small>All wallets combined</small>
            </div>
            <div class="metric-card">
                <span>Received (This month)</span>
                <strong id="monthlyReceived">PHP 0.00</strong>
                <small>This month</small>
            </div>
            <div class="metric-card">
                <span>Sent (This month)</span>
                <strong id="monthlySent">PHP 0.00</strong>
                <small>This month</small>
            </div>
            <div class="metric-card">
                <span>⏳ Pending</span>
                <strong id="pendingCount">0</strong>
                <small>Requests awaiting action</small>
            </div>
        </section>

        <section class="quick-actions">
            <a href="wallets.php" class="action-tile">💸 Send Money</a>
            <a href="wallets.php" class="action-tile">📩 Request Payment</a>
            <a href="exchange.php" class="action-tile">🔄 Exchange Currency</a>
            <a href="transactions.php" class="action-tile">📋 View Transactions</a>
        </section>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Wallets</p><h2>Multi-currency balances</h2></div>
                    <span class="badge neutral"><?php echo $walletPageData["walletSource"] === "database" ? "Database" : "Demo"; ?> data</span>
                </div>
                <div class="wallet-grid" id="walletGrid"></div>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Activity</p><h2>Recent activity feed</h2></div>
                    <span class="badge success"><?php echo $transactionPageData["transactionSource"] === "database" ? "Database" : "Demo"; ?> data</span>
                </div>
                <div class="activity-list" id="activityList"></div>
            </article>
        </section>

        <article class="panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Admin</p>
                    <h2>User management and audit log</h2>
                </div>
                <span class="badge warning">Role gated</span>
            </div>
            
            <div class="admin-list" style="margin-bottom: 25px; border-bottom: 1px solid var(--line); padding-bottom: 20px;">
                <div><strong>Maria Santos</strong><span class="badge success">Active</span><button class="mini-button">Suspend</button><button class="mini-button">Reset</button></div>
                <div><strong>Juan Dela Cruz</strong><span class="badge success">Active</span><button class="mini-button">Suspend</button><button class="mini-button">Reset</button></div>
                <div><strong>Login audit</strong><small>Maria signed in from 127.0.0.1 at 8:45 PM</small></div>
            </div>

            <section class="security-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                <article class="security-card"><span>Session</span><strong>Active state shown</strong><small>The current user and session state remain visible while working.</small></article>
                <article class="security-card"><span>Roles</span><strong>User and admin split</strong><small>Administrative actions are grouped away from normal wallet tasks.</small></article>
                <article class="security-card"><span>Audit</span><strong>Activity trail ready</strong><small>Login and transaction events have clear spaces for backend records.</small></article>
            </section>
        </article>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script>
        window.PERAHP_DATA = <?php echo perahp_json($pageData); ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>
