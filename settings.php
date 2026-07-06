<?php
$user = [
    "name" => "Maria Santos",
    "email" => "maria@perahp.test",
    "phone" => "+63 917 100 2000",
    "address" => "Makati City, Philippines",
    "role" => "Wallet owner",
    "status" => "Active"
];

function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Settings</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Sidebar -->
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
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link" href="reports.php">Reports</a>
            <a class="nav-link active" href="settings.php">Settings</a>
        </nav>
        <div class="auth-box">
            <a class="profile-link" href="profile.php" aria-label="Open profile">
                <span class="status-dot"></span>
                <div>
                    <strong><?php echo e($user["name"]); ?></strong>
                    <small><?php echo e($user["email"]); ?></small>
                </div>
            </a>
            <button class="mini-button" id="logoutButton" style="margin-left:auto;">Logout</button>
        </div>
    </aside>

    <!-- Settings Content -->
    <div class="page">
        <header class="topbar">
            <div>
                <h1>Settings</h1>
                <small style="color:var(--muted);">Manage profile, security, and wallet preferences</small>
            </div>
            <div class="top-actions">
                <button class="icon-button" id="menuButton" aria-label="Open menu">
                    <span></span><span></span><span></span>
                </button>
                <button class="ghost-button" id="printButton">Print</button>
            </div>
        </header>

        <section class="overview-band settings-overview">
            <div class="overview-copy">
                <p class="eyebrow">Account Settings</p>
                <h2>Keep your PeraHP account ready for secure transactions.</h2>
                <p>Update your personal details, security choices, alerts, and wallet defaults from one connected page.</p>
            </div>
            <div class="readiness-card">
                <h2 style="font-size:1.1rem;">Account status</h2>
                <div class="progress-meter"><span style="width:88%;"></span></div>
                <div class="readiness-list">
                    <div><span>Profile</span><span>Complete</span></div>
                    <div><span>Security</span><span>2FA ready</span></div>
                    <div><span>KYC</span><span>Reviewing</span></div>
                </div>
            </div>
        </section>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Profile</p><h2>Personal information</h2></div>
                    <span class="badge success"><?php echo e($user["status"]); ?></span>
                </div>
                <form class="form-stack" id="settingsProfileForm">
                    <label>Full name<input type="text" name="full_name" value="<?php echo e($user["name"]); ?>"></label>
                    <label>Email address<input type="email" name="email" value="<?php echo e($user["email"]); ?>"></label>
                    <div class="form-row two">
                        <label>Phone number<input type="text" name="phone" value="<?php echo e($user["phone"]); ?>"></label>
                        <label>City / Province<input type="text" name="address" value="<?php echo e($user["address"]); ?>"></label>
                    </div>
                    <button class="primary-button" type="submit">Save profile</button>
                </form>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Security</p><h2>Password and access</h2></div>
                    <span class="badge warning">Needs backend</span>
                </div>
                <form class="form-stack" id="settingsSecurityForm">
                    <label>Current password<input type="password" id="currentPassword" placeholder="Required before changing password"></label>
                    <div class="form-row two">
                        <label>New password<input type="password" id="newPassword" placeholder="Optional"></label>
                        <label>Confirm password<input type="password" id="confirmPassword" placeholder="Optional"></label>
                    </div>
                    <div class="form-row two">
                        <label>Two-factor authentication
                            <select id="twoFactorMethod">
                                <option value="sms">SMS code</option>
                                <option value="email">Email code</option>
                                <option value="app">Authenticator app</option>
                            </select>
                        </label>
                        <label>Login alerts
                            <select id="loginAlerts">
                                <option value="all">Every login</option>
                                <option value="new-device">New devices only</option>
                                <option value="off">Off</option>
                            </select>
                        </label>
                    </div>
                    <button class="secondary-button" type="submit">Update security</button>
                </form>
            </article>
        </section>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Preferences</p><h2>Wallet defaults</h2></div>
                    <span class="badge neutral">Local mock</span>
                </div>
                <form class="form-stack" id="settingsPreferenceForm">
                    <div class="form-row two">
                        <label>Default wallet
                            <select id="defaultWallet">
                                <option value="PHP">PHP - Philippine Peso</option>
                                <option value="USD">USD - US Dollar</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="JPY">JPY - Japanese Yen</option>
                                <option value="SGD">SGD - Singapore Dollar</option>
                            </select>
                        </label>
                        <label>Statement schedule
                            <select id="statementSchedule">
                                <option value="monthly">Monthly</option>
                                <option value="weekly">Weekly</option>
                                <option value="quarterly">Quarterly</option>
                            </select>
                        </label>
                    </div>
                    <div class="form-row two">
                        <label>Daily send limit<input type="number" id="dailyLimit" min="100" value="50000"></label>
                        <label>Single transaction limit<input type="number" id="transactionLimit" min="100" value="25000"></label>
                    </div>
                    <button class="primary-button" type="submit">Save preferences</button>
                </form>
            </article>

            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Notifications</p><h2>Activity alerts</h2></div>
                    <span class="badge success">Enabled</span>
                </div>
                <form class="form-stack" id="settingsNotificationForm">
                    <label class="setting-toggle">
                        <input type="checkbox" checked>
                        <span><strong>Transaction updates</strong><small>Send alerts for successful, pending, and failed transfers.</small></span>
                    </label>
                    <label class="setting-toggle">
                        <input type="checkbox" checked>
                        <span><strong>Payment request reminders</strong><small>Notify me when requested payments are still pending.</small></span>
                    </label>
                    <label class="setting-toggle">
                        <input type="checkbox">
                        <span><strong>Marketing messages</strong><small>Receive occasional product news and wallet tips.</small></span>
                    </label>
                    <button class="secondary-button" type="submit">Save alerts</button>
                </form>
            </article>
        </section>

        <section class="grid two-columns">
            <article class="panel">
                <div class="panel-heading">
                    <div><p class="eyebrow">Account</p><h2>Current access profile</h2></div>
                    <span class="badge neutral"><?php echo e($user["role"]); ?></span>
                </div>
                <div class="settings-list">
                    <div><span>Primary email</span><strong><?php echo e($user["email"]); ?></strong></div>
                    <div><span>Mobile number</span><strong><?php echo e($user["phone"]); ?></strong></div>
                    <div><span>Device trust</span><strong>This browser remembered</strong></div>
                    <div><span>Last reviewed</span><strong>Jul 6, 2026</strong></div>
                </div>
            </article>

            <article class="panel danger-zone">
                <div class="panel-heading">
                    <div><p class="eyebrow">Controls</p><h2>Account actions</h2></div>
                    <span class="badge danger">Careful</span>
                </div>
                <div class="settings-list">
                    <div><span>Download data</span><button class="mini-button" data-settings-action="download">Request export</button></div>
                    <div><span>Freeze wallet</span><button class="mini-button" data-settings-action="freeze">Freeze account</button></div>
                    <div><span>Close account</span><button class="mini-button" data-settings-action="close">Start review</button></div>
                </div>
            </article>
        </section>
    </div>

    <div class="toast" id="toast">Action completed</div>
    <script src="script.js"></script>
</body>
</html>
