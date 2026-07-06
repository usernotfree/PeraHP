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
                <strong>Maria Santos</strong>
                <small>maria@perahp.test</small>
            </div>
            <button class="mini-button" id="logoutButton" style="margin-left:auto;">Logout</button>
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
                    <tbody id="transactionTable">
                        <tr><td>RCV-260701-214</td><td>Receive</td><td>Client Example</td><td>PHP 74,930.00</td><td><span class="badge success">Completed</span></td><td>Jul 1, 2026</td></tr>
                        <tr><td>SEND-260630-A91</td><td>Send</td><td>Juan Dela Cruz</td><td>USD 100.00</td><td><span class="badge success">Completed</span></td><td>Jun 30, 2026</td></tr>
                        <tr><td>REQ-260629-K02</td><td>Request</td><td>Client Example</td><td>PHP 2,500.00</td><td><span class="badge warning">Pending</span></td><td>Jun 29, 2026</td></tr>
                        <tr><td>EXCH-260628-V19</td><td>Exchange</td><td>Maria Santos</td><td>EUR 50.00</td><td><span class="badge success">Completed</span></td><td>Jun 28, 2026</td></tr>
                        <tr><td>SEND-260627-R77</td><td>Send</td><td>Online Store</td><td>SGD 40.00</td><td><span class="badge danger" style="background:#e74c3c;">Failed</span></td><td>Jun 27, 2026</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script src="script.js"></script>
</body>
</html>