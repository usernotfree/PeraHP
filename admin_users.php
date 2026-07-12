<?php
require_once __DIR__ . "/admin_data.php";
require_admin();
$user = current_user();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    perahp_handle_admin_user_post($user);
    $query = http_build_query([
        "status" => $_POST["current_status"] ?? "all",
        "role" => $_POST["current_role"] ?? "all",
        "search" => $_POST["current_search"] ?? ""
    ]);
    header("Location: admin_users.php" . ($query ? "?" . $query : ""));
    exit;
}

$status = perahp_admin_user_status_filter($_GET["status"] ?? "all");
$role = perahp_admin_user_role_filter($_GET["role"] ?? "all");
$search = trim((string) ($_GET["search"] ?? ""));
$flash = perahp_admin_take_flash();
$users = perahp_admin_users($status, $role, $search);
$counts = perahp_admin_user_counts();
$currentUserId = (int) ($user["id"] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - PeraHP</title>
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
            <a class="nav-link active" href="admin_users.php">Admin Users</a>
            <a class="nav-link" href="admin_audit.php">Audit Logs</a>
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
                <h1>User Management</h1>
                <small style="color:var(--muted);">Review accounts, roles, wallet records, and account status</small>
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
                <span>Total Users</span>
                <strong><?php echo e($counts["total"]); ?></strong>
                <small>All registered accounts</small>
            </div>
            <div class="metric-card">
                <span>Active</span>
                <strong><?php echo e($counts["active"]); ?></strong>
                <small>Can log in and transact</small>
            </div>
            <div class="metric-card">
                <span>Suspended</span>
                <strong><?php echo e($counts["suspended"]); ?></strong>
                <small>Blocked from login</small>
            </div>
            <div class="metric-card">
                <span>Admins</span>
                <strong><?php echo e($counts["admin"]); ?></strong>
                <small>Role protected users</small>
            </div>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Accounts</p>
                    <h2>Users</h2>
                </div>
                <form class="filters" method="get" action="admin_users.php">
                    <input type="search" name="search" value="<?php echo e($search); ?>" placeholder="Search name or email">
                    <select name="status">
                        <?php foreach (["all" => "All statuses", "active" => "Active", "pending" => "Pending", "suspended" => "Suspended"] as $key => $label): ?>
                            <option value="<?php echo e($key); ?>" <?php echo $status === $key ? "selected" : ""; ?>><?php echo e($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="role">
                        <?php foreach (["all" => "All roles", "user" => "Wallet owners", "admin" => "Admins"] as $key => $label): ?>
                            <option value="<?php echo e($key); ?>" <?php echo $role === $key ? "selected" : ""; ?>><?php echo e($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="mini-button" type="submit">Filter</button>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Wallets</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) === 0): ?>
                            <tr><td colspan="6">No users found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $row): ?>
                                <?php
                                    $rowId = (int) $row["id"];
                                    $isSelf = $currentUserId > 0 && $rowId === $currentUserId;
                                    $rowRoleLabel = $row["role"] === "admin" ? "Administrator" : "Wallet owner";
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo e($row["full_name"]); ?></strong>
                                        <small style="display:block;color:var(--muted);"><?php echo e($row["email"]); ?></small>
                                        <?php if (!empty($row["phone"]) || !empty($row["address"])): ?>
                                            <small style="display:block;color:var(--muted);"><?php echo e(trim(($row["phone"] ?: "") . " " . ($row["address"] ?: ""))); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge neutral"><?php echo e($rowRoleLabel); ?></span></td>
                                    <td><span class="badge <?php echo e(perahp_admin_badge_class($row["status"])); ?>"><?php echo e(ucfirst($row["status"])); ?></span></td>
                                    <td>
                                        <strong><?php echo e($row["wallet_count"]); ?> wallets</strong>
                                        <small style="display:block;color:var(--muted);"><?php echo e($row["wallet_summary"] ?: "No wallets yet"); ?></small>
                                    </td>
                                    <td><?php echo e(perahp_admin_date_time($row["created_at"])); ?></td>
                                    <td>
                                        <?php if ($isSelf): ?>
                                            <span class="badge warning">Current admin</span>
                                        <?php else: ?>
                                            <div class="admin-actions">
                                                <?php if ($row["status"] === "active"): ?>
                                                    <form method="post" action="admin_users.php">
                                                        <?php echo csrf_input(); ?>
                                                        <input type="hidden" name="action" value="suspend_user">
                                                        <input type="hidden" name="user_id" value="<?php echo e($rowId); ?>">
                                                        <input type="hidden" name="current_status" value="<?php echo e($status); ?>">
                                                        <input type="hidden" name="current_role" value="<?php echo e($role); ?>">
                                                        <input type="hidden" name="current_search" value="<?php echo e($search); ?>">
                                                        <button class="mini-button reject-button" type="submit">Suspend</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" action="admin_users.php">
                                                        <?php echo csrf_input(); ?>
                                                        <input type="hidden" name="action" value="activate_user">
                                                        <input type="hidden" name="user_id" value="<?php echo e($rowId); ?>">
                                                        <input type="hidden" name="current_status" value="<?php echo e($status); ?>">
                                                        <input type="hidden" name="current_role" value="<?php echo e($role); ?>">
                                                        <input type="hidden" name="current_search" value="<?php echo e($search); ?>">
                                                        <button class="mini-button approve-button" type="submit">Activate</button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if ($row["role"] === "admin"): ?>
                                                    <form method="post" action="admin_users.php">
                                                        <?php echo csrf_input(); ?>
                                                        <input type="hidden" name="action" value="make_user">
                                                        <input type="hidden" name="user_id" value="<?php echo e($rowId); ?>">
                                                        <input type="hidden" name="current_status" value="<?php echo e($status); ?>">
                                                        <input type="hidden" name="current_role" value="<?php echo e($role); ?>">
                                                        <input type="hidden" name="current_search" value="<?php echo e($search); ?>">
                                                        <button class="mini-button" type="submit">Make owner</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" action="admin_users.php">
                                                        <?php echo csrf_input(); ?>
                                                        <input type="hidden" name="action" value="make_admin">
                                                        <input type="hidden" name="user_id" value="<?php echo e($rowId); ?>">
                                                        <input type="hidden" name="current_status" value="<?php echo e($status); ?>">
                                                        <input type="hidden" name="current_role" value="<?php echo e($role); ?>">
                                                        <input type="hidden" name="current_search" value="<?php echo e($search); ?>">
                                                        <button class="mini-button approve-button" type="submit">Make admin</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
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
