<?php
require_once __DIR__ . "/wallet_data.php";

function perahp_account_set_flash($type, $message) {
    $_SESSION["perahp_account_flash"] = [
        "type" => $type,
        "message" => $message
    ];
}

function perahp_account_take_flash() {
    $flash = $_SESSION["perahp_account_flash"] ?? null;
    unset($_SESSION["perahp_account_flash"]);
    return is_array($flash) ? $flash : null;
}

function perahp_find_user_by_id($userId) {
    $pdo = perahp_db();

    if (!$pdo) {
        return null;
    }

    try {
        $statement = $pdo->prepare(
            "SELECT id, full_name, email, password_hash, phone, address, role, status, created_at
             FROM users
             WHERE id = :id
             LIMIT 1"
        );
        $statement->execute(["id" => $userId]);
        $user = $statement->fetch();

        return $user ?: null;
    } catch (Throwable $exception) {
        error_log("PeraHP user lookup by id failed: " . $exception->getMessage());
        return null;
    }
}

function perahp_refresh_session_user($userId) {
    $databaseUser = perahp_find_user_by_id($userId);

    if ($databaseUser) {
        $_SESSION["perahp_user"] = user_from_database_row($databaseUser);
    }

    return current_user();
}

function perahp_user_settings($userId) {
    $defaults = [
        "default_currency" => "PHP",
        "email_notifications" => 1,
        "sms_notifications" => 0,
        "two_factor_enabled" => 0
    ];

    if ($userId <= 0) {
        return $defaults;
    }

    $pdo = perahp_db();

    if (!$pdo) {
        return $defaults;
    }

    try {
        $statement = $pdo->prepare(
            "SELECT default_currency, email_notifications, sms_notifications, two_factor_enabled
             FROM user_settings
             WHERE user_id = :user_id
             LIMIT 1"
        );
        $statement->execute(["user_id" => $userId]);
        $settings = $statement->fetch();

        return $settings ? array_merge($defaults, $settings) : $defaults;
    } catch (Throwable $exception) {
        error_log("PeraHP settings lookup failed: " . $exception->getMessage());
        return $defaults;
    }
}

function perahp_require_database_account($user) {
    $userId = (int) ($user["id"] ?? 0);

    if ($userId <= 0) {
        throw new RuntimeException("Use a registered account to save changes.");
    }

    if (!perahp_db()) {
        throw new RuntimeException("Database connection is not ready.");
    }

    return $userId;
}

function perahp_update_profile($user) {
    $userId = perahp_require_database_account($user);
    $name = trim((string) ($_POST["full_name"] ?? ""));
    $phone = trim((string) ($_POST["phone"] ?? ""));
    $address = trim((string) ($_POST["address"] ?? ""));

    if (strlen($name) < 2) {
        throw new RuntimeException("Enter your full name.");
    }

    $pdo = perahp_db();
    $statement = $pdo->prepare(
        "UPDATE users
         SET full_name = :full_name,
             phone = :phone,
             address = :address
         WHERE id = :id"
    );
    $statement->execute([
        "full_name" => $name,
        "phone" => $phone !== "" ? $phone : null,
        "address" => $address !== "" ? $address : null,
        "id" => $userId
    ]);

    perahp_refresh_session_user($userId);
    perahp_account_set_flash("success", "Profile saved.");
}

function perahp_update_preferences($user) {
    $userId = perahp_require_database_account($user);
    $currency = perahp_currency_code($_POST["default_currency"] ?? "");
    $rates = perahp_exchange_rates();

    if ($currency === "" || !isset($rates[$currency])) {
        throw new RuntimeException("Choose a valid default currency.");
    }

    $pdo = perahp_db();
    $statement = $pdo->prepare(
        "INSERT INTO user_settings (user_id, default_currency)
         VALUES (:user_id, :default_currency)
         ON DUPLICATE KEY UPDATE default_currency = VALUES(default_currency)"
    );
    $statement->execute([
        "user_id" => $userId,
        "default_currency" => $currency
    ]);

    perahp_account_set_flash("success", "Preferences saved.");
}

function perahp_change_password($user) {
    $userId = perahp_require_database_account($user);
    $currentPassword = (string) ($_POST["current_password"] ?? "");
    $newPassword = (string) ($_POST["new_password"] ?? "");
    $confirmPassword = (string) ($_POST["confirm_password"] ?? "");

    if (strlen($newPassword) < 8) {
        throw new RuntimeException("Use at least 8 characters for the new password.");
    }

    if (!hash_equals($newPassword, $confirmPassword)) {
        throw new RuntimeException("The new password confirmation does not match.");
    }

    $databaseUser = perahp_find_user_by_id($userId);

    if (!$databaseUser || !password_verify($currentPassword, $databaseUser["password_hash"])) {
        throw new RuntimeException("Current password is incorrect.");
    }

    $pdo = perahp_db();
    $statement = $pdo->prepare(
        "UPDATE users
         SET password_hash = :password_hash
         WHERE id = :id"
    );
    $statement->execute([
        "password_hash" => password_hash($newPassword, PASSWORD_DEFAULT),
        "id" => $userId
    ]);

    perahp_account_set_flash("success", "Password updated.");
}

function perahp_handle_account_post($user, $allowedActions) {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        return;
    }

    $action = (string) ($_POST["action"] ?? "");

    if (!csrf_token_is_valid($_POST["csrf_token"] ?? null)) {
        perahp_account_set_flash("error", "Your session token expired. Please try again.");
        return;
    }

    if (!in_array($action, $allowedActions, true)) {
        perahp_account_set_flash("error", "Unknown account action.");
        return;
    }

    try {
        if ($action === "update_profile") {
            perahp_update_profile($user);
        } elseif ($action === "update_preferences") {
            perahp_update_preferences($user);
        } elseif ($action === "change_password") {
            perahp_change_password($user);
        }
    } catch (Throwable $exception) {
        error_log("PeraHP account action failed: " . $exception->getMessage());
        perahp_account_set_flash("error", $exception->getMessage());
    }
}
