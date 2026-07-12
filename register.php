<?php
require_once __DIR__ . "/auth.php";

if (is_logged_in()) {
    header("Location: main.php");
    exit;
}

$next = safe_next_page($_POST["next"] ?? $_GET["next"] ?? "main.php");
$values = [
    "full_name" => "",
    "email" => "",
    "phone" => "",
    "address" => ""
];
$error = "";
$dbReady = perahp_db() !== null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    foreach ($values as $key => $value) {
        $values[$key] = trim((string) ($_POST[$key] ?? ""));
    }

    $password = (string) ($_POST["password"] ?? "");
    $confirmPassword = (string) ($_POST["confirm_password"] ?? "");

    if (!$dbReady) {
        $error = "The database is not ready yet. Import schema.sql, then try registration again.";
    } elseif (strlen($values["full_name"]) < 2) {
        $error = "Enter your full name.";
    } elseif (!filter_var($values["email"], FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Use at least 8 characters for the password.";
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
                    "INSERT INTO users (full_name, email, password_hash, phone, address, role, status)
                     VALUES (:full_name, :email, :password_hash, :phone, :address, 'user', 'active')"
                );
                $insertUser->execute([
                    "full_name" => $values["full_name"],
                    "email" => $values["email"],
                    "password_hash" => password_hash($password, PASSWORD_DEFAULT),
                    "phone" => $values["phone"] !== "" ? $values["phone"] : null,
                    "address" => $values["address"] !== "" ? $values["address"] : null
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
                     VALUES (:user_id, 'user.registered', 'users', :entity_id, :ip_address, :user_agent, :details)"
                );
                $insertAudit->execute([
                    "user_id" => $userId,
                    "entity_id" => $userId,
                    "ip_address" => $_SERVER["REMOTE_ADDR"] ?? null,
                    "user_agent" => substr((string) ($_SERVER["HTTP_USER_AGENT"] ?? ""), 0, 255),
                    "details" => json_encode(["default_wallet" => "PHP"])
                ]);

                $pdo->commit();

                $createdUser = find_user_by_email($values["email"]);
                $sessionUser = $createdUser ? user_from_database_row($createdUser) : [
                    "id" => $userId,
                    "name" => $values["full_name"],
                    "email" => $values["email"],
                    "phone" => $values["phone"],
                    "address" => $values["address"],
                    "role" => "Wallet owner",
                    "status" => "Active",
                    "member_since" => date("F Y")
                ];

                login_user($sessionUser);
                header("Location: " . $next);
                exit;
            }
        } catch (Throwable $exception) {
            if ($pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("PeraHP registration failed: " . $exception->getMessage());
            $error = "Registration could not be completed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeraHP - Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="auth-page auth-register">
    <main class="auth-shell">
        <section class="login-panel">
            <a class="brand auth-brand" href="index.php">
                <img class="brand-mark" src="logo.png" width="46" height="46" alt="PeraHP logo">
                <div>
                    <strong>PeraHP</strong>
                    <small>Digital wallet</small>
                </div>
            </a>

            <div class="auth-copy">
                <p class="eyebrow">Create Account</p>
                <h1>Register your PeraHP wallet.</h1>
                <p>Create a user account with a secure password. A default PHP wallet is created with the account.</p>
            </div>

            <?php if (!$dbReady): ?>
                <div class="auth-alert">Import schema.sql into MySQL before creating accounts.</div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="auth-alert"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form class="form-stack" method="post" action="register.php">
                <input type="hidden" name="next" value="<?php echo e($next); ?>">
                <label>Full name
                    <input type="text" name="full_name" value="<?php echo e($values["full_name"]); ?>" autocomplete="name" required>
                </label>
                <label>Email address
                    <input type="email" name="email" value="<?php echo e($values["email"]); ?>" autocomplete="email" required>
                </label>
                <div class="form-row two">
                    <label>Phone
                        <input type="text" name="phone" value="<?php echo e($values["phone"]); ?>" autocomplete="tel">
                    </label>
                    <label>City / Province
                        <input type="text" name="address" value="<?php echo e($values["address"]); ?>" autocomplete="address-level2">
                    </label>
                </div>
                <div class="form-row two">
                    <label>Password
                        <input type="password" name="password" autocomplete="new-password" required>
                    </label>
                    <label>Confirm password
                        <input type="password" name="confirm_password" autocomplete="new-password" required>
                    </label>
                </div>
                <button class="primary-button" type="submit" <?php echo $dbReady ? "" : "disabled"; ?>>Create account</button>
            </form>

            <div class="auth-links">
                <span>Already have an account?</span>
                <a href="login.php?next=<?php echo urlencode($next); ?>">Log in</a>
            </div>
        </section>

        <section class="auth-showcase" aria-label="PeraHP registration preview">
            <p class="eyebrow">Account Setup</p>
            <h2>Start with a PHP wallet, then add the rest.</h2>
            <div class="auth-preview-card">
                <span>Default wallet</span>
                <strong>PHP 0.00</strong>
                <small>New users begin with an active Philippine Peso wallet ready for later funding flows.</small>
            </div>
        </section>
    </main>
    <script src="script.js"></script>
</body>
</html>
