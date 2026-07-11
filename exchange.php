<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/wallet_actions.php";
require_login();
$user = current_user();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    perahp_handle_wallet_post($user, ["exchange_funds"]);
    header("Location: exchange.php");
    exit;
}

$flash = perahp_take_flash();
$walletPageData = perahp_wallet_page_data($user);
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

        <?php if ($flash): ?>
            <div class="action-alert <?php echo e($flash["type"] ?? ""); ?>"><?php echo e($flash["message"] ?? ""); ?></div>
        <?php endif; ?>

        <section class="panel" id="exchange">
            <div class="panel-heading">
                <div><p class="eyebrow">Currency Exchange</p><h2>Convert between wallets</h2></div>
            </div>
            
            <form class="form-stack" id="exchangeForm" method="post" action="exchange.php">
                <input type="hidden" name="action" value="exchange_funds">
                <?php echo csrf_input(); ?>
                <div class="form-row">
                    <label>Amount <input type="number" id="exchangeAmount" name="amount" min="0.01" step="0.01" placeholder="0.00" required></label>
                    <label>From <select id="exchangeFrom" name="exchange_from"></select></label>
                    <label>To <select id="exchangeTo" name="exchange_to"></select></label>
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
                    <?php foreach ($walletPageData["ratesToPhp"] as $code => $rate): ?>
                        <?php if ($code !== "PHP"): ?>
                            <div style="padding: 15px; background: #f3f4f6; border-radius: 8px; font-size: 1rem;"><strong>1 <?php echo e($code); ?> = PHP <?php echo e(number_format((float) $rate, 2)); ?></strong></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script>
        window.PERAHP_DATA = <?php echo perahp_json($walletPageData); ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>
