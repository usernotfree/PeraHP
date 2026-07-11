<?php
require_once __DIR__ . "/transaction_data.php";
require_login();
$user = current_user();
$transactionPageData = perahp_transaction_page_data($user);
$monthlyReport = $transactionPageData["monthlyReport"];
$transactions = $transactionPageData["transactions"];
$totalInflow = array_reduce($monthlyReport, function($sum, $row) {
    return $sum + (float) ($row["received"] ?? 0);
}, 0);
$totalOutflow = array_reduce($monthlyReport, function($sum, $row) {
    return $sum + (float) ($row["sent"] ?? 0);
}, 0);
$netFlow = $totalInflow - $totalOutflow;

function perahp_report_money($amount, $currency = "PHP") {
    return $currency . " " . number_format((float) $amount, 2);
}

function perahp_report_status_class($status) {
    return $status === "completed" ? "success" : ($status === "pending" ? "warning" : ($status === "failed" ? "danger" : "neutral"));
}

function perahp_report_status_label($status) {
    $status = (string) $status;
    return $status === "" ? "Unknown" : ucfirst($status);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Reports - PeraHP</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        a { text-decoration: none; }
    </style>
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
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link active" href="reports.php">Reports</a>
            <a class="nav-link" href="settings.php">Settings</a>
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
                <h1>Financial Reports</h1>
                <small style="color:var(--muted);">
                    <?php echo $transactionPageData["transactionSource"] === "database" ? "Loaded from SQL transaction records" : "Showing demo report records"; ?>
                </small>
            </div>
            <div class="top-actions">
                <button class="icon-button" id="menuButton" aria-label="Open menu">
                    <span></span><span></span><span></span>
                </button>
                <button class="ghost-button" id="printButton">Print Report</button>
            </div>
        </header>

        <section class="metric-grid">
            <div class="metric-card">
                <span>Total Inflow</span>
                <strong id="totalIn"><?php echo e(perahp_report_money($totalInflow)); ?></strong>
                <small>Completed money received in the report period</small>
            </div>
            <div class="metric-card">
                <span>Total Outflow</span>
                <strong id="totalOut"><?php echo e(perahp_report_money($totalOutflow)); ?></strong>
                <small>Completed money sent in the report period</small>
            </div>
            <div class="metric-card">
                <span>Net Flow</span>
                <strong id="netFlow"><?php echo e(perahp_report_money($netFlow)); ?></strong>
                <small>Inflow minus outflow</small>
            </div>
        </section>

        <article class="panel" style="margin-bottom:20px;">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">SQL Report</p>
                    <h2>Financial trend</h2>
                </div>
                <span class="badge neutral">Last 6 months</span>
            </div>
            <div style="height: 250px;">
                <canvas id="reportChartCanvas"></canvas>
            </div>
        </article>

        <article class="panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Ledger</p>
                    <h2>Transaction report</h2>
                </div>
                <span class="badge success"><?php echo e(count($transactions)); ?> rows</span>
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
                    <tbody id="reportTableBody">
                        <?php if (count($transactions) === 0): ?>
                            <tr><td colspan="6">No report transactions yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><strong><?php echo e($transaction["ref"]); ?></strong></td>
                                    <td><?php echo e($transaction["type"]); ?></td>
                                    <td><?php echo e($transaction["user"]); ?></td>
                                    <td><?php echo e(perahp_report_money($transaction["amount"], $transaction["currency"])); ?></td>
                                    <td><span class="badge <?php echo e(perahp_report_status_class($transaction["status"])); ?>"><?php echo e(perahp_report_status_label($transaction["status"])); ?></span></td>
                                    <td><?php echo e($transaction["date"]); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script>
        window.PERAHP_DATA = <?php echo perahp_json($transactionPageData); ?>;
        const reportData = {
            labels: <?php echo perahp_json(array_column($monthlyReport, "month")); ?>,
            inflow: <?php echo perahp_json(array_map(function($row) { return (float) ($row["received"] ?? 0); }, $monthlyReport)); ?>,
            outflow: <?php echo perahp_json(array_map(function($row) { return (float) ($row["sent"] ?? 0); }, $monthlyReport)); ?>
        };
        const reportCanvas = document.getElementById("reportChartCanvas");

        if (reportCanvas && window.Chart) {
            new Chart(reportCanvas.getContext("2d"), {
                type: "line",
                data: {
                    labels: reportData.labels,
                    datasets: [{
                        label: "Inflow",
                        data: reportData.inflow,
                        borderColor: "#2ecc71",
                        backgroundColor: "rgba(46, 204, 113, 0.12)",
                        tension: 0.3
                    }, {
                        label: "Outflow",
                        data: reportData.outflow,
                        borderColor: "#e74c3c",
                        backgroundColor: "rgba(231, 76, 60, 0.12)",
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { boxWidth: 12 } }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }
    </script>
    <script src="script.js"></script>
</body>
</html>
