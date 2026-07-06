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
    <link rel="stylesheet" href="styles.css">
</head>
<body class="auth-page">
    <main class="auth-shell logout-shell">
        <section class="login-panel logout-panel">
            <a class="brand auth-brand" href="login.php">
                <span class="brand-mark">PHP</span>
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
    </main>
</body>
</html>
