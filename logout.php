<?php
require_once __DIR__ . "/auth.php";

$user = current_user();
logout_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Logged Out</title>
    <link rel="stylesheet" href="styles.css?v=20260712-bg">
</head>
<body class="auth-page auth-logout">
    <main class="auth-shell logout-shell">
        <section class="login-panel logout-panel">
            <a class="brand auth-brand" href="index.php">
                <img class="brand-mark" src="logo.png" width="46" height="46" alt="PeraHP logo">
                <div>
                    <strong>PeraHP</strong>
                    <small>Digital wallet</small>
                </div>
            </a>
            <div class="auth-copy">
                <p class="eyebrow">Signed Out</p>
                <h1>You are logged out.</h1>
                <p><?php echo e($user["name"]); ?>, your session has ended. Log in again when you are ready to continue.</p>
            </div>
            <a class="primary-button" href="login.php">Back to login</a>
        </section>
        <section class="auth-showcase logout-showcase" aria-label="Signed out securely">
            <p class="eyebrow">Session Complete</p>
            <h2>Your wallet is secure until you return.</h2>
            <div class="auth-preview-card logout-status-card">
                <span class="logout-check" aria-hidden="true">✓</span>
                <div>
                    <strong>Signed out safely</strong>
                    <small>Your PeraHP session has ended and your account remains protected.</small>
                </div>
            </div>
            <a class="logout-home-link" href="index.php">Explore PeraHP promos <span>→</span></a>
        </section>
    </main>
    <script src="script.js"></script>
</body>
</html>
