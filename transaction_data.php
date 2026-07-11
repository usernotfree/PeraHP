<?php
require_once __DIR__ . "/wallet_data.php";

function perahp_default_transactions() {
    return [
        ["ref" => "RCV-260701-214", "type" => "Receive", "user" => "Client Example", "amount" => 74930, "currency" => "PHP", "status" => "completed", "date" => "Jul 1, 2026"],
        ["ref" => "SEND-260630-A91", "type" => "Send", "user" => "Juan Dela Cruz", "amount" => 100, "currency" => "USD", "status" => "completed", "date" => "Jun 30, 2026"],
        ["ref" => "REQ-260629-K02", "type" => "Request", "user" => "Client Example", "amount" => 2500, "currency" => "PHP", "status" => "pending", "date" => "Jun 29, 2026"],
        ["ref" => "EXCH-260628-V19", "type" => "Exchange", "user" => "Maria Santos", "amount" => 50, "currency" => "EUR", "status" => "completed", "date" => "Jun 28, 2026"],
        ["ref" => "SEND-260627-R77", "type" => "Send", "user" => "Online Store", "amount" => 40, "currency" => "SGD", "status" => "failed", "date" => "Jun 27, 2026"]
    ];
}

function perahp_default_monthly_report() {
    return [
        ["month" => "Jan", "received" => 42000, "sent" => 18000],
        ["month" => "Feb", "received" => 51000, "sent" => 22000],
        ["month" => "Mar", "received" => 47000, "sent" => 25000],
        ["month" => "Apr", "received" => 69000, "sent" => 28000],
        ["month" => "May", "received" => 61000, "sent" => 31000],
        ["month" => "Jun", "received" => 74930, "sent" => 28440]
    ];
}

function perahp_transaction_type_label($type) {
    $labels = [
        "send" => "Send",
        "receive" => "Receive",
        "request" => "Request",
        "exchange" => "Exchange",
        "cash_in" => "Cash in",
        "cash_out" => "Cash out"
    ];

    return $labels[$type] ?? ucfirst(str_replace("_", " ", (string) $type));
}

function perahp_transaction_counterparty_label($row) {
    if (!empty($row["counterparty_name"])) {
        return $row["counterparty_name"];
    }

    if (!empty($row["counterparty_email"])) {
        return $row["counterparty_email"];
    }

    if (!empty($row["description"])) {
        return $row["description"];
    }

    return $row["transaction_type"] === "exchange" ? "Wallet exchange" : "PeraHP";
}

function perahp_transaction_php_value($transaction, $rates) {
    if (isset($transaction["php_value"]) && $transaction["php_value"] !== null) {
        return (float) $transaction["php_value"];
    }

    $currency = perahp_currency_code($transaction["currency_code"] ?? "PHP");
    $rate = (float) ($rates[$currency] ?? 1);

    return (float) ($transaction["amount"] ?? 0) * $rate;
}

function perahp_user_transactions($userId, $limit = 50) {
    if (!$userId) {
        return perahp_default_transactions();
    }

    $pdo = perahp_db();

    if (!$pdo) {
        return [];
    }

    try {
        $statement = $pdo->prepare(
            "SELECT t.reference_code, t.transaction_type, t.amount, t.currency_code, t.status,
                    t.description, t.created_at, u.full_name AS counterparty_name,
                    u.email AS counterparty_email
             FROM transactions t
             LEFT JOIN users u ON u.id = t.counterparty_user_id
             WHERE t.user_id = :user_id
             ORDER BY t.created_at DESC
             LIMIT :limit_count"
        );
        $statement->bindValue(":user_id", $userId, PDO::PARAM_INT);
        $statement->bindValue(":limit_count", $limit, PDO::PARAM_INT);
        $statement->execute();

        $transactions = [];

        foreach ($statement->fetchAll() as $row) {
            $createdAt = strtotime((string) $row["created_at"]);

            $transactions[] = [
                "ref" => $row["reference_code"],
                "type" => perahp_transaction_type_label($row["transaction_type"]),
                "user" => perahp_transaction_counterparty_label($row),
                "amount" => (float) $row["amount"],
                "currency" => perahp_currency_code($row["currency_code"]),
                "status" => $row["status"],
                "date" => $createdAt ? date("M j, Y", $createdAt) : ""
            ];
        }

        return $transactions;
    } catch (Throwable $exception) {
        error_log("PeraHP transaction lookup failed: " . $exception->getMessage());
        return [];
    }
}

function perahp_month_keys($months = 6) {
    $start = new DateTime("first day of this month");
    $start->modify("-" . max(0, $months - 1) . " months");
    $keys = [];

    for ($i = 0; $i < $months; $i++) {
        $key = $start->format("Y-m");
        $keys[$key] = [
            "month" => $start->format("M"),
            "received" => 0,
            "sent" => 0
        ];
        $start->modify("+1 month");
    }

    return $keys;
}

function perahp_user_monthly_report($userId) {
    if (!$userId) {
        return perahp_default_monthly_report();
    }

    $pdo = perahp_db();

    if (!$pdo) {
        return array_values(perahp_month_keys());
    }

    $report = perahp_month_keys();
    $reportKeys = array_keys($report);
    $firstMonth = $reportKeys[0] ?? date("Y-m");
    $rates = perahp_exchange_rates();

    try {
        $statement = $pdo->prepare(
            "SELECT transaction_type, amount, currency_code, php_value, created_at
             FROM transactions
             WHERE user_id = :user_id
               AND status = 'completed'
               AND created_at >= :start_date
             ORDER BY created_at ASC"
        );
        $statement->execute([
            "user_id" => $userId,
            "start_date" => $firstMonth . "-01 00:00:00"
        ]);

        foreach ($statement->fetchAll() as $row) {
            $createdAt = strtotime((string) $row["created_at"]);
            $key = $createdAt ? date("Y-m", $createdAt) : "";

            if (!isset($report[$key])) {
                continue;
            }

            $value = perahp_transaction_php_value($row, $rates);

            if (in_array($row["transaction_type"], ["receive", "cash_in"], true)) {
                $report[$key]["received"] += $value;
            } elseif (in_array($row["transaction_type"], ["send", "cash_out"], true)) {
                $report[$key]["sent"] += $value;
            }
        }
    } catch (Throwable $exception) {
        error_log("PeraHP monthly report lookup failed: " . $exception->getMessage());
    }

    return array_values($report);
}

function perahp_pending_request_count($userId) {
    if (!$userId) {
        return 1;
    }

    $pdo = perahp_db();

    if (!$pdo) {
        return 0;
    }

    try {
        $statement = $pdo->prepare(
            "SELECT
                (SELECT COUNT(*) FROM payment_requests
                 WHERE (requester_user_id = :requester_id OR payer_user_id = :payer_id)
                   AND status = 'pending')
                +
                (SELECT COUNT(*) FROM transactions
                 WHERE user_id = :transaction_user_id AND transaction_type = 'request' AND status = 'pending')
                AS pending_count"
        );
        $statement->execute([
            "requester_id" => $userId,
            "payer_id" => $userId,
            "transaction_user_id" => $userId
        ]);

        return (int) ($statement->fetch()["pending_count"] ?? 0);
    } catch (Throwable $exception) {
        error_log("PeraHP pending request lookup failed: " . $exception->getMessage());
        return 0;
    }
}

function perahp_transaction_page_data($user) {
    $userId = (int) ($user["id"] ?? 0);

    if ($userId <= 0) {
        return [
            "transactions" => perahp_default_transactions(),
            "monthlyReport" => perahp_default_monthly_report(),
            "pendingCount" => 1,
            "transactionSource" => "demo"
        ];
    }

    return [
        "transactions" => perahp_user_transactions($userId),
        "monthlyReport" => perahp_user_monthly_report($userId),
        "pendingCount" => perahp_pending_request_count($userId),
        "transactionSource" => "database"
    ];
}
