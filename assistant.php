<?php
require_once __DIR__ . "/auth.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
    exit;
}

$payload = json_decode((string) file_get_contents("php://input"), true);
$token = is_array($payload) ? ($payload["csrf_token"] ?? "") : "";

if (!csrf_token_is_valid($token)) {
    http_response_code(403);
    echo json_encode(["error" => "Your chat session expired. Refresh the page and try again."]);
    exit;
}

$now = time();
$requests = array_values(array_filter($_SESSION["perahp_assistant_requests"] ?? [], fn($time) => $time > $now - 60));
if (count($requests) >= 20) {
    http_response_code(429);
    echo json_encode(["error" => "Please wait a moment before sending another message."]);
    exit;
}
$requests[] = $now;
$_SESSION["perahp_assistant_requests"] = $requests;

$message = trim((string) ($payload["message"] ?? ""));
if ($message === "" || mb_strlen($message) > 500) {
    http_response_code(422);
    echo json_encode(["error" => "Enter a message between 1 and 500 characters."]);
    exit;
}

$text = mb_strtolower($message);
$answer = "I can help with PeraHP accounts, wallets, transfers, payment requests, currency exchange, security, and finding pages. What would you like to know?";
$suggestions = ["How do I create an account?", "Which currencies are supported?", "How does exchange work?"];

$topics = [
    "register" => ["keywords" => ["register", "sign up", "create account", "new account", "join"], "answer" => "Select Create account on the home page, enter your details, and choose a password with at least 8 characters. PeraHP creates a PHP wallet for you after registration.", "suggestions" => ["How do I log in?", "Is PeraHP secure?", "Which currencies are supported?"]],
    "login" => ["keywords" => ["login", "log in", "sign in", "password"], "answer" => "Select Log in at the top of the home page. Enter your registered email and password, then you’ll be taken to your dashboard.", "suggestions" => ["How do I create an account?", "What is the dashboard?", "Is PeraHP secure?"]],
    "wallet" => ["keywords" => ["wallet", "balance", "cash in", "fund"], "answer" => "Your Wallets page shows each currency balance and its PHP equivalent. You can cash in, send money, request payment, or create another currency wallet through supported transactions.", "suggestions" => ["Which currencies are supported?", "How do I send money?", "How does exchange work?"]],
    "send" => ["keywords" => ["send", "transfer", "recipient", "pay someone"], "answer" => "Open Wallets, choose Send Money, select the source and recipient currencies, enter the recipient’s registered email and amount, then review before submitting.", "suggestions" => ["How do payment requests work?", "How does exchange work?", "Where can I see transactions?"]],
    "request" => ["keywords" => ["request", "payment request", "ask for money"], "answer" => "Open Wallets and use Request Payment. Enter the payer’s email, amount, and currency. You can track pending requests from the Payment Requests page.", "suggestions" => ["How do I send money?", "Where can I see transactions?", "Is PeraHP secure?"]],
    "exchange" => ["keywords" => ["exchange", "convert", "rate", "currency"], "answer" => "Open Exchange, enter an amount, then select your source and destination currencies. PeraHP previews the converted amount using its configured PHP reference rates before you submit.", "suggestions" => ["Which currencies are supported?", "Can I use Korean Won?", "Where can I see transactions?"]],
    "krw" => ["keywords" => ["krw", "korean won", "korea won", "won"], "answer" => "Yes. PeraHP supports South Korean Won (KRW). You can select KRW for transfers, payment requests, cash-in, and currency exchange.", "suggestions" => ["How does exchange work?", "Which currencies are supported?", "How do I cash in?"]],
    "currencies" => ["keywords" => ["supported currencies", "which currencies", "currency list"], "answer" => "PeraHP currently supports PHP, USD, EUR, JPY, SGD, and KRW. Wallet totals are also shown as PHP equivalents.", "suggestions" => ["Can I use Korean Won?", "How does exchange work?", "What is a PHP equivalent?"]],
    "security" => ["keywords" => ["secure", "security", "safe", "privacy", "protect"], "answer" => "PeraHP uses password hashing, protected sessions, CSRF checks on account actions, role-based admin access, and audit records for important activity. Always log out on shared devices.", "suggestions" => ["How do I log in?", "Where can I see transactions?", "How do I create an account?"]],
    "transactions" => ["keywords" => ["transaction", "history", "activity", "receipt"], "answer" => "Open Transactions to search and filter your activity. The dashboard also shows your five most recent transactions and their current status.", "suggestions" => ["How do I send money?", "How do payment requests work?", "What do statuses mean?"]],
    "dark" => ["keywords" => ["dark mode", "theme", "light mode"], "answer" => "Use the moon or sun button in the header to switch themes. PeraHP remembers your choice as you move between supported pages.", "suggestions" => ["Where is the dashboard?", "Which currencies are supported?", "Is PeraHP secure?"]]
];

$bestScore = 0;
foreach ($topics as $topic) {
    $score = 0;
    foreach ($topic["keywords"] as $keyword) {
        if (str_contains($text, $keyword)) {
            $score += strlen($keyword);
        }
    }
    if ($score > $bestScore) {
        $bestScore = $score;
        $answer = $topic["answer"];
        $suggestions = $topic["suggestions"];
    }
}

echo json_encode(["answer" => $answer, "suggestions" => $suggestions], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);