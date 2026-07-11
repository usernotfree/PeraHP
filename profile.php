<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/account_actions.php";
require_login();
$user = current_user();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    perahp_handle_account_post($user, ["update_profile"]);
    header("Location: profile.php");
    exit;
}

$flash = perahp_account_take_flash();
$user = current_user();
$initials = strtoupper(substr(trim($user["name"]), 0, 1));
$walletPageData = perahp_wallet_page_data($user);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="main.php">
            <img class="brand-mark" src="logo.png" width="46" height="46" alt="PeraHP logo">
            <div>
                <strong>PeraHP</strong>
                <small>Digital wallet</small>
            </div>
        </a>
        <nav class="nav-list">
            <a class="nav-link" href="main.php">Home</a>
            <a class="nav-link" href="wallets.php">Wallets</a>
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link" href="reports.php">Reports</a>
            <a class="nav-link" href="settings.php">Settings</a>
        </nav>
        <div class="auth-box">
            <a class="profile-link active" href="profile.php" aria-label="Open profile">
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
                <h1>Profile</h1>
                <small style="color:var(--muted);">Review your account identity and personal details</small>
            </div>
            <div class="top-actions">
                <button class="icon-button" id="menuButton" aria-label="Open menu">
                    <span></span><span></span><span></span>
                </button>
                <button class="ghost-button" id="printButton">Print</button>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="action-alert <?php echo e($flash["type"] ?? ""); ?>"><?php echo e($flash["message"] ?? ""); ?></div>
        <?php endif; ?>

        <section class="overview-band profile-overview">
            <div class="overview-copy">
                <p class="eyebrow">Account Profile</p>
                <h2><?php echo e($user["name"]); ?></h2>
                <p>Your profile keeps the account owner, contact details, verification state, and account access information in one place.</p>
            </div>
            <div class="readiness-card profile-card">
                <div class="profile-avatar"><?php echo e($initials); ?></div>
                <h2 style="font-size:1.1rem;"><?php echo e($user["role"]); ?></h2>
                <div class="readiness-list">
                    <div><span>Status</span><span><?php echo e($user["status"]); ?></span></div>
                    <div><span>Member since</span><span><?php echo e($user["member_since"]); ?></span></div>
                    <div><span>Primary wallet</span><span>PHP</span></div>
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
                <span>Profile Status</span>
                <strong>Complete</strong>
                <small>Basic details are ready</small>
            </div>
            <div class="metric-card">
                <span>Verification</span>
                <strong>Pending</strong>
                <small>KYC review in progress</small>
            </div>
            <div class="metric-card">
                <span>Security</span>
                <strong>2FA Ready</strong>
                <small>Configured in settings</small>
            </div>
        </section>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Profile Details</p><h2>Personal information</h2></div>
                    <span class="badge success"><?php echo e($user["status"]); ?></span>
                </div>
                <form class="form-stack" id="profileForm" method="post" action="profile.php">
                    <input type="hidden" name="action" value="update_profile">
                    <?php echo csrf_input(); ?>
                    <label>Full name<input type="text" name="full_name" value="<?php echo e($user["name"]); ?>" required></label>
                    <label>Email<input type="email" value="<?php echo e($user["email"]); ?>" readonly></label>
                    <div class="form-row two">
                        <label>Phone<input type="text" name="phone" value="<?php echo e($user["phone"]); ?>"></label>
                        <label>City / Province<input type="text" name="address" value="<?php echo e($user["address"]); ?>"></label>
                    </div>
                    <button class="primary-button" type="submit">Save profile</button>
                </form>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Account Identity</p><h2>Current account record</h2></div>
                    <span class="badge neutral"><?php echo e($user["role"]); ?></span>
                </div>
                <div class="settings-list">
                    <div><span>Primary email</span><strong><?php echo e($user["email"]); ?></strong></div>
                    <div><span>Mobile number</span><strong><?php echo e($user["phone"]); ?></strong></div>
                    <div><span>Registered location</span><strong><?php echo e($user["address"]); ?></strong></div>
                    <div><span>Account type</span><strong>Personal wallet</strong></div>
                </div>
            </article>
        </section>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Verification</p><h2>KYC and account readiness</h2></div>
                    <span class="badge warning">Pending</span>
                </div>
                <div class="settings-list">
                    <div><span>Government ID</span><strong>Submitted</strong></div>
                    <div><span>Address proof</span><strong>For review</strong></div>
                    <div><span>Daily send limit</span><strong>PHP 50,000.00</strong></div>
                    <div><span>Single transaction limit</span><strong>PHP 25,000.00</strong></div>
                </div>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Profile Actions</p><h2>Related pages</h2></div>
                    <span class="badge neutral">Shortcuts</span>
                </div>
                <div class="settings-list">
                    <div><span>Security settings</span><a class="mini-button" href="settings.php">Open settings</a></div>
                    <div><span>Wallet balances</span><a class="mini-button" href="wallets.php">Open wallets</a></div>
                    <div><span>Transaction ledger</span><a class="mini-button" href="transactions.php">Open transactions</a></div>
                    <div><span>Financial reports</span><a class="mini-button" href="reports.php">Open reports</a></div>
                </div>
            </article>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script>
        window.PERAHP_DATA = <?php echo perahp_json($walletPageData); ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>
