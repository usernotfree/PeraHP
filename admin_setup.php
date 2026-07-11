<?php
require_once __DIR__ . "/auth.php";

function perahp_admin_exists() {
    $pdo = perahp_db();

    if (!$pdo) {
        return false;
    }

    try {
        $count = $pdo->query("SELECT COUNT(*) AS admin_count FROM users WHERE role = 'admin'")->fetch();
        return (int) ($count["admin_count"] ?? 0) > 0;
    } catch (Throwable $exception) {
        error_log("PeraHP admin setup check failed: " . $exception->getMessage());
        return false;
    }
}

$dbReady = perahp_db() !== null;
$adminExists = $dbReady ? perahp_admin_exists() : false;
$values = [
    "full_name" => "",
    "email" => ""
];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && $dbReady && !$adminExists) {
    $values["full_name"] = trim((string) ($_POST["full_name"] ?? ""));
    $values["email"] = trim((string) ($_POST["email"] ?? ""));
    $password = (string) ($_POST["password"] ?? "");
    $confirmPassword = (string) ($_POST["confirm_password"] ?? "");

    if (!csrf_token_is_valid($_POST["csrf_token"] ?? null)) {
        $error = "Your session token expired. Please try again.";
    } elseif (strlen($values["full_name"]) < 2) {
        $error = "Enter the admin full name.";
    } elseif (!filter_var($values["email"], FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid admin email.";
    } elseif (strlen($password) < 8) {
        $error = "Use at least 8 characters for the admin password.";
    } elseif (!hash_equals($password, $confirmPassword)) {
        $error = "The password confirmation does not match.";
    }

    if ($error === "") {
        $pdo = perahp_db();

        try {
            $existing = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $existing->execute(["email" => $values["email"]]);

            if ($existing->fetch()) {
                $error = "An account already exists for that email.";
            } else {
                $pdo->beginTransaction();

                $insertUser = $pdo->prepare(
                    "INSERT INTO users (full_name, email, password_hash, role, status)
                     VALUES (:full_name, :email, :password_hash, 'admin', 'active')"
                );
                $insertUser->execute([
                    "full_name" => $values["full_name"],
                    "email" => $values["email"],
                    "password_hash" => password_hash($password, PASSWORD_DEFAULT)
                ]);
                $userId = (int) $pdo->lastInsertId();

                $insertWallet = $pdo->prepare(
                    "INSERT INTO wallets (user_id, currency_code, balance, status)
                     VALUES (:user_id, 'PHP', 0.00, 'active')"
                );
                $insertWallet->execute(["user_id" => $userId]);

                $insertSettings = $pdo->prepare(
                    "INSERT INTO user_settings (user_id, default_currency)
                     VALUES (:user_id, 'PHP')"
                );
                $insertSettings->execute(["user_id" => $userId]);

                $insertAudit = $pdo->prepare(
                    "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, details)
                     VALUES (:user_id, 'admin.created', 'users', :entity_id, :ip_address, :user_agent, :details)"
                );
                $insertAudit->execute([
                    "user_id" => $userId,
                    "entity_id" => $userId,
                    "ip_address" => $_SERVER["REMOTE_ADDR"] ?? null,
                    "user_agent" => substr((string) ($_SERVER["HTTP_USER_AGENT"] ?? ""), 0, 255),
                    "details" => json_encode(["setup" => "first_admin"])
                ]);

                $pdo->commit();

                $createdUser = find_user_by_email($values["email"]);
                login_user(user_from_database_row($createdUser));
                header("Location: admin_deposits.php");
                exit;
            }
        } catch (Throwable $exception) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("PeraHP admin setup failed: " . $exception->getMessage());
            $error = "Admin setup could not be completed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Admin Setup</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="login-panel">
            <a class="brand auth-brand" href="login.php">
                <span class="brand-mark">PHP</span>
                <div><strong>PeraHP</strong><small>Digital wallet</small></div>
            </a>

            <div class="auth-copy">
                <p class="eyebrow">First Admin</p>
                <h1>Create the admin account.</h1>
                <p>This setup page is only used while the database has no administrator account yet.</p>
            </div>

            <?php if (!$dbReady): ?>
                <div class="auth-alert">Import schema.sql first, then refresh this page.</div>
            <?php elseif ($adminExists): ?>
                <div class="auth-alert">An administrator already exists. Log in with that account to access admin pages.</div>
            <?php elseif ($error !== ""): ?>
                <div class="auth-alert"><?php echo e($error); ?></div>
            <?php endif; ?>

            <?php if ($dbReady && !$adminExists): ?>
                <form class="form-stack" method="post" action="admin_setup.php">
                    <?php echo csrf_input(); ?>
                    <label>Full name
                        <input type="text" name="full_name" value="<?php echo e($values["full_name"]); ?>" autocomplete="name" required>
                    </label>
                    <label>Email address
                        <input type="email" name="email" value="<?php echo e($values["email"]); ?>" autocomplete="email" required>
                    </label>
                    <div class="form-row two">
                        <label>Password
                            <input type="password" name="password" autocomplete="new-password" required>
                        </label>
                        <label>Confirm password
                            <input type="password" name="confirm_password" autocomplete="new-password" required>
                        </label>
                    </div>
                    <button class="primary-button" type="submit">Create admin</button>
                </form>
            <?php endif; ?>

            <div class="auth-links">
                <a href="login.php">Back to login</a>
                <a href="main.php">Dashboard</a>
            </div>
        </section>

        <section class="auth-showcase" aria-label="PeraHP admin setup preview">
            <p class="eyebrow">Approval Flow</p>
            <h2>Admins review deposits before balances change.</h2>
            <div class="auth-preview-card">
                <span>Admin access</span>
                <strong>Deposit approvals</strong>
                <small>After setup, this account can approve or reject pending cash-in requests.</small>
            </div>
        </section>
    </main>
</body>
</html>
