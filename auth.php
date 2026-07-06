<?php
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
        "name" => "Maria Santos",
        "email" => $email,
        "phone" => "+63 917 100 2000",
        "address" => "Makati City, Philippines",
        "role" => "Wallet owner",
        "status" => "Active",
        "member_since" => "January 2026"
    ];
}

function is_logged_in() {
    return isset($_SESSION["perahp_user"]) && is_array($_SESSION["perahp_user"]);
}

function current_user() {
    return is_logged_in() ? $_SESSION["perahp_user"] : demo_user();
}

function login_user($email) {
    session_regenerate_id(true);
    $_SESSION["perahp_user"] = demo_user($email);
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
