<?php
require_once __DIR__ . "/admin_data.php";
require_admin();
$user = current_user();
$adminMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_token_is_valid($_POST["csrf_token"] ?? "")) {
        $adminMessage = "Your session token expired. Please try again.";
    } elseif (($_POST["action"] ?? "") === "complete_transaction") {
        $adminMessage = perahp_admin_complete_transaction($_POST["reference_code"] ?? "", $user);
    }
}

$adminData = perahp_admin_dashboard_data();
$stats = $adminData["stats"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PeraHP</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        a { text-decoration: none; }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="main.php">
            <img class="brand-mark" src="logo.png" width="46" height="46" alt="PeraHP logo">
            <div><strong>PeraHP</strong><small>Admin console</small></div>
        </a>
        <nav class="nav-list">
            <a class="nav-link" href="main.php">Dashboard</a>
            <a class="nav-link" href="wallets.php">Wallets</a>
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link" href="reports.php">Reports</a>
            <a class="nav-link" href="settings.php">Settings</a>
            <a class="nav-link active" href="admin.php">Admin</a>
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
                <h1>Admin Dashboard</h1>
                <small style="color:var(--muted);">
                    <?php echo $adminData["source"] === "database" ? "Monitoring live SQL records" : "Showing demo admin records"; ?>
                </small>
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
                <p class="eyebrow">Operations</p>
                <h2>System control center</h2>
                <p>Review users, wallet activity, transaction movement, and audit events from one administrator view.</p>
            </div>
            <div class="readiness-card">
                <h2 style="font-size:1.1rem;">Admin access</h2>
                <div class="readiness-list">
                    <div><span>Role</span><span><?php echo e($user["role"]); ?></span></div>
                    <div><span>Status</span><span><?php echo e($user["status"]); ?></span></div>
                    <div><span>Data source</span><span><?php echo e(ucfirst($adminData["source"])); ?></span></div>
                </div>
            </div>
        </section>

        <?php if ($adminMessage !== ""): ?>
            <div class="auth-alert" style="margin-bottom:18px;"><?php echo e($adminMessage); ?></div>
        <?php endif; ?>

        <section class="metric-grid">
            <div class="metric-card main-metric">
                <span>Total Users</span>
                <strong><?php echo e($stats["users"]); ?></strong>
                <small>Registered user accounts</small>
            </div>
            <div class="metric-card">
                <span>Wallets</span>
                <strong><?php echo e($stats["wallets"]); ?></strong>
                <small>System wallet records</small>
            </div>
            <div class="metric-card">
                <span>Transactions</span>
                <strong><?php echo e($stats["transactions"]); ?></strong>
                <small>Total transaction records</small>
            </div>
            <div class="metric-card">
                <span>Pending</span>
                <strong><?php echo e($stats["pending"]); ?></strong>
                <small>Transactions awaiting action</small>
            </div>
        </section>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Users</p><h2>User management</h2></div>
                    <span class="badge neutral"><?php echo e(count($adminData["users"])); ?> shown</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($adminData["users"] as $row): ?>
                                <tr>
                                    <td><strong><?php echo e($row["name"]); ?></strong><br><small><?php echo e($row["created_at"]); ?></small></td>
                                    <td><?php echo e($row["email"]); ?></td>
                                    <td><?php echo e($row["role"]); ?></td>
                                    <td><span class="badge <?php echo e(perahp_admin_badge_class($row["status"])); ?>"><?php echo e($row["status"]); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Audit</p><h2>Recent audit events</h2></div>
                    <span class="badge success">Security</span>
                </div>
                <div class="admin-list">
                    <?php foreach ($adminData["auditLogs"] as $log): ?>
                        <div>
                            <strong><?php echo e($log["action"]); ?></strong>
                            <small><?php echo e($log["user"]); ?> - <?php echo e($log["details"]); ?></small>
                            <span class="badge neutral"><?php echo e($log["date"]); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
        </section>

        <article class="panel">
            <div class="panel-heading">
                <div><p class="eyebrow">Ledger</p><h2>Recent system transactions</h2></div>
                <span class="badge warning">Admin review</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($adminData["transactions"] as $transaction): ?>
                            <tr>
                                <td><strong><?php echo e($transaction["reference"]); ?></strong></td>
                                <td><?php echo e($transaction["user"]); ?></td>
                                <td><?php echo e($transaction["type"]); ?></td>
                                <td><?php echo e($transaction["amount"]); ?></td>
                                <td><span class="badge <?php echo e(perahp_admin_badge_class($transaction["status"])); ?>"><?php echo e(ucfirst($transaction["status"])); ?></span></td>
                                <td>
                                    <?php echo e($transaction["date"]); ?>
                                    <?php if (strtolower((string) $transaction["status"]) === "pending"): ?>
                                        <form method="post" action="admin.php" style="margin-top:8px;">
                                            <?php echo csrf_input(); ?>
                                            <input type="hidden" name="action" value="complete_transaction">
                                            <input type="hidden" name="reference_code" value="<?php echo e($transaction["reference"]); ?>">
                                            <button class="mini-button" type="submit">Mark completed</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script src="script.js"></script>
</body>
</html>
