<?php
require_once __DIR__ . "/auth.php";

function perahp_admin_default_data() {
    return [
        "source" => "demo",
        "stats" => [
            "users" => 2,
            "wallets" => 5,
            "transactions" => 6,
            "pending" => 1
        ],
        "users" => [
            [
                "name" => "Sir Eli",
                "email" => "mareli@perahp.test",
                "role" => "Wallet owner",
                "status" => "Active",
                "created_at" => "January 2026"
            ],
            [
                "name" => "PeraHP Administrator",
                "email" => PERAHP_ADMIN_EMAIL,
                "role" => "Administrator",
                "status" => "Active",
                "created_at" => "January 2026"
            ]
        ],
        "transactions" => [
            [
                "reference" => "PH-DEMO-1001",
                "user" => "Sir Eli",
                "type" => "send",
                "amount" => "PHP 2,500.00",
                "status" => "completed",
                "date" => date("M d, Y")
            ],
            [
                "reference" => "PH-DEMO-1002",
                "user" => "Sir Eli",
                "type" => "exchange",
                "amount" => "USD 75.00",
                "status" => "pending",
                "date" => date("M d, Y", strtotime("-1 day"))
            ]
        ],
        "auditLogs" => [
            [
                "action" => "admin_login_ready",
                "user" => "PeraHP Administrator",
                "details" => "Admin dashboard demo data loaded",
                "date" => date("M d, Y h:i A")
            ],
            [
                "action" => "user_login",
                "user" => "Sir Eli",
                "details" => "Wallet owner session activity",
                "date" => date("M d, Y h:i A", strtotime("-2 hours"))
            ]
        ]
    ];
}

function perahp_admin_dashboard_data() {
    $pdo = perahp_db();

    if (!$pdo) {
        return perahp_admin_default_data();
    }

    try {
        $stats = [
            "users" => (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            "wallets" => (int) $pdo->query("SELECT COUNT(*) FROM wallets")->fetchColumn(),
            "transactions" => (int) $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn(),
            "pending" => (int) $pdo->query("SELECT COUNT(*) FROM transactions WHERE status = 'pending'")->fetchColumn()
        ];

        $userStatement = $pdo->query(
            "SELECT full_name, email, role, status, created_at
             FROM users
             ORDER BY created_at DESC
             LIMIT 8"
        );
        $users = array_map(function($row) {
            return [
                "name" => $row["full_name"],
                "email" => $row["email"],
                "role" => $row["role"] === "admin" ? "Administrator" : "Wallet owner",
                "status" => ucfirst($row["status"]),
                "created_at" => date("M d, Y", strtotime($row["created_at"]))
            ];
        }, $userStatement->fetchAll());

        $transactionStatement = $pdo->query(
            "SELECT t.reference_code, u.full_name, t.transaction_type, t.amount, t.currency_code, t.status, t.created_at
             FROM transactions t
             INNER JOIN users u ON u.id = t.user_id
             ORDER BY t.created_at DESC
             LIMIT 8"
        );
        $transactions = array_map(function($row) {
            return [
                "reference" => $row["reference_code"],
                "user" => $row["full_name"],
                "type" => ucfirst(str_replace("_", " ", $row["transaction_type"])),
                "amount" => $row["currency_code"] . " " . number_format((float) $row["amount"], 2),
                "status" => $row["status"],
                "date" => date("M d, Y", strtotime($row["created_at"]))
            ];
        }, $transactionStatement->fetchAll());

        $auditStatement = $pdo->query(
            "SELECT a.action, COALESCE(u.full_name, 'System') AS user_name, a.entity_type, a.created_at
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC
             LIMIT 8"
        );
        $auditLogs = array_map(function($row) {
            return [
                "action" => $row["action"],
                "user" => $row["user_name"],
                "details" => $row["entity_type"] ?: "System event",
                "date" => date("M d, Y h:i A", strtotime($row["created_at"]))
            ];
        }, $auditStatement->fetchAll());

        return [
            "source" => "database",
            "stats" => $stats,
            "users" => $users,
            "transactions" => $transactions,
            "auditLogs" => $auditLogs
        ];
    } catch (Throwable $exception) {
        error_log("PeraHP admin dashboard failed: " . $exception->getMessage());
        return perahp_admin_default_data();
    }
}

function perahp_admin_complete_transaction($referenceCode, $adminUser) {
    $referenceCode = trim((string) $referenceCode);

    if ($referenceCode === "") {
        return "Select a valid transaction reference.";
    }

    $pdo = perahp_db();

    if (!$pdo) {
        return "Database connection is required to complete a transaction.";
    }

    try {
        $statement = $pdo->prepare(
            "UPDATE transactions
             SET status = 'completed', completed_at = COALESCE(completed_at, NOW())
             WHERE reference_code = :reference_code
               AND status = 'pending'"
        );
        $statement->execute(["reference_code" => $referenceCode]);

        if ($statement->rowCount() === 0) {
            return "Only pending transactions can be marked as completed.";
        }

        $audit = $pdo->prepare(
            "INSERT INTO audit_logs (user_id, action, entity_type, ip_address, details)
             VALUES (:user_id, 'transaction_completed_by_admin', 'transactions', :ip_address, :details)"
        );
        $audit->execute([
            "user_id" => $adminUser["id"] ?? null,
            "ip_address" => $_SERVER["REMOTE_ADDR"] ?? null,
            "details" => json_encode(["reference_code" => $referenceCode])
        ]);

        return "Transaction marked as completed.";
    } catch (Throwable $exception) {
        error_log("PeraHP admin transaction completion failed: " . $exception->getMessage());
        return "Unable to complete the transaction right now.";
    }
}

function perahp_admin_badge_class($status) {
    $status = strtolower((string) $status);
    if ($status === "active" || $status === "completed") {
        return "success";
    }

    if ($status === "pending") {
        return "warning";
    }

    if ($status === "suspended" || $status === "failed" || $status === "cancelled") {
        return "danger";
    }

    return "neutral";
}
?>
