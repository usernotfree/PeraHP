<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Transactions</title>
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
            <a class="nav-link active" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link" href="reports.php">Reports</a>
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
                <h1>Transactions</h1>
                <small style="color:var(--muted);">Send, request, and review account activity</small>
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
                <span>Received (Jun)</span>
                <strong id="monthlyReceived">PHP 0.00</strong>
                <small>Incoming transactions</small>
            </div>
            <div class="metric-card">
                <span>Sent (Jun)</span>
                <strong id="monthlySent">PHP 0.00</strong>
                <small>Outgoing payments</small>
            </div>
            <div class="metric-card">
                <span>Pending</span>
                <strong id="pendingCount">0</strong>
                <small>Requests awaiting action</small>
            </div>
            <div class="metric-card">
                <span>Ledger Items</span>
                <strong>5</strong>
                <small>Mock records loaded</small>
            </div>
        </section>

        <section class="grid two-columns">
            <article class="panel" id="send-money">
                <div class="panel-heading">
                    <div><p class="eyebrow">Send Money</p><h2>Transfer with live conversion</h2></div>
                    <span class="badge success">Validated flow</span>
                </div>
                <form class="form-stack" id="sendForm">
                    <label>Recipient email<input type="email" id="recipientEmail" value="juan@perahp.test"></label>
                    <div class="form-row">
                        <label>Amount<input type="number" id="sendAmount" min="1" value="100"></label>
                        <label>From<select id="sendFrom"></select></label>
                        <label>To<select id="sendTo"></select></label>
                    </div>
                    <div class="conversion-preview">
                        <span>Converted amount</span>
                        <strong id="sendPreview">PHP 0.00</strong>
                        <small id="sendPhpValue">PHP base value: PHP 0.00</small>
                    </div>
                    <button class="primary-button" type="submit">Send payment</button>
                </form>
            </article>

            <article class="panel" id="requests">
                <div class="panel-heading">
                    <div><p class="eyebrow">Receive & Request</p><h2>Generate payment reference</h2></div>
                    <span class="badge warning">Pending queue</span>
                </div>
                <form class="form-stack" id="requestForm">
                    <div class="form-row two">
                        <label>Amount<input type="number" id="requestAmount" min="1" value="2500"></label>
                        <label>Currency<select id="requestCurrency"></select></label>
                    </div>
                    <label>Payer email<input type="email" id="payerEmail" value="client@example.com"></label>
                    <button class="secondary-button" type="submit">Generate reference</button>
                </form>
                <div class="reference-box">
                    <span>Latest reference</span>
                    <strong id="referenceCode">REQ-260701-001</strong>
                    <small id="referenceStatus">Status: Pending</small>
                </div>
            </article>
        </section>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Activity</p><h2>Recent activity feed</h2></div>
                    <span class="badge success">Live mock data</span>
                </div>
                <div class="activity-list" id="activityList"></div>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Review</p><h2>Transaction controls</h2></div>
                    <span class="badge neutral">Front-end mock</span>
                </div>
                <div class="settings-list">
                    <div><span>Default status filter</span><strong>All statuses</strong></div>
                    <div><span>Export format</span><strong>CSV ready</strong></div>
                    <div><span>Audit trail</span><strong>Prepared for backend logs</strong></div>
                </div>
            </article>
        </section>

        <section class="panel" id="history">
            <div class="panel-heading">
                <div><p class="eyebrow">Transaction History</p><h2>Searchable and filterable ledger</h2></div>
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
