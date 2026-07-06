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
    <title>PeraHP - Home</title>
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
            <a class="nav-link active" href="main.php">Home</a>
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
                <h1>Home</h1>
                <small style="color:var(--muted);">Promos, wallet shortcuts, and account highlights</small>
            </div>
            <div class="top-actions">
                <button class="icon-button" id="menuButton" aria-label="Open menu">
                    <span></span><span></span><span></span>
                </button>
                <button class="ghost-button" id="printButton">Print</button>
            </div>
        </header>

        <section class="home-hero">
            <div class="home-hero-copy">
                <p class="eyebrow">PeraHP Everyday</p>
                <h2>Spend, save, and discover wallet deals from one home screen.</h2>
                <p>Start with the promos and shortcuts that matter most. Currency exchange is still available when needed, but it no longer drives the main landing page.</p>
                <div class="hero-actions">
                    <a class="primary-button" href="transactions.php#send-money">Send money</a>
                    <a class="secondary-button" href="wallets.php">View wallets</a>
                </div>
            </div>
            <div class="hero-phone" aria-label="PeraHP wallet preview">
                <div class="phone-status">
                    <span>PeraHP</span>
                    <strong>PHP 128,090.00</strong>
                </div>
                <div class="phone-card wallet-card-visual">
                    <span>Payday Boost</span>
                    <strong>15% bill rewards</strong>
                    <small>Available until Jul 31</small>
                </div>
                <div class="phone-shortcuts">
                    <span>Send</span>
                    <span>Cash In</span>
                    <span>Bills</span>
                    <span>Card</span>
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
            <a class="action-tile" href="wallets.php"><span>Cash in</span><small>Add funds and review wallet balances.</small></a>
            <a class="action-tile" href="transactions.php#send-money"><span>Send money</span><small>Transfer to another PeraHP user.</small></a>
            <a class="action-tile" href="transactions.php#requests"><span>Request payment</span><small>Create a reference for collections.</small></a>
            <a class="action-tile" href="reports.php"><span>Track spending</span><small>Review money movement and reports.</small></a>
        </section>

        <section class="promo-grid">
            <article class="promo-card promo-card-featured">
                <div>
                    <p class="eyebrow">Featured Ad</p>
                    <h2>Get more from every bill payment.</h2>
                    <p>Earn wallet rewards when you settle utilities, subscriptions, and tuition from PeraHP.</p>
                </div>
                <a class="mini-button" href="transactions.php#send-money">Pay now</a>
            </article>
            <article class="promo-card promo-card-dark">
                <p class="eyebrow">New Card</p>
                <h2>Virtual card for online shopping.</h2>
                <p>Keep a separate card balance for safer checkouts.</p>
                <a class="mini-button" href="wallets.php">Set up card</a>
            </article>
            <article class="promo-card promo-card-light">
                <p class="eyebrow">Savings</p>
                <h2>Build a goal wallet.</h2>
                <p>Separate travel, emergency, and school funds without mixing daily spend.</p>
                <a class="mini-button" href="wallets.php">Create goal</a>
            </article>
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
                    <div><p class="eyebrow">Explore</p><h2>More ways to use PeraHP</h2></div>
                    <span class="badge neutral">Home shortcuts</span>
                </div>
                <div class="module-grid home-modules">
                    <a class="module-card" href="transactions.php#send-money"><span>Transfer</span><strong>Send money</strong><small>Move funds to contacts in a few taps.</small></a>
                    <a class="module-card" href="wallets.php"><span>Wallet</span><strong>Cash in</strong><small>See balances and prepare top-ups.</small></a>
                    <a class="module-card" href="transactions.php#requests"><span>Collect</span><strong>Request payment</strong><small>Generate references for incoming money.</small></a>
                    <a class="module-card" href="reports.php"><span>Insights</span><strong>Spending report</strong><small>Understand monthly activity.</small></a>
                </div>
            </article>
        </section>

        <section class="deal-board">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Deals</p>
                    <h2>Ads and promos</h2>
                </div>
                <span class="badge success">Updated today</span>
            </div>
            <div class="deals-grid">
                <article class="deal-card">
                    <span>Shopping</span>
                    <strong>PHP 200 cashback</strong>
                    <small>Use your PeraHP wallet for eligible online stores.</small>
                </article>
                <article class="deal-card">
                    <span>Load</span>
                    <strong>Bonus data bundle</strong>
                    <small>Buy mobile load and get partner data rewards.</small>
                </article>
                <article class="deal-card">
                    <span>Invite</span>
                    <strong>Earn referral points</strong>
                    <small>Share PeraHP with friends and collect wallet perks.</small>
                </article>
            </div>
        </section>

        <section class="security-grid">
            <article class="security-card"><span>Account</span><strong>Verified profile path</strong><small>Finish verification to raise limits and unlock more wallet options.</small></article>
            <article class="security-card"><span>Safety</span><strong>Secure wallet habits</strong><small>Review login alerts and transaction limits in settings.</small></article>
            <article class="security-card"><span>Support</span><strong>Help-ready records</strong><small>Recent activity and reports stay close when you need a reference.</small></article>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script src="script.js"></script>
</body>
</html>
