<?php
require_once __DIR__ . "/wallet_actions.php";

function perahp_admin_deposit_status($status) {
    $status = strtolower(trim((string) $status));
    $allowed = ["pending", "approved", "rejected", "cancelled", "all"];

    return in_array($status, $allowed, true) ? $status : "pending";
}

function perahp_admin_deposit_requests($status = "pending") {
    $pdo = perahp_db();

    if (!$pdo) {
        return [];
    }

    $status = perahp_admin_deposit_status($status);
    $where = $status === "all" ? "" : "WHERE d.status = :status";

    try {
        $statement = $pdo->prepare(
            "SELECT d.id, d.reference_code, d.amount, d.currency_code, d.php_value,
                    d.proof_reference, d.note, d.status, d.rejection_reason,
                    d.created_at, d.reviewed_at,
                    u.full_name AS user_name, u.email AS user_email,
                    reviewer.full_name AS reviewer_name
             FROM deposit_requests d
             INNER JOIN users u ON u.id = d.user_id
             LEFT JOIN users reviewer ON reviewer.id = d.reviewed_by
             {$where}
             ORDER BY d.created_at DESC
             LIMIT 100"
        );

        if ($status !== "all") {
            $statement->bindValue(":status", $status);
        }

        $statement->execute();
        return $statement->fetchAll();
    } catch (Throwable $exception) {
        error_log("PeraHP admin deposit lookup failed: " . $exception->getMessage());
        return [];
    }
}

function perahp_admin_deposit_counts() {
    $defaults = [
        "pending" => 0,
        "approved" => 0,
        "rejected" => 0,
        "cancelled" => 0,
        "all" => 0
    ];
    $pdo = perahp_db();

    if (!$pdo) {
        return $defaults;
    }

    try {
        $rows = $pdo->query(
            "SELECT status, COUNT(*) AS request_count
             FROM deposit_requests
             GROUP BY status"
        )->fetchAll();

        foreach ($rows as $row) {
            $status = (string) $row["status"];
            $count = (int) $row["request_count"];

            if (isset($defaults[$status])) {
                $defaults[$status] = $count;
            }

            $defaults["all"] += $count;
        }
    } catch (Throwable $exception) {
        error_log("PeraHP admin deposit counts failed: " . $exception->getMessage());
    }

    return $defaults;
}

function perahp_admin_find_deposit_for_update($pdo, $depositId) {
    $statement = $pdo->prepare(
        "SELECT id, reference_code, user_id, wallet_id, transaction_id, amount,
                currency_code, php_value, status
         FROM deposit_requests
         WHERE id = :id
         LIMIT 1
         FOR UPDATE"
    );
    $statement->execute(["id" => $depositId]);
    $deposit = $statement->fetch();

    if (!$deposit) {
        throw new RuntimeException("Deposit request was not found.");
    }

    if ($deposit["status"] !== "pending") {
        throw new RuntimeException("Only pending deposit requests can be reviewed.");
    }

    return $deposit;
}

function perahp_admin_wallet_by_id_for_update($pdo, $walletId) {
    if (!$walletId) {
        return null;
    }

    $statement = $pdo->prepare(
        "SELECT id, user_id, currency_code, balance, status
         FROM wallets
         WHERE id = :wallet_id
         LIMIT 1
         FOR UPDATE"
    );
    $statement->execute(["wallet_id" => $walletId]);

    return $statement->fetch() ?: null;
}

function perahp_admin_approve_deposit($adminUser, $depositId) {
    $adminId = perahp_current_user_id($adminUser);
    $pdo = perahp_db();

    if (!$pdo) {
        throw new RuntimeException("Database connection is not ready.");
    }

    $pdo->beginTransaction();

    try {
        $deposit = perahp_admin_find_deposit_for_update($pdo, $depositId);
        $userId = (int) $deposit["user_id"];
        $currency = perahp_currency_code($deposit["currency_code"]);
        $amount = (float) $deposit["amount"];
        $wallet = perahp_admin_wallet_by_id_for_update($pdo, (int) ($deposit["wallet_id"] ?? 0));

        if (!$wallet || (int) $wallet["user_id"] !== $userId || $wallet["currency_code"] !== $currency || $wallet["status"] !== "active") {
            $wallet = perahp_ensure_active_wallet_for_update($pdo, $userId, $currency);
        }

        perahp_update_wallet_balance($pdo, $wallet["id"], (float) $wallet["balance"] + $amount);

        $updateDeposit = $pdo->prepare(
            "UPDATE deposit_requests
             SET status = 'approved',
                 wallet_id = :wallet_id,
                 reviewed_by = :reviewed_by,
                 reviewed_at = NOW(),
                 rejection_reason = NULL
             WHERE id = :id"
        );
        $updateDeposit->execute([
            "wallet_id" => $wallet["id"],
            "reviewed_by" => $adminId,
            "id" => $deposit["id"]
        ]);

        if (!empty($deposit["transaction_id"])) {
            $updateTransaction = $pdo->prepare(
                "UPDATE transactions
                 SET wallet_id = :wallet_id,
                     status = 'completed',
                     description = 'Deposit approved by admin',
                     completed_at = NOW()
                 WHERE id = :transaction_id"
            );
            $updateTransaction->execute([
                "wallet_id" => $wallet["id"],
                "transaction_id" => $deposit["transaction_id"]
            ]);
        } else {
            $transactionId = perahp_insert_transaction($pdo, [
                "reference_code" => $deposit["reference_code"],
                "user_id" => $userId,
                "counterparty_user_id" => null,
                "wallet_id" => $wallet["id"],
                "transaction_type" => "cash_in",
                "amount" => $amount,
                "currency_code" => $currency,
                "php_value" => $deposit["php_value"],
                "status" => "completed",
                "description" => "Deposit approved by admin"
            ]);

            $linkTransaction = $pdo->prepare(
                "UPDATE deposit_requests
                 SET transaction_id = :transaction_id
                 WHERE id = :id"
            );
            $linkTransaction->execute([
                "transaction_id" => $transactionId,
                "id" => $deposit["id"]
            ]);
        }

        perahp_insert_audit_log($pdo, $adminId, "admin.deposit_approved", "deposit_requests", $deposit["id"], [
            "reference" => $deposit["reference_code"],
            "user_id" => $userId,
            "amount" => $amount,
            "currency" => $currency
        ]);

        $pdo->commit();
        perahp_set_flash("success", "Deposit approved. Reference: " . $deposit["reference_code"]);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function perahp_admin_reject_deposit($adminUser, $depositId, $reason) {
    $adminId = perahp_current_user_id($adminUser);
    $pdo = perahp_db();

    if (!$pdo) {
        throw new RuntimeException("Database connection is not ready.");
    }

    $reason = trim((string) $reason);

    if ($reason === "") {
        $reason = "Rejected by admin.";
    }

    $pdo->beginTransaction();

    try {
        $deposit = perahp_admin_find_deposit_for_update($pdo, $depositId);

        $updateDeposit = $pdo->prepare(
            "UPDATE deposit_requests
             SET status = 'rejected',
                 reviewed_by = :reviewed_by,
                 reviewed_at = NOW(),
                 rejection_reason = :rejection_reason
             WHERE id = :id"
        );
        $updateDeposit->execute([
            "reviewed_by" => $adminId,
            "rejection_reason" => $reason,
            "id" => $deposit["id"]
        ]);

        if (!empty($deposit["transaction_id"])) {
            $updateTransaction = $pdo->prepare(
                "UPDATE transactions
                 SET status = 'failed',
                     description = :description
                 WHERE id = :transaction_id"
            );
            $updateTransaction->execute([
                "description" => "Deposit rejected: " . $reason,
                "transaction_id" => $deposit["transaction_id"]
            ]);
        }

        perahp_insert_audit_log($pdo, $adminId, "admin.deposit_rejected", "deposit_requests", $deposit["id"], [
            "reference" => $deposit["reference_code"],
            "user_id" => (int) $deposit["user_id"],
            "amount" => (float) $deposit["amount"],
            "currency" => $deposit["currency_code"],
            "reason" => $reason
        ]);

        $pdo->commit();
        perahp_set_flash("success", "Deposit rejected. Reference: " . $deposit["reference_code"]);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function perahp_handle_admin_deposit_post($adminUser) {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        return;
    }

    if (!csrf_token_is_valid($_POST["csrf_token"] ?? null)) {
        perahp_set_flash("error", "Your session token expired. Please try again.");
        return;
    }

    $action = (string) ($_POST["action"] ?? "");
    $depositId = (int) ($_POST["deposit_id"] ?? 0);

    if ($depositId <= 0) {
        perahp_set_flash("error", "Choose a valid deposit request.");
        return;
    }

    try {
        if ($action === "approve_deposit") {
            perahp_admin_approve_deposit($adminUser, $depositId);
        } elseif ($action === "reject_deposit") {
            perahp_admin_reject_deposit($adminUser, $depositId, $_POST["rejection_reason"] ?? "");
        } else {
            perahp_set_flash("error", "Unknown admin action.");
        }
    } catch (Throwable $exception) {
        error_log("PeraHP admin deposit action failed: " . $exception->getMessage());
        perahp_set_flash("error", $exception->getMessage());
    }
}
