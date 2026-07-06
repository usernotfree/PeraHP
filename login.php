<?php
require_once __DIR__ . "/auth.php";

if (is_logged_in()) {
    header("Location: main.php");
    exit;
}

$email = $_POST["email"] ?? PERAHP_LOGIN_EMAIL;
$next = safe_next_page($_POST["next"] ?? $_GET["next"] ?? "main.php");
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim((string) ($_POST["email"] ?? ""));
    $password = (string) ($_POST["password"] ?? "");

    if (strcasecmp($email, PERAHP_LOGIN_EMAIL) === 0 && hash_equals(PERAHP_LOGIN_PASSWORD, $password)) {
        login_user(PERAHP_LOGIN_EMAIL);
        header("Location: " . $next);
        exit;
    }

    $error = "Use the demo email and password shown below.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="login-panel">
            <a class="brand auth-brand" href="login.php">
                <span class="brand-mark">PHP</span>
                <div>
                    <strong>PeraHP</strong>
                    <small>Digital wallet</small>
                </div>
            </a>

            <div class="auth-copy">
                <p class="eyebrow">Secure Access</p>
                <h1>Log in to your PeraHP wallet.</h1>
                <p>Enter the demo account credentials to open the home dashboard, wallet tools, reports, and exchange page.</p>
            </div>

            <?php if ($error !== ""): ?>
                <div class="auth-alert"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form class="form-stack" method="post" action="login.php">
                <input type="hidden" name="next" value="<?php echo e($next); ?>">
                <label>Email address
                    <input type="email" name="email" value="<?php echo e($email); ?>" autocomplete="username" required>
                </label>
                <label>Password
                    <input type="password" name="password" placeholder="Enter password" autocomplete="current-password" required>
                </label>
                <button class="primary-button" type="submit">Log in</button>
            </form>

            <div class="demo-credentials">
                <span>Demo account</span>
                <strong><?php echo e(PERAHP_LOGIN_EMAIL); ?></strong>
                <small>Password: <?php echo e(PERAHP_LOGIN_PASSWORD); ?></small>
            </div>
        </section>

        <section class="auth-showcase" aria-label="PeraHP account preview">
            <p class="eyebrow">Home Dashboard</p>
            <h2>Promos first, money tools when you need them.</h2>
            <div class="auth-preview-card">
                <span>Available balance</span>
                <strong>PHP 128,090.00</strong>
                <small>Cash in, send, request, and track spending after sign in.</small>
            </div>
        </section>
    </main>
</body>
</html>