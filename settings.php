<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/account_actions.php";
require_login();
$user = current_user();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    perahp_handle_account_post($user, ["update_preferences", "change_password"]);
    header("Location: settings.php");
    exit;
}

$flash = perahp_account_take_flash();
$settings = perahp_user_settings((int) ($user["id"] ?? 0));
$rates = perahp_exchange_rates();
$currencies = perahp_available_currencies($rates);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - PeraHP</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="main.php"><span class="brand-mark">PHP</span><div><strong>PeraHP</strong><small>Digital wallet</small></div></a>
        <nav class="nav-list">
            <a class="nav-link" href="main.php">Dashboard</a>
            <a class="nav-link" href="wallets.php">Wallets</a>
            <a class="nav-link" href="payment_requests.php">Payment Requests</a>
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link" href="reports.php">Reports</a>
            <a class="nav-link active" href="settings.php">Settings</a>
            <?php if (is_admin()): ?>
                <a class="nav-link" href="admin_deposits.php">Admin Deposits</a>
            <?php endif; ?>
        </nav>
        <div class="auth-box">
            <a class="profile-link" href="profile.php" aria-label="Open profile">
                <span class="status-dot"></span>
                <div><strong><?php echo e($user["name"]); ?></strong><small><?php echo e($user["email"]); ?></small></div>
            </a>
            <a class="mini-button logout-link" href="logout.php" style="margin-left:auto;">Logout</a>
        </div>
    </aside>

    <div class="page">
        <header class="topbar">
            <div><h1>Account Settings</h1><small style="color:var(--muted);">Manage your profile and security</small></div>
        </header>

        <?php if ($flash): ?>
            <div class="action-alert <?php echo e($flash["type"] ?? ""); ?>"><?php echo e($flash["message"] ?? ""); ?></div>
        <?php endif; ?>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Preferences</p><h2>Wallet defaults</h2></div>
                    <span class="badge neutral">SQL saved</span>
                </div>
                <form class="form-stack" method="post" action="settings.php">
                    <input type="hidden" name="action" value="update_preferences">
                    <?php echo csrf_input(); ?>
                    <label>Default Currency
                        <select name="default_currency">
                            <?php foreach ($currencies as $currency): ?>
                                <option value="<?php echo e($currency["code"]); ?>" <?php echo $settings["default_currency"] === $currency["code"] ? "selected" : ""; ?>>
                                    <?php echo e($currency["code"] . " - " . $currency["name"]); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <button class="primary-button" type="submit">Save preferences</button>
                </form>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Security</p><h2>Change password</h2></div>
                    <span class="badge warning">Protected</span>
                </div>
                <form class="form-stack" method="post" action="settings.php">
                    <input type="hidden" name="action" value="change_password">
                    <?php echo csrf_input(); ?>
                    <label>Current password<input type="password" name="current_password" autocomplete="current-password" required></label>
                    <div class="form-row two">
                        <label>New password<input type="password" name="new_password" autocomplete="new-password" required></label>
                        <label>Confirm password<input type="password" name="confirm_password" autocomplete="new-password" required></label>
                    </div>
                    <button class="secondary-button" type="submit">Update password</button>
                </form>
            </article>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div><p class="eyebrow">Profile</p><h2>Personal details live on your profile page</h2></div>
                <a class="mini-button" href="profile.php">Open profile</a>
            </div>
            <div class="settings-list">
                <div><span>Name</span><strong><?php echo e($user["name"]); ?></strong></div>
                <div><span>Email</span><strong><?php echo e($user["email"]); ?></strong></div>
                <div><span>Phone</span><strong><?php echo e($user["phone"] ?: "Not set"); ?></strong></div>
                <div><span>Location</span><strong><?php echo e($user["address"] ?: "Not set"); ?></strong></div>
            </div>
        </section>
    </div>
    <script src="script.js"></script>
</body>
</html>
