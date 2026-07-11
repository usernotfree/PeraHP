<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/wallet_data.php";
require_once __DIR__ . "/wallet_actions.php";
require_login();
$user = current_user();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    perahp_handle_wallet_post($user, ["send_money", "request_payment", "cash_in"]);
    header("Location: wallets.php");
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
            <a class="nav-link" href="payment_requests.php">Payment Requests</a>
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link" href="reports.php">Reports</a>
            <a class="nav-link" href="settings.php">Settings</a>
            <?php if (is_admin()): ?>
                <a class="nav-link" href="admin_deposits.php">Admin Deposits</a>
            <?php endif; ?>
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
                <h1>Wallets & Transfers</h1>
                <small style="color:var(--muted);">Manage your funds and initiate payments</small>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="action-alert <?php echo e($flash["type"] ?? ""); ?>"><?php echo e($flash["message"] ?? ""); ?></div>
        <?php endif; ?>

        <section class="panel" style="margin-bottom: 25px;">
            <div class="panel-heading">
                <div><p class="eyebrow">Wallets</p><h2>Multi-currency balances</h2></div>
                <span class="badge neutral"><?php echo $walletPageData["walletSource"] === "database" ? "Database" : "Demo"; ?> data</span>
            </div>
            <?php if ($walletPageData["walletSource"] === "database" && count($walletPageData["wallets"]) === 0): ?>
                <div class="auth-alert">No active wallets were found for this account yet.</div>
            <?php endif; ?>
            <div class="wallet-grid" id="walletGrid"></div>
        </section>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">Cash In</p>
                        <h2>Request deposit approval</h2>
                    </div>
                    <span class="badge warning">Admin Review</span>
                </div>
                <form id="cashInForm" class="form-stack" method="post" action="wallets.php">
                    <input type="hidden" name="action" value="cash_in">
                    <?php echo csrf_input(); ?>
                    <small style="color:var(--muted);">Your balance changes only after an admin approves this request.</small>
                    <label>Amount
                        <input type="number" id="cashInAmount" name="amount" placeholder="1000.00" min="0.01" step="0.01" required>
                    </label>
                    <label>Currency
                        <select id="cashInCurrency" name="cash_in_currency"></select>
                    </label>
                    <label>Payment reference
                        <input type="text" name="proof_reference" placeholder="Bank slip, GCash ref, or receipt number">
                    </label>
                    <label>Note
                        <input type="text" name="deposit_note" placeholder="Optional note for the admin">
                    </label>
                    <button type="submit" class="primary-button">Submit deposit request</button>
                </form>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">Send Money</p>
                        <h2>Transfer with live conversion</h2>
                    </div>
                    <span class="badge success">Validated Flow</span>
                </div>
                <form id="sendForm" class="form-stack" method="post" action="wallets.php">
                    <input type="hidden" name="action" value="send_money">
                    <?php echo csrf_input(); ?>
                    <label>Recipient Email
                        <input type="email" id="recipientEmail" name="recipient_email" placeholder="juan@perahp.test" required>
                    </label>
                    <label>Amount
                        <input type="number" id="sendAmount" name="amount" placeholder="100.00" min="0.01" step="0.01" required>
                    </label>
                    <div class="form-row two">
                        <label>From <select id="sendFrom" name="send_from"></select></label>
                        <label>To <select id="sendTo" name="send_to"></select></label>
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
                <form id="requestForm" class="form-stack" method="post" action="wallets.php">
                    <input type="hidden" name="action" value="request_payment">
                    <?php echo csrf_input(); ?>
                    <label>Amount
                        <input type="number" id="requestAmount" name="amount" placeholder="2500" min="0.01" step="0.01" required>
                    </label>
                    <label>Currency
                        <select id="requestCurrency" name="request_currency"></select>
                    </label>
                    <label>Payer Email
                        <input type="email" id="payerEmail" name="payer_email" placeholder="client@example.com" required>
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
    <script>
        window.PERAHP_DATA = <?php echo perahp_json($walletPageData); ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>
