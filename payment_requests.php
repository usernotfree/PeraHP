<?php
require_once __DIR__ . "/payment_request_actions.php";
require_login();
$user = current_user();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    perahp_handle_payment_request_response_post($user);
    header("Location: payment_requests.php");
    exit;
}

$flash = perahp_take_flash();
$incomingRequests = perahp_payment_requests_for_user($user, "incoming", "all");
$sentRequests = perahp_payment_requests_for_user($user, "sent", "all");
$counts = perahp_payment_request_counts($user);

function perahp_request_money($amount, $currency = "PHP") {
    return $currency . " " . number_format((float) $amount, 2);
}

function perahp_request_date($value) {
    $time = strtotime((string) $value);
    return $time ? date("M j, Y g:i A", $time) : "-";
}

function perahp_request_badge_class($status) {
    return $status === "paid" ? "success" : ($status === "pending" ? "warning" : ($status === "cancelled" ? "danger" : "neutral"));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Requests - PeraHP</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="main.php">
            <img class="brand-mark" src="logo.png" width="46" height="46" alt="PeraHP logo">
            <div><strong>PeraHP</strong><small>Digital wallet</small></div>
        </a>
        <nav class="nav-list">
            <a class="nav-link" href="main.php">Dashboard</a>
            <a class="nav-link" href="wallets.php">Wallets</a>
            <a class="nav-link active" href="payment_requests.php">Payment Requests</a>
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
                <div><strong><?php echo e($user["name"]); ?></strong><small><?php echo e($user["email"]); ?></small></div>
            </a>
            <a class="mini-button logout-link" href="logout.php" style="margin-left:auto;">Logout</a>
        </div>
    </aside>

    <div class="page">
        <header class="topbar">
            <div>
                <h1>Payment Requests</h1>
                <small style="color:var(--muted);">Pay requests sent to you or track requests you created</small>
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

        <section class="metric-grid">
            <div class="metric-card">
                <span>To Pay</span>
                <strong><?php echo e($counts["incoming_pending"]); ?></strong>
                <small>Incoming pending requests</small>
            </div>
            <div class="metric-card">
                <span>Waiting</span>
                <strong><?php echo e($counts["sent_pending"]); ?></strong>
                <small>Requests you sent</small>
            </div>
            <div class="metric-card">
                <span>Paid</span>
                <strong><?php echo e($counts["paid"]); ?></strong>
                <small>Completed request records</small>
            </div>
            <div class="metric-card">
                <span>Declined</span>
                <strong><?php echo e($counts["cancelled"]); ?></strong>
                <small>Cancelled request records</small>
            </div>
        </section>

        <section class="panel" style="margin-bottom: 25px;">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Incoming</p>
                    <h2>Requests sent to you</h2>
                </div>
                <a class="mini-button" href="wallets.php">Create request</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Requester</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($incomingRequests) === 0): ?>
                            <tr><td colspan="6">No payment requests were sent to you yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($incomingRequests as $request): ?>
                                <tr>
                                    <td><strong><?php echo e($request["reference_code"]); ?></strong></td>
                                    <td>
                                        <strong><?php echo e($request["requester_name"]); ?></strong>
                                        <small style="display:block;color:var(--muted);"><?php echo e($request["requester_email"]); ?></small>
                                    </td>
                                    <td><?php echo e(perahp_request_money($request["amount"], $request["currency_code"])); ?></td>
                                    <td><span class="badge <?php echo e(perahp_request_badge_class($request["status"])); ?>"><?php echo e($request["status"]); ?></span></td>
                                    <td><?php echo e(perahp_request_date($request["created_at"])); ?></td>
                                    <td>
                                        <?php if ($request["status"] === "pending"): ?>
                                            <div class="request-actions">
                                                <form method="post" action="payment_requests.php">
                                                    <input type="hidden" name="action" value="pay_payment_request">
                                                    <input type="hidden" name="request_id" value="<?php echo e($request["id"]); ?>">
                                                    <?php echo csrf_input(); ?>
                                                    <button class="mini-button approve-button" type="submit">Pay</button>
                                                </form>
                                                <form method="post" action="payment_requests.php">
                                                    <input type="hidden" name="action" value="decline_payment_request">
                                                    <input type="hidden" name="request_id" value="<?php echo e($request["id"]); ?>">
                                                    <?php echo csrf_input(); ?>
                                                    <button class="mini-button reject-button" type="submit">Decline</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <small style="color:var(--muted);">Reviewed <?php echo e(perahp_request_date($request["paid_at"])); ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Sent</p>
                    <h2>Requests you created</h2>
                </div>
                <span class="badge neutral"><?php echo e(count($sentRequests)); ?> rows</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Payer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($sentRequests) === 0): ?>
                            <tr><td colspan="6">You have not created payment requests yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($sentRequests as $request): ?>
                                <tr>
                                    <td><strong><?php echo e($request["reference_code"]); ?></strong></td>
                                    <td>
                                        <strong><?php echo e($request["payer_name"] ?: "Pending payer"); ?></strong>
                                        <small style="display:block;color:var(--muted);"><?php echo e($request["payer_account_email"] ?: $request["payer_email"]); ?></small>
                                    </td>
                                    <td><?php echo e(perahp_request_money($request["amount"], $request["currency_code"])); ?></td>
                                    <td><span class="badge <?php echo e(perahp_request_badge_class($request["status"])); ?>"><?php echo e($request["status"]); ?></span></td>
                                    <td><?php echo e(perahp_request_date($request["created_at"])); ?></td>
                                    <td><?php echo e(perahp_request_date($request["paid_at"])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script src="script.js"></script>
</body>
</html>
