<?php
require_once __DIR__ . "/auth.php";
require_login();
$user = current_user();
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
            <span class="brand-mark">PHP</span>
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
                <small style="color:var(--muted);">Analytics, spending trends, and history</small>
            </div>
            <div class="top-actions">
                <button class="ghost-button" onclick="window.print()">🖨 Print Report</button>
            </div>
        </header>

        <section class="metric-grid">
            <div class="metric-card"><span>Total Inflow</span><strong id="totalIn">PHP 74,930.00</strong></div>
            <div class="metric-card"><span>Total Outflow</span><strong id="totalOut">PHP 5,800.00</strong></div>
            <div class="metric-card"><span>Net Flow</span><strong id="netFlow">PHP 69,130.00</strong></div>
        </section>

        <article class="panel" style="margin-bottom:20px;">
            <div class="panel-heading"><h2>Financial Trend</h2></div>
            <div style="height: 250px;">
                <canvas id="reportChart"></canvas>
            </div>
        </article>

        <article class="panel">
            <div class="panel-heading"><h2>Transaction Ledger</h2></div>
            <table style="width:100%; border-collapse:collapse; margin-top:10px;">
                <thead>
                    <tr style="text-align:left; color:var(--muted); font-size:0.85rem;">
                        <th style="padding:10px;">Reference</th>
                        <th style="padding:10px;">Type</th>
                        <th style="padding:10px;">User</th>
                        <th style="padding:10px;">Amount</th>
                        <th style="padding:10px;">Status</th>
                    </tr>
                </thead>
                <tbody id="reportTableBody">
                    <tr style="border-top:1px solid var(--line);">
                        <td style="padding:12px;">RCV-260701-214</td>
                        <td style="padding:12px;">Receive</td>
                        <td style="padding:12px;">Client Example</td>
                        <td style="padding:12px;">PHP 74,930.00</td>
                        <td style="padding:12px;"><span class="badge success">Completed</span></td>
                    </tr>
                    <tr style="border-top:1px solid var(--line);">
                        <td style="padding:12px;">SEND-260630-A91</td>
                        <td style="padding:12px;">Send</td>
                        <td style="padding:12px;">Juan Dela Cruz</td>
                        <td style="padding:12px;">USD 100.00</td>
                        <td style="padding:12px;"><span class="badge success">Completed</span></td>
                    </tr>
                    <tr style="border-top:1px solid var(--line);">
                        <td style="padding:12px;">REQ-260629-K02</td>
                        <td style="padding:12px;">Request</td>
                        <td style="padding:12px;">Client Example</td>
                        <td style="padding:12px;">PHP 2,500.00</td>
                        <td style="padding:12px;"><span class="badge warning">Pending</span></td>
                    </tr>
                    <tr style="border-top:1px solid var(--line);">
                        <td style="padding:12px;">EXCH-260628-V19</td>
                        <td style="padding:12px;">Exchange</td>
                        <td style="padding:12px;">Maria Santos</td>
                        <td style="padding:12px;">EUR 50.00</td>
                        <td style="padding:12px;"><span class="badge success">Completed</span></td>
                    </tr>
                    <tr style="border-top:1px solid var(--line);">
                        <td style="padding:12px;">SEND-260627-R77</td>
                        <td style="padding:12px;">Send</td>
                        <td style="padding:12px;">Online Store</td>
                        <td style="padding:12px;">SGD 40.00</td>
                        <td style="padding:12px;"><span class="badge danger" style="background:#e74c3c;">Failed</span></td>
                    </tr>
                </tbody>
            </table>
        </article>
    </div>

    <script>
        const ctx = document.getElementById('reportChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Inflow',
                    data: [12000, 19000, 3000, 5000, 20000, 74930],
                    borderColor: '#2ecc71',
                    tension: 0.3
                }, {
                    label: 'Outflow',
                    data: [1000, 2000, 5000, 1000, 3000, 5800],
                    borderColor: '#e74c3c',
                    tension: 0.3
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
    <script src="script.js"></script>
</body>
</html>
