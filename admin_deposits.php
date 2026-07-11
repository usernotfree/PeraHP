<?php
require_once __DIR__ . "/admin_actions.php";
require_admin();
$user = current_user();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    perahp_handle_admin_deposit_post($user);
    $nextStatus = perahp_admin_deposit_status($_POST["current_status"] ?? "pending");
    header("Location: admin_deposits.php?status=" . urlencode($nextStatus));
    exit;
}

$status = perahp_admin_deposit_status($_GET["status"] ?? "pending");
$flash = perahp_take_flash();
$requests = perahp_admin_deposit_requests($status);
$counts = perahp_admin_deposit_counts();

function perahp_admin_money($amount, $currency = "PHP") {
    return $currency . " " . number_format((float) $amount, 2);
}

function perahp_admin_date($value) {
    $time = strtotime((string) $value);
    return $time ? date("M j, Y g:i A", $time) : "-";
}

function perahp_admin_badge_class($status) {
    return $status === "approved" ? "success" : ($status === "pending" ? "warning" : ($status === "rejected" ? "danger" : "neutral"));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Deposits - PeraHP</title>
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
            <a class="nav-link" href="payment_requests.php">Payment Requests</a>
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link" href="reports.php">Reports</a>
            <a class="nav-link" href="settings.php">Settings</a>
            <a class="nav-link active" href="admin_deposits.php">Admin Deposits</a>
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
                <h1>Deposit Approvals</h1>
                <small style="color:var(--muted);">Review cash-in requests before balances change</small>
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
                <span>Pending</span>
                <strong><?php echo e($counts["pending"]); ?></strong>
                <small>Waiting for admin review</small>
            </div>
            <div class="metric-card">
                <span>Approved</span>
                <strong><?php echo e($counts["approved"]); ?></strong>
                <small>Balance already credited</small>
            </div>
            <div class="metric-card">
                <span>Rejected</span>
                <strong><?php echo e($counts["rejected"]); ?></strong>
                <small>No balance was added</small>
            </div>
            <div class="metric-card">
                <span>Total Requests</span>
                <strong><?php echo e($counts["all"]); ?></strong>
                <small>All deposit requests</small>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Admin Queue</p>
                    <h2>Deposit requests</h2>
                </div>
                <div class="filters">
                    <?php foreach (["pending" => "Pending", "approved" => "Approved", "rejected" => "Rejected", "all" => "All"] as $key => $label): ?>
                        <a class="mini-button <?php echo $status === $key ? "active-filter" : ""; ?>" href="admin_deposits.php?status=<?php echo e($key); ?>">
                            <?php echo e($label); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Proof</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($requests) === 0): ?>
                            <tr><td colspan="7">No deposit requests found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($request["reference_code"]); ?></strong>
                                        <?php if (!empty($request["note"])): ?>
                                            <small style="display:block;color:var(--muted);"><?php echo e($request["note"]); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo e($request["user_name"]); ?></strong>
                                        <small style="display:block;color:var(--muted);"><?php echo e($request["user_email"]); ?></small>
                                    </td>
                                    <td><?php echo e(perahp_admin_money($request["amount"], $request["currency_code"])); ?></td>
                                    <td><?php echo e($request["proof_reference"] ?: "-"); ?></td>
                                    <td><span class="badge <?php echo e(perahp_admin_badge_class($request["status"])); ?>"><?php echo e($request["status"]); ?></span></td>
                                    <td><?php echo e(perahp_admin_date($request["created_at"])); ?></td>
                                    <td>
                                        <?php if ($request["status"] === "pending"): ?>
                                            <div class="admin-actions">
                                                <form method="post" action="admin_deposits.php">
                                                    <input type="hidden" name="action" value="approve_deposit">
                                                    <input type="hidden" name="deposit_id" value="<?php echo e($request["id"]); ?>">
                                                    <input type="hidden" name="current_status" value="<?php echo e($status); ?>">
                                                    <?php echo csrf_input(); ?>
                                                    <button class="mini-button approve-button" type="submit">Approve</button>
                                                </form>
                                                <form method="post" action="admin_deposits.php" class="reject-form">
                                                    <input type="hidden" name="action" value="reject_deposit">
                                                    <input type="hidden" name="deposit_id" value="<?php echo e($request["id"]); ?>">
                                                    <input type="hidden" name="current_status" value="<?php echo e($status); ?>">
                                                    <?php echo csrf_input(); ?>
                                                    <input type="text" name="rejection_reason" placeholder="Reason">
                                                    <button class="mini-button reject-button" type="submit">Reject</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <strong><?php echo e($request["reviewer_name"] ?: "-"); ?></strong>
                                            <small style="display:block;color:var(--muted);"><?php echo e(perahp_admin_date($request["reviewed_at"])); ?></small>
                                            <?php if (!empty($request["rejection_reason"])): ?>
                                                <small style="display:block;color:var(--muted);"><?php echo e($request["rejection_reason"]); ?></small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
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
