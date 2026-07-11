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

    $authenticatedUser = authenticate_user($email, $password);

    if ($authenticatedUser !== null) {
        login_user($authenticatedUser);
        header("Location: " . $next);
        exit;
    }

    $error = "Check your email and password, or create a new account.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Login</title>
    <link rel="stylesheet" href="styles.css?v=20260712-bg">
</head>
<body class="auth-page" style="background: linear-gradient(135deg, rgba(0, 0, 0, 0.58), rgba(0, 0, 0, 0.34)), url('moneybackground.jpg') center / cover no-repeat fixed !important;">
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
                <p>Use your registered account, or use the demo credentials while the database is being prepared.</p>
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

            <div class="demo-credentials">
                <span>Admin account</span>
                <strong><?php echo e(PERAHP_ADMIN_EMAIL); ?></strong>
                <small>Password: <?php echo e(PERAHP_ADMIN_PASSWORD); ?></small>
            </div>

            <div class="auth-links">
                <span>Need an account?</span>
                <a href="register.php?next=<?php echo urlencode($next); ?>">Create one</a>
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
