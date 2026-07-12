<?php
require_once __DIR__ . "/admin_data.php";
require_admin();
$user = current_user();
$action = perahp_admin_audit_action_filter($_GET["action"] ?? "all");
$search = trim((string) ($_GET["search"] ?? ""));
$logs = perahp_admin_audit_logs($action, $search);
$actions = perahp_admin_audit_actions();
$counts = perahp_admin_audit_counts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - PeraHP</title>
    <link rel="stylesheet" href="styles.css">
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
            <a class="nav-link" href="payment_requests.php">Payment Requests</a>
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link" href="reports.php">Reports</a>
            <a class="nav-link" href="settings.php">Settings</a>
            <a class="nav-link" href="admin.php">Admin</a>
            <a class="nav-link" href="admin_deposits.php">Admin Deposits</a>
            <a class="nav-link" href="admin_users.php">Admin Users</a>
            <a class="nav-link active" href="admin_audit.php">Audit Logs</a>
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
                <h1>Audit Logs</h1>
                <small style="color:var(--muted);">Review admin, wallet, account, and security events</small>
            </div>
            <div class="top-actions">
                <button class="icon-button" id="menuButton" aria-label="Open menu">
                    <span></span><span></span><span></span>
                </button>
                <button class="ghost-button" id="printButton">Print</button>
            </div>
        </header>

        <section class="metric-grid">
            <div class="metric-card">
                <span>Total Events</span>
                <strong><?php echo e($counts["total"]); ?></strong>
                <small>All audit records</small>
            </div>
            <div class="metric-card">
                <span>Today</span>
                <strong><?php echo e($counts["today"]); ?></strong>
                <small>Events created today</small>
            </div>
            <div class="metric-card">
                <span>Admin Actions</span>
                <strong><?php echo e($counts["admin"]); ?></strong>
                <small>Role protected activity</small>
            </div>
            <div class="metric-card">
                <span>Wallet Events</span>
                <strong><?php echo e($counts["wallet"]); ?></strong>
                <small>Money movement activity</small>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Security Trail</p>
                    <h2>System audit log</h2>
                </div>
                <form class="filters" method="get" action="admin_audit.php">
                    <input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Search logs">
                    <select name="action">
                        <option value="all">All actions</option>
                        <?php foreach ($actions as $rowAction): ?>
                            <option value="<?php echo e($rowAction); ?>" <?php echo $action === $rowAction ? "selected" : ""; ?>>
                                <?php echo e($rowAction); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="mini-button" type="submit">Filter</button>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Actor</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>Details</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($logs) === 0): ?>
                            <tr><td colspan="6">No audit records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo e(perahp_admin_date_time($log["created_at"])); ?></td>
                                    <td>
                                        <strong><?php echo e($log["user_name"]); ?></strong>
                                        <small style="display:block;color:var(--muted);"><?php echo e($log["user_email"] ?: "System event"); ?></small>
                                    </td>
                                    <td><span class="badge neutral"><?php echo e($log["action"]); ?></span></td>
                                    <td>
                                        <?php echo e($log["entity_type"] ?: "-"); ?>
                                        <?php if (!empty($log["entity_id"])): ?>
                                            <small style="display:block;color:var(--muted);">ID <?php echo e($log["entity_id"]); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?php echo e(perahp_admin_audit_details($log["details"])); ?></small></td>
                                    <td><?php echo e($log["ip_address"] ?: "-"); ?></td>
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
