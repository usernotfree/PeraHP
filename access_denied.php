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
    <title>Access Denied - PeraHP</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="login-panel">
            <a class="brand auth-brand" href="main.php">
                <img class="brand-mark" src="logo.png" width="46" height="46" alt="PeraHP logo">
                <div><strong>PeraHP</strong><small>Digital wallet</small></div>
            </a>
            <div class="auth-copy">
                <p class="eyebrow">Admin Area</p>
                <h1>Access denied.</h1>
                <p><?php echo e($user["email"]); ?> is signed in, but this page requires an administrator account.</p>
            </div>
            <div class="auth-links">
                <a href="main.php">Back to dashboard</a>
                <a href="logout.php">Log out</a>
            </div>
        </section>
        <section class="auth-showcase" aria-label="PeraHP admin access">
            <p class="eyebrow">Protected Page</p>
            <h2>Deposit approvals are only available to admin users.</h2>
            <div class="auth-preview-card">
                <span>Current role</span>
                <strong><?php echo e($user["role"]); ?></strong>
                <small>Use an account with the admin role to review deposit requests.</small>
            </div>
        </section>
    </main>
</body>
</html>
