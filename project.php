<?php
?>

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PeraHP Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="#dashboard" aria-label="PeraHP dashboard">
            <span class="brand-mark">P</span>
            <span>
                <strong>PeraHP</strong>
                <small>Multi-currency tracking</small>
            </span>
        </a>

        <nav class="nav-list" aria-label="Dashboard navigation">
            <a class="nav-link active" href="#dashboard">Dashboard</a>
            <a class="nav-link" href="#send-money">Send Money</a>
            <a class="nav-link" href="#requests">Receive & Request</a>
            <a class="nav-link" href="#exchange">Currency Exchange</a>
            <a class="nav-link" href="#history">Transaction History</a>
            <a class="nav-link" href="#reports">Financial Reports</a>
            <a class="nav-link" href="#admin">Admin</a>
            <a class="nav-link" href="#profile">Profile</a>
        </nav>

        <div class="auth-box">
            <span class="status-dot"></span>
            <div>
                <strong>Ning Yizhuo</strong>
                <small>User session active</small>
            </div>
        </div>
    </aside>

    <main class="page">
        <header class="topbar">
            <button class="icon-button" id="menuButton" aria-label="Open menu">
                <span></span><span></span><span></span>
            </button>
            <div>
                <p class="eyebrow">PeraHP</p>
                <h1>Transactional Multi-Currency Dashboard</h1>
            </div>
            <div class="top-actions">
                <button class="ghost-button" id="printButton">Print Report</button>
                <button class="primary-button" id="logoutButton">Logout</button>
            </div>
        </header>

        <section class="auth-preview">
            <div>
                <p class="eyebrow">Authentication Preview</p>
                <h2>Login, registration, session, and role access</h2>
            </div>
            <div class="auth-pills">
                <span>Logged in as User</span>
                <span>Admin tools locked by role</span>
                <span>Session: Active</span>
            </div>
        </section>

        <section class="metric-grid" id="dashboard">
            <article class="metric-card main-metric">
                <span>Total PHP Value</span>
                <strong id="totalBalance">PHP 0.00</strong>
                <small>All wallets converted using stored rates</small>
            </article>
            <article class="metric-card">
                <span>Money Received</span>
                <strong>PHP 74,930.00</strong>
                <small>This month</small>
            </article>
            <article class="metric-card">
                <span>Money Sent</span>
                <strong>PHP 28,440.00</strong>
                <small>This month</small>
            </article>
            <article class="metric-card">
                <span>Pending Requests</span>
                <strong id="pendingCount">3</strong>
                <small>Awaiting confirmation</small>
            </article>
        </section>

        <section class="quick-actions">
            <button class="action-tile" data-jump="#send-money">Send funds</button>
            <button class="action-tile" data-jump="#requests">Create request</button>
            <button class="action-tile" data-jump="#exchange">Exchange currency</button>
            <button class="action-tile" data-jump="#history">View ledger</button>
        </section>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">Wallets</p>
                        <h2>Multi-currency balances</h2>
                    </div>
                    <span class="badge neutral">PHP base</span>
                </div>
                <div class="wallet-grid" id="walletGrid"></div>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">Activity</p>
                        <h2>Recent activity feed</h2>
                    </div>
                </div>
                <div class="activity-list" id="activityList"></div>
            </article>
        </section>

        <section class="grid two-columns">
            <article class="panel" id="send-money">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">Send Money</p>
                        <h2>Transfer with live conversion</h2>
                    </div>
                </div>
                <form class="form-stack" id="sendForm">
                    <label>
                        Recipient email
                        <input type="email" value="juan@perahp.test">
                    </label>
                    <div class="form-row">
                        <label>
                            Amount
                            <input type="number" id="sendAmount" min="1" value="100">
                        </label>
                        <label>
                            From
                            <select id="sendFrom"></select>
                        </label>
                        <label>
                            To
                            <select id="sendTo"></select>
                        </label>
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
                    <div>
                        <p class="eyebrow">Receive & Request</p>
                        <h2>Generate payment reference</h2>
                    </div>
                </div>
                <form class="form-stack" id="requestForm">
                    <div class="form-row two">
                        <label>
                            Amount
                            <input type="number" id="requestAmount" min="1" value="2500">
                        </label>
                        <label>
                            Currency
                            <select id="requestCurrency"></select>
                        </label>
                    </div>
                    <label>
                        Payer email
                        <input type="email" id="payerEmail" value="client@example.com">
                    </label>
                    <button class="secondary-button" type="submit">Generate reference</button>
                </form>
                <div class="reference-box">
                    <span>Latest reference</span>
                    <strong id="referenceCode">REQ-260629-001</strong>
                    <small id="referenceStatus">Status: Pending</small>
                </div>
            </article>
        </section>

        <section class="grid two-columns">
            <article class="panel" id="exchange">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">Currency Exchange</p>
                        <h2>Convert between wallets</h2>
                    </div>
                </div>
                <form class="form-stack" id="exchangeForm">
                    <div class="form-row">
                        <label>
                            Amount
                            <input type="number" id="exchangeAmount" min="1" value="50">
                        </label>
                        <label>
                            From
                            <select id="exchangeFrom"></select>
                        </label>
                        <label>
                            To
                            <select id="exchangeTo"></select>
                        </label>
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
                    <div>
                        <p class="eyebrow">Profile Management</p>
                        <h2>Personal information</h2>
                    </div>
                </div>
                <form class="form-stack" id="profileForm">
                    <label>
                        Full name
                        <input type="text" value="Maria Santos">
                    </label>
                    <label>
                        Email
                        <input type="email" value="maria@perahp.test">
                    </label>
                    <div class="form-row two">
                        <label>
                            Phone
                            <input type="text" value="+63 917 100 2000">
                        </label>
                        <label>
                            New password
                            <input type="password" placeholder="Optional">
                        </label>
                    </div>
                    <button class="secondary-button" type="submit">Save profile</button>
                </form>
            </article>
        </section>

        <section class="panel" id="history">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">Transaction History</p>
                    <h2>Searchable and filterable ledger</h2>
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

        <section class="grid two-columns">
            <article class="panel" id="reports">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">Financial Reports</p>
                        <h2>Monthly summary chart</h2>
                    </div>
                    <span class="badge success">Printable</span>
                </div>
                <div class="chart" id="reportChart"></div>
            </article>

            <article class="panel" id="admin">
                <div class="panel-heading">
                    <div>
                        <p class="eyebrow">Admin Preview</p>
                        <h2>User management and audit log</h2>
                    </div>
                </div>
                <div class="admin-list">
                    <div>
                        <strong>Maria Santos</strong>
                        <span class="badge success">Active</span>
                        <button class="mini-button">Suspend</button>
                        <button class="mini-button">Reset</button>
                    </div>
                    <div>
                        <strong>Juan Dela Cruz</strong>
                        <span class="badge success">Active</span>
                        <button class="mini-button">Suspend</button>
                        <button class="mini-button">Reset</button>
                    </div>
                    <div>
                        <strong>Login audit</strong>
                        <small>Maria signed in from 127.0.0.1 at 8:45 PM</small>
                    </div>
                </div>
            </article>
        </section>
    </main>

    <div class="toast" id="toast">Action completed</div>
    <script src="script.js"></script>
</body>
</html>

