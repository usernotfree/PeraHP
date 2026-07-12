<?php
require_once __DIR__ . "/auth.php";

function perahp_admin_set_flash($type, $message) {
    $_SESSION["perahp_admin_flash"] = [
        "type" => $type,
        "message" => $message
    ];
}

function perahp_admin_take_flash() {
    $flash = $_SESSION["perahp_admin_flash"] ?? null;
    unset($_SESSION["perahp_admin_flash"]);
    return is_array($flash) ? $flash : null;
}

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

function perahp_admin_date_time($value) {
    $time = strtotime((string) $value);
    return $time ? date("M d, Y h:i A", $time) : "-";
}

function perahp_admin_money($amount, $currency = "PHP") {
    return $currency . " " . number_format((float) $amount, 2);
}

function perahp_admin_record_action($adminUser, $action, $entityType, $entityId, $details = []) {
    $pdo = perahp_db();

    if (!$pdo) {
        return;
    }

    try {
        $statement = $pdo->prepare(
            "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, details)
             VALUES (:user_id, :action, :entity_type, :entity_id, :ip_address, :user_agent, :details)"
        );
        $statement->execute([
            "user_id" => $adminUser["id"] ?? null,
            "action" => $action,
            "entity_type" => $entityType,
            "entity_id" => $entityId,
            "ip_address" => $_SERVER["REMOTE_ADDR"] ?? null,
            "user_agent" => substr((string) ($_SERVER["HTTP_USER_AGENT"] ?? ""), 0, 255),
            "details" => json_encode($details)
        ]);
    } catch (Throwable $exception) {
        error_log("PeraHP admin audit insert failed: " . $exception->getMessage());
    }
}

function perahp_admin_user_status_filter($status) {
    $status = strtolower(trim((string) $status));
    $allowed = ["all", "active", "pending", "suspended"];
    return in_array($status, $allowed, true) ? $status : "all";
}

function perahp_admin_user_role_filter($role) {
    $role = strtolower(trim((string) $role));
    $allowed = ["all", "user", "admin"];
    return in_array($role, $allowed, true) ? $role : "all";
}

function perahp_admin_user_counts() {
    $defaults = [
        "total" => 0,
        "active" => 0,
        "pending" => 0,
        "suspended" => 0,
        "admin" => 0
    ];
    $pdo = perahp_db();

    if (!$pdo) {
        return $defaults;
    }

    try {
        $defaults["total"] = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $defaults["admin"] = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        $rows = $pdo->query(
            "SELECT status, COUNT(*) AS user_count
             FROM users
             GROUP BY status"
        )->fetchAll();

        foreach ($rows as $row) {
            $status = (string) $row["status"];

            if (isset($defaults[$status])) {
                $defaults[$status] = (int) $row["user_count"];
            }
        }
    } catch (Throwable $exception) {
        error_log("PeraHP admin user counts failed: " . $exception->getMessage());
    }

    return $defaults;
}

function perahp_admin_users($status = "all", $role = "all", $search = "") {
    $pdo = perahp_db();

    if (!$pdo) {
        return [];
    }

    $status = perahp_admin_user_status_filter($status);
    $role = perahp_admin_user_role_filter($role);
    $search = trim((string) $search);
    $where = [];
    $params = [];

    if ($status !== "all") {
        $where[] = "u.status = :status";
        $params["status"] = $status;
    }

    if ($role !== "all") {
        $where[] = "u.role = :role";
        $params["role"] = $role;
    }

    if ($search !== "") {
        $where[] = "(u.full_name LIKE :search OR u.email LIKE :search)";
        $params["search"] = "%" . $search . "%";
    }

    $whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

    try {
        $statement = $pdo->prepare(
            "SELECT u.id, u.full_name, u.email, u.phone, u.address, u.role, u.status, u.created_at,
                    COUNT(w.id) AS wallet_count,
                    GROUP_CONCAT(CONCAT(w.currency_code, ' ', FORMAT(w.balance, 2)) ORDER BY w.currency_code SEPARATOR ', ') AS wallet_summary
             FROM users u
             LEFT JOIN wallets w ON w.user_id = u.id
             {$whereSql}
             GROUP BY u.id, u.full_name, u.email, u.phone, u.address, u.role, u.status, u.created_at
             ORDER BY u.created_at DESC
             LIMIT 100"
        );
        $statement->execute($params);
        return $statement->fetchAll();
    } catch (Throwable $exception) {
        error_log("PeraHP admin users lookup failed: " . $exception->getMessage());
        return [];
    }
}

function perahp_admin_change_user($adminUser, $targetUserId, $action) {
    $targetUserId = (int) $targetUserId;
    $adminId = (int) ($adminUser["id"] ?? 0);

    if ($targetUserId <= 0) {
        throw new RuntimeException("Choose a valid user.");
    }

    if ($adminId > 0 && $targetUserId === $adminId && in_array($action, ["suspend_user", "make_user"], true)) {
        throw new RuntimeException("You cannot remove your own admin access or suspend your own account.");
    }

    $pdo = perahp_db();

    if (!$pdo) {
        throw new RuntimeException("Database connection is not ready.");
    }

    $allowed = [
        "suspend_user" => ["field" => "status", "value" => "suspended", "audit" => "admin.user_suspended", "message" => "User suspended."],
        "activate_user" => ["field" => "status", "value" => "active", "audit" => "admin.user_activated", "message" => "User activated."],
        "make_admin" => ["field" => "role", "value" => "admin", "audit" => "admin.user_promoted", "message" => "User promoted to admin."],
        "make_user" => ["field" => "role", "value" => "user", "audit" => "admin.user_demoted", "message" => "User changed to wallet owner."]
    ];

    if (!isset($allowed[$action])) {
        throw new RuntimeException("Unknown user management action.");
    }

    $change = $allowed[$action];
    $pdo->beginTransaction();

    try {
        $current = $pdo->prepare(
            "SELECT id, full_name, email, role, status
             FROM users
             WHERE id = :id
             LIMIT 1
             FOR UPDATE"
        );
        $current->execute(["id" => $targetUserId]);
        $target = $current->fetch();

        if (!$target) {
            throw new RuntimeException("User was not found.");
        }

        if ($target["role"] === "admin" && in_array($action, ["suspend_user", "make_user"], true)) {
            $remainingAdmins = $pdo->prepare(
                "SELECT COUNT(*)
                 FROM users
                 WHERE id <> :id
                   AND role = 'admin'
                   AND status = 'active'"
            );
            $remainingAdmins->execute(["id" => $targetUserId]);

            if ((int) $remainingAdmins->fetchColumn() === 0) {
                throw new RuntimeException("At least one active admin account must remain.");
            }
        }

        $statement = $pdo->prepare(
            "UPDATE users
             SET {$change["field"]} = :value
             WHERE id = :id"
        );
        $statement->execute([
            "value" => $change["value"],
            "id" => $targetUserId
        ]);

        perahp_admin_record_action($adminUser, $change["audit"], "users", $targetUserId, [
            "target_email" => $target["email"],
            "previous_role" => $target["role"],
            "previous_status" => $target["status"],
            "new_" . $change["field"] => $change["value"]
        ]);

        $pdo->commit();
        perahp_admin_set_flash("success", $change["message"]);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function perahp_handle_admin_user_post($adminUser) {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        return;
    }

    if (!csrf_token_is_valid($_POST["csrf_token"] ?? null)) {
        perahp_admin_set_flash("error", "Your session token expired. Please try again.");
        return;
    }

    try {
        perahp_admin_change_user($adminUser, $_POST["user_id"] ?? 0, $_POST["action"] ?? "");
    } catch (Throwable $exception) {
        error_log("PeraHP admin user action failed: " . $exception->getMessage());
        perahp_admin_set_flash("error", $exception->getMessage());
    }
}

function perahp_admin_audit_action_filter($action) {
    $action = trim((string) $action);
    return preg_match("/^[a-zA-Z0-9_.-]{1,80}$/", $action) ? $action : "all";
}

function perahp_admin_audit_actions() {
    $pdo = perahp_db();

    if (!$pdo) {
        return [];
    }

    try {
        return array_map(function($row) {
            return $row["action"];
        }, $pdo->query(
            "SELECT DISTINCT action
             FROM audit_logs
             ORDER BY action ASC
             LIMIT 120"
        )->fetchAll());
    } catch (Throwable $exception) {
        error_log("PeraHP audit action lookup failed: " . $exception->getMessage());
        return [];
    }
}

function perahp_admin_audit_counts() {
    $defaults = [
        "total" => 0,
        "today" => 0,
        "admin" => 0,
        "wallet" => 0
    ];
    $pdo = perahp_db();

    if (!$pdo) {
        return $defaults;
    }

    try {
        $defaults["total"] = (int) $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
        $defaults["today"] = (int) $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $defaults["admin"] = (int) $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE action LIKE 'admin.%'")->fetchColumn();
        $defaults["wallet"] = (int) $pdo->query("SELECT COUNT(*) FROM audit_logs WHERE action LIKE 'wallet.%'")->fetchColumn();
    } catch (Throwable $exception) {
        error_log("PeraHP audit counts failed: " . $exception->getMessage());
    }

    return $defaults;
}

function perahp_admin_audit_logs($action = "all", $search = "") {
    $pdo = perahp_db();

    if (!$pdo) {
        return [];
    }

    $action = perahp_admin_audit_action_filter($action);
    $search = trim((string) $search);
    $where = [];
    $params = [];

    if ($action !== "all") {
        $where[] = "a.action = :action";
        $params["action"] = $action;
    }

    if ($search !== "") {
        $where[] = "(u.full_name LIKE :search OR u.email LIKE :search OR a.action LIKE :search OR a.entity_type LIKE :search OR CAST(a.details AS CHAR) LIKE :search)";
        $params["search"] = "%" . $search . "%";
    }

    $whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

    try {
        $statement = $pdo->prepare(
            "SELECT a.id, a.user_id, a.action, a.entity_type, a.entity_id,
                    a.ip_address, a.user_agent, a.details, a.created_at,
                    COALESCE(u.full_name, 'System') AS user_name,
                    u.email AS user_email
             FROM audit_logs a
             LEFT JOIN users u ON u.id = a.user_id
             {$whereSql}
             ORDER BY a.created_at DESC
             LIMIT 150"
        );
        $statement->execute($params);
        return $statement->fetchAll();
    } catch (Throwable $exception) {
        error_log("PeraHP audit log lookup failed: " . $exception->getMessage());
        return [];
    }
}

function perahp_admin_audit_details($details) {
    $decoded = json_decode((string) $details, true);

    if (!is_array($decoded)) {
        return trim((string) $details) !== "" ? (string) $details : "-";
    }

    $parts = [];

    foreach ($decoded as $key => $value) {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $parts[] = $key . ": " . (string) $value;
    }

    return count($parts) ? implode("; ", $parts) : "-";
}
?>
