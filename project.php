<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Digital Wallet Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="#">
            <span class="brand-mark">PHP</span>
            <div>
                <strong>PeraHP</strong>
                <small>Digital wallet</small>
            </div>
        </a>
        <nav class="nav-list">
            <a class="nav-link active" href="#">Dashboard</a>
            <a class="nav-link" href="#">Wallets</a>
            <a class="nav-link" href="#">Transactions</a>
            <a class="nav-link" href="#">Exchange</a>
            <a class="nav-link" href="#">Reports</a>
            <a class="nav-link" href="#">Settings</a>
        </nav>
        <div class="auth-box">
            <span class="status-dot"></span>
            <div>
                <strong>Maria Santos</strong>
                <small>maria@perahp.test</small>
            </div>
            <button class="mini-button" id="logoutButton" style="margin-left:auto;">Logout</button>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="page">
        <!-- Top Bar -->
        <header class="topbar">
            <div>
                <h1>Dashboard</h1>
                <small style="color:var(--muted);">Welcome back, Maria</small>
            </div>
            <div class="top-actions">
                <button class="icon-button" id="menuButton">
                    <span></span><span></span><span></span>
                </button>
                <button class="ghost-button" id="printButton">🖨 Print</button>
            </div>
        </header>

        <!-- Overview / Metrics -->
        <section class="overview-band">
            <div class="overview-copy">
                <p class="eyebrow">Overview</p>
                <h2>Your money at a glance</h2>
                <p>Track your multi-currency balances and recent activity all in one place.</p>
            </div>
            <div class="readiness-card">
                <h2 style="font-size:1.1rem;">Account readiness</h2>
                <div class="progress-meter"><span style="width:76%;"></span></div>
                <div class="readiness-list">
                    <div><span>Profile</span><span>✅ Complete</span></div>
                    <div><span>Wallets</span><span>✅ 5 active</span></div>
                    <div><span>Verification</span><span>⏳ Pending</span></div>
                </div>
            </div>
        </section>

        <!-- Metric Cards -->
        <section class="metric-grid">
            <div class="metric-card main-metric">
                <span>💰 Total Balance</span>
                <strong id="totalBalance">PHP 0.00</strong>
                <small>All wallets combined</small>
            </div>
            <div class="metric-card">
                <span>📈 Received (Jun)</span>
                <strong id="monthlyReceived">PHP 0.00</strong>
                <small>This month</small>
            </div>
            <div class="metric-card">
                <span>📤 Sent (Jun)</span>
                <strong id="monthlySent">PHP 0.00</strong>
                <small>This month</small>
            </div>
            <div class="metric-card">
                <span>⏳ Pending</span>
                <strong id="pendingCount">0</strong>
                <small>Requests awaiting action</small>
            </div>
        </section>

        <!-- Quick Actions -->
        <section class="quick-actions">
            <button class="action-tile" data-jump="#send-money">💸 Send money</button>
            <button class="action-tile" data-jump="#requests">📩 Request payment</button>
            <button class="action-tile" data-jump="#exchange">🔄 Exchange currency</button>
            <button class="action-tile" data-jump="#history">📋 View history</button>
        </section>

        <!-- Main Grid -->
        <section class="grid two-columns">
            <!-- Wallets & Activity -->
            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Wallets</p><h2>Multi-currency balances</h2></div>
                    <span class="badge neutral">PHP base</span>
                </div>
                <div class="wallet-grid" id="walletGrid"></div>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Activity</p><h2>Recent activity feed</h2></div>
                    <span class="badge success">Live mock data</span>
                </div>
                <div class="activity-list" id="activityList"></div>
            </article>
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
            <article class="panel" id="exchange">
                <div class="panel-heading">
                    <div><p class="eyebrow">Currency Exchange</p><h2>Convert between wallets</h2></div>
                    <span class="badge neutral">Stored rates</span>
                </div>
                <form class="form-stack" id="exchangeForm">
                    <div class="form-row">
                        <label>Amount<input type="number" id="exchangeAmount" min="1" value="50"></label>
                        <label>From<select id="exchangeFrom"></select></label>
                        <label>To<select id="exchangeTo"></select></label>
                    </div>
                    <div class="conversion-preview">
                        <span>Exchange preview</span>
                        <strong id="exchangePreview">PHP 0.00</strong>
                        <small>Mock rates are managed by admin in this static version.</small>
                    </div>
                    <button class="primary-button" type="submit">Exchange funds</button>
                </form>
            </article>

            <article class="panel" id="profile">
                <div class="panel-heading">
                    <div><p class="eyebrow">Profile Management</p><h2>Personal information</h2></div>
                    <span class="badge success">Editable</span>
                </div>
                <form class="form-stack" id="profileForm">
                    <label>Full name<input type="text" value="Maria Santos"></label>
                    <label>Email<input type="email" value="maria@perahp.test"></label>
                    <div class="form-row two">
                        <label>Phone<input type="text" value="+63 917 100 2000"></label>
                        <label>New password<input type="password" placeholder="Optional"></label>
                    </div>
                    <button class="secondary-button" type="submit">Save profile</button>
                </form>
            </article>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div><p class="eyebrow">Exchange Rates</p><h2>Admin-managed conversion table</h2></div>
                <span class="badge neutral">PHP reference</span>
            </div>
            <div class="rate-grid" id="rateGrid"></div>
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

        <section class="grid two-columns">
            <article class="panel" id="reports">
                <div class="panel-heading">
                    <div><p class="eyebrow">Financial Reports</p><h2>Monthly summary chart</h2></div>
                    <span class="badge success">Printable</span>
                </div>
                <div class="chart-legend"><span class="legend-dot received"></span>Received <span class="legend-dot sent"></span>Sent</div>
                <div class="chart" id="reportChart"></div>
            </article>

            <article class="panel" id="admin">
                <div class="panel-heading">
                    <div><p class="eyebrow">Admin Preview</p><h2>User management and audit log</h2></div>
                    <span class="badge warning">Role gated</span>
                </div>
                <div class="admin-list">
                    <div><strong>Maria Santos</strong><span class="badge success">Active</span><button class="mini-button">Suspend</button><button class="mini-button">Reset</button></div>
                    <div><strong>Juan Dela Cruz</strong><span class="badge success">Active</span><button class="mini-button">Suspend</button><button class="mini-button">Reset</button></div>
                    <div><strong>Login audit</strong><small>Maria signed in from 127.0.0.1 at 8:45 PM</small></div>
                </div>
            </article>
        </section>

        <section class="security-grid">
            <article class="security-card"><span>Session</span><strong>Active state shown</strong><small>The current user and session state remain visible while working.</small></article>
            <article class="security-card"><span>Roles</span><strong>User and admin split</strong><small>Administrative actions are grouped away from normal wallet tasks.</small></article>
            <article class="security-card"><span>Audit</span><strong>Activity trail ready</strong><small>Login and transaction events have clear spaces for backend records.</small></article>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script src="script.js"></script>
</body>
</html>
