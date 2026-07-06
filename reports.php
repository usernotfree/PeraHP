<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Reports</title>
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
            <a class="nav-link" href="main.php">Home</a>
            <a class="nav-link" href="wallets.php">Wallets</a>
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link active" href="reports.php">Reports</a>
            <a class="nav-link" href="settings.php">Settings</a>
        </nav>
        <div class="auth-box">
            <a class="profile-link" href="profile.php" aria-label="Open profile">
                <span class="status-dot"></span>
                <div>
                    <strong>Maria Santos</strong>
                    <small>maria@perahp.test</small>
                </div>
            </a>
            <button class="mini-button" id="logoutButton" style="margin-left:auto;">Logout</button>
        </div>
    </aside>

    <div class="page">
        <header class="topbar">
            <div>
                <h1>Reports</h1>
                <small style="color:var(--muted);">Monthly summaries and printable financial views</small>
            </div>
            <div class="top-actions">
                <button class="icon-button" id="menuButton" aria-label="Open menu">
                    <span></span><span></span><span></span>
                </button>
                <button class="ghost-button" id="printButton">Print</button>
            </div>
        </header>

        <section class="metric-grid">
            <div class="metric-card main-metric">
                <span>Total Balance</span>
                <strong id="totalBalance">PHP 0.00</strong>
                <small>All wallets combined</small>
            </div>
            <div class="metric-card">
                <span>Received (Jun)</span>
                <strong id="monthlyReceived">PHP 0.00</strong>
                <small>This month</small>
            </div>
            <div class="metric-card">
                <span>Sent (Jun)</span>
                <strong id="monthlySent">PHP 0.00</strong>
                <small>This month</small>
            </div>
            <div class="metric-card">
                <span>Pending</span>
                <strong id="pendingCount">0</strong>
                <small>Requests awaiting action</small>
            </div>
        </section>

        <section class="grid two-columns">
            <article class="panel" id="reports">
                <div class="panel-heading">
                    <div><p class="eyebrow">Financial Reports</p><h2>Monthly summary chart</h2></div>
                    <span class="badge success">Printable</span>
                </div>
                <div class="chart-legend"><span class="legend-dot received"></span>Received <span class="legend-dot sent"></span>Sent</div>
                <div class="chart" id="reportChart"></div>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Report Settings</p><h2>Current view</h2></div>
                    <span class="badge neutral">Mock filters</span>
                </div>
                <form class="form-stack">
                    <div class="form-row two">
                        <label>Period
                            <select>
                                <option>January to June 2026</option>
                                <option>Q2 2026</option>
                                <option>June 2026</option>
                            </select>
                        </label>
                        <label>Currency
                            <select>
                                <option>PHP base value</option>
                                <option>Original wallet currency</option>
                            </select>
                        </label>
                    </div>
                    <button class="secondary-button" type="button" data-settings-action="report">Apply view</button>
                </form>
                <div class="settings-list report-summary">
                    <div><span>Highest received month</span><strong>June</strong></div>
                    <div><span>Highest sent month</span><strong>May</strong></div>
                    <div><span>Prepared for export</span><strong>PDF and CSV</strong></div>
                </div>
            </article>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div><p class="eyebrow">Transactions</p><h2>Report source ledger</h2></div>
                <span class="badge neutral">Included data</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Reference</th><th>Type</th><th>User</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody id="transactionTable"></tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script src="script.js"></script>
</body>
</html>
