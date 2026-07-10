<?php
require_once __DIR__ . "/db.php";

$sessionPath = __DIR__ . "/runtime/sessions";

if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0775, true);
}

if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const PERAHP_LOGIN_EMAIL = "maria@perahp.test";
const PERAHP_LOGIN_PASSWORD = "perahp123";

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function demo_user($email = PERAHP_LOGIN_EMAIL) {
    return [
        "id" => null,
        "name" => "Maria Santos",
        "email" => $email,
        "phone" => "+63 917 100 2000",
        "address" => "Makati City, Philippines",
        "role" => "Wallet owner",
        "status" => "Active",
        "member_since" => "January 2026"
    ];
}

function user_from_database_row($row) {
    if (!$row) {
        return demo_user();
    }

    $createdAt = strtotime((string) ($row["created_at"] ?? ""));

    return [
        "id" => (int) $row["id"],
        "name" => $row["full_name"],
        "email" => $row["email"],
        "phone" => $row["phone"] ?: "",
        "address" => $row["address"] ?: "",
        "role" => ($row["role"] ?? "user") === "admin" ? "Administrator" : "Wallet owner",
        "status" => ucfirst((string) ($row["status"] ?? "active")),
        "member_since" => $createdAt ? date("F Y", $createdAt) : date("F Y")
    ];
}

function find_user_by_email($email) {
    $pdo = perahp_db();

    if (!$pdo) {
        return null;
    }

    try {
        $statement = $pdo->prepare(
            "SELECT id, full_name, email, password_hash, phone, address, role, status, created_at
             FROM users
             WHERE email = :email
             LIMIT 1"
        );
        $statement->execute(["email" => $email]);
        $user = $statement->fetch();

        return $user ?: null;
    } catch (Throwable $exception) {
        error_log("PeraHP user lookup failed: " . $exception->getMessage());
        return null;
    }
}

function authenticate_user($email, $password) {
    $databaseUser = find_user_by_email($email);

    if ($databaseUser) {
        if (!password_verify($password, $databaseUser["password_hash"])) {
            return null;
        }

        if (($databaseUser["status"] ?? "active") !== "active") {
            return null;
        }

        return user_from_database_row($databaseUser);
    }

    if (strcasecmp($email, PERAHP_LOGIN_EMAIL) === 0 && hash_equals(PERAHP_LOGIN_PASSWORD, $password)) {
        return demo_user(PERAHP_LOGIN_EMAIL);
    }

    return null;
}

function is_logged_in() {
    return isset($_SESSION["perahp_user"]) && is_array($_SESSION["perahp_user"]);
}

function current_user() {
    return is_logged_in() ? $_SESSION["perahp_user"] : demo_user();
}

function login_user($user) {
    session_regenerate_id(true);
    $_SESSION["perahp_user"] = is_array($user) ? $user : demo_user($user);
}

function logout_user() {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }

    session_destroy();
}

function safe_next_page($next) {
    $next = trim((string) $next);

    if ($next === "" || preg_match("/^(https?:)?\/\//i", $next) || str_contains($next, "\\")) {
        return "main.php";
    }

    return $next;
}

function require_login() {
    if (is_logged_in()) {
        return;
    }

    $next = basename($_SERVER["REQUEST_URI"] ?? "main.php");
    header("Location: login.php?next=" . urlencode($next));
    exit;
}
?>
