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
    <title>Settings - PeraHP</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="main.php"><span class="brand-mark">PHP</span><div><strong>PeraHP</strong><small>Digital wallet</small></div></a>
        <nav class="nav-list">
            <a class="nav-link" href="main.php">Dashboard</a>
            <a class="nav-link" href="wallets.php">Wallets</a>
            <a class="nav-link" href="transactions.php">Transactions</a>
            <a class="nav-link" href="exchange.php">Exchange</a>
            <a class="nav-link" href="reports.php">Reports</a>
            <a class="nav-link active" href="settings.php">Settings</a>
        </nav>
        <div class="auth-box">
            <span class="status-dot"></span>
            <div><strong><?php echo e($user["name"]); ?></strong><small><?php echo e($user["email"]); ?></small></div>
            <a class="mini-button logout-link" href="logout.php" style="margin-left:auto;">Logout</a>
        </div>
    </aside>

    <div class="page">
        <header class="topbar">
            <div><h1>Account Settings</h1><small style="color:var(--muted);">Manage your profile and security</small></div>
        </header>

        <section class="panel">
            <form class="form-stack" id="profileForm">
                <div class="panel-heading"><h2>Personal Information</h2></div>
                <div class="form-row two">
                    <label>Full name<input type="text" value="<?php echo e($user["name"]); ?>"></label>
                    <label>Email<input type="email" value="<?php echo e($user["email"]); ?>"></label>
                </div>
                <div class="form-row two">
                    <label>Phone<input type="text" value="<?php echo e($user["phone"]); ?>"></label>
                    <label>Date of Birth<input type="date"></label>
                </div>
                <label>Address<input type="text" placeholder="123 Street Name, City, Philippines"></label>

                <hr style="border: 0; border-top: 1px solid var(--line); margin: 20px 0;">

                <div class="panel-heading"><h2>Security</h2></div>
                <div class="form-row two">
                    <label>Current password<input type="password"></label>
                    <label>New password<input type="password"></label>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--line); margin: 20px 0;">

                <div class="panel-heading"><h2>Preferences</h2></div>
                <label>Default Currency
                    <select>
                        <option>PHP - Philippine Peso</option>
                        <option>USD - US Dollar</option>
                    </select>
                </label>

                <button class="primary-button" type="submit" style="margin-top:20px;">Save all changes</button>
            </form>
        </section>
    </div>
    <script src="script.js"></script>
</body>
</html>
