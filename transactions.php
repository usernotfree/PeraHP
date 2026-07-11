<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/transaction_data.php";
require_login();
$user = current_user();
$transactionPageData = perahp_transaction_page_data($user);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - PeraHP</title>
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
            <a class="nav-link" href="main.php">Dashboard</a>
            <a class="nav-link" href="wallets.php">Wallets</a>
            <a class="nav-link active" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link" href="reports.php">Reports</a>
            <a class="nav-link" href="settings.php">Settings</a>
        </nav>
        <div class="auth-box">
            <span class="status-dot"></span>
            <div>
                <strong><?php echo e($user["name"]); ?></strong>
                <small><?php echo e($user["email"]); ?></small>
            </div>
            <a class="mini-button logout-link" href="logout.php" style="margin-left:auto;">Logout</a>
        </div>
    </aside>

    <div class="page">
        <header class="topbar">
            <div>
                <h1>Transactions</h1>
                <small style="color:var(--muted);">View your complete ledger</small>
            </div>
            <div class="top-actions">
                <button class="icon-button" id="menuButton">
                    <span></span><span></span><span></span>
                </button>
                <button class="ghost-button" id="printButton">🖨 Print</button>
            </div>
        </header>

        <section class="panel" id="history">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Transaction History</p>
                    <h2>Searchable and filterable ledger</h2>
                    <small style="color:var(--muted);"><?php echo $transactionPageData["transactionSource"] === "database" ? "Loaded from SQL records" : "Showing demo records"; ?></small>
                </div>
                <div class="filters">
                    <input type="search" id="searchInput" placeholder="Search reference or user">
                    <select id="statusFilter">
                        <option value="all">All statuses</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Type</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="transactionTable"></tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script>
        window.PERAHP_DATA = <?php echo perahp_json($transactionPageData); ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>
