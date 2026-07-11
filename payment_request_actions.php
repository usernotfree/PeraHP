<?php
require_once __DIR__ . "/wallet_actions.php";

function perahp_payment_request_status($status) {
    $status = strtolower(trim((string) $status));
    $allowed = ["pending", "paid", "cancelled", "expired", "all"];

    return in_array($status, $allowed, true) ? $status : "all";
}

function perahp_payment_requests_for_user($user, $direction = "incoming", $status = "all") {
    $userId = perahp_current_user_id($user);
    $email = (string) ($user["email"] ?? "");

    if ($userId <= 0 || $email === "") {
        return [];
    }

    $pdo = perahp_db();

    if (!$pdo) {
        return [];
    }

    $status = perahp_payment_request_status($status);
    $directionClause = $direction === "sent"
        ? "pr.requester_user_id = :user_id"
        : "(pr.payer_user_id = :user_id OR LOWER(pr.payer_email) = LOWER(:email))";
    $statusClause = $status === "all" ? "" : "AND pr.status = :status";

    try {
        $statement = $pdo->prepare(
            "SELECT pr.id, pr.reference_code, pr.requester_user_id, pr.payer_user_id,
                    pr.payer_email, pr.amount, pr.currency_code, pr.status,
                    pr.created_at, pr.paid_at,
                    requester.full_name AS requester_name,
                    requester.email AS requester_email,
                    payer.full_name AS payer_name,
                    payer.email AS payer_account_email
             FROM payment_requests pr
             INNER JOIN users requester ON requester.id = pr.requester_user_id
             LEFT JOIN users payer ON payer.id = pr.payer_user_id
             WHERE {$directionClause}
               {$statusClause}
             ORDER BY pr.created_at DESC
             LIMIT 100"
        );
        $statement->bindValue(":user_id", $userId, PDO::PARAM_INT);

        if ($direction !== "sent") {
            $statement->bindValue(":email", $email);
        }

        if ($status !== "all") {
            $statement->bindValue(":status", $status);
        }

        $statement->execute();
        return $statement->fetchAll();
    } catch (Throwable $exception) {
        error_log("PeraHP payment request lookup failed: " . $exception->getMessage());
        return [];
    }
}

function perahp_payment_request_counts($user) {
    $incoming = perahp_payment_requests_for_user($user, "incoming", "all");
    $sent = perahp_payment_requests_for_user($user, "sent", "all");

    return [
        "incoming_pending" => count(array_filter($incoming, function($request) {
            return $request["status"] === "pending";
        })),
        "sent_pending" => count(array_filter($sent, function($request) {
            return $request["status"] === "pending";
        })),
        "paid" => count(array_filter(array_merge($incoming, $sent), function($request) {
            return $request["status"] === "paid";
        })),
        "cancelled" => count(array_filter(array_merge($incoming, $sent), function($request) {
            return $request["status"] === "cancelled";
        }))
    ];
}

function perahp_payment_request_for_update($pdo, $requestId, $payerUser) {
    $payerId = perahp_current_user_id($payerUser);
    $payerEmail = (string) ($payerUser["email"] ?? "");

    $statement = $pdo->prepare(
        "SELECT pr.id, pr.reference_code, pr.requester_user_id, pr.payer_user_id,
                pr.payer_email, pr.amount, pr.currency_code, pr.status,
                requester.email AS requester_email
         FROM payment_requests pr
         INNER JOIN users requester ON requester.id = pr.requester_user_id
         WHERE pr.id = :id
         LIMIT 1
         FOR UPDATE"
    );
    $statement->execute(["id" => $requestId]);
    $request = $statement->fetch();

    if (!$request) {
        throw new RuntimeException("Payment request was not found.");
    }

    if ($request["status"] !== "pending") {
        throw new RuntimeException("Only pending payment requests can be reviewed.");
    }

    $payerMatchesById = (int) ($request["payer_user_id"] ?? 0) === $payerId;
    $payerMatchesByEmail = strcasecmp((string) $request["payer_email"], $payerEmail) === 0;

    if (!$payerMatchesById && !$payerMatchesByEmail) {
        throw new RuntimeException("This payment request is not assigned to your account.");
    }

    if ((int) $request["requester_user_id"] === $payerId) {
        throw new RuntimeException("You cannot pay your own payment request.");
    }

    return $request;
}

function perahp_pay_payment_request($payerUser, $requestId) {
    $payerId = perahp_current_user_id($payerUser);

    if ($payerId <= 0) {
        throw new RuntimeException("Use a registered account to pay requests.");
    }

    $pdo = perahp_db();

    if (!$pdo) {
        throw new RuntimeException("Database connection is not ready.");
    }

    $pdo->beginTransaction();

    try {
        $request = perahp_payment_request_for_update($pdo, $requestId, $payerUser);
        $requesterId = (int) $request["requester_user_id"];
        $amount = round((float) $request["amount"], 2);
        $currency = perahp_currency_code($request["currency_code"]);
        $rates = perahp_exchange_rates();
        $phpValue = perahp_money_value_php($amount, $currency, $rates);

        $payerWallet = perahp_active_wallet_for_update($pdo, $payerId, $currency);

        if ((float) $payerWallet["balance"] < $amount) {
            throw new RuntimeException("Insufficient {$currency} balance.");
        }

        $requesterWallet = perahp_ensure_active_wallet_for_update($pdo, $requesterId, $currency);

        perahp_update_wallet_balance($pdo, $payerWallet["id"], (float) $payerWallet["balance"] - $amount);
        perahp_update_wallet_balance($pdo, $requesterWallet["id"], (float) $requesterWallet["balance"] + $amount);

        $sendReference = perahp_unique_reference($pdo, "SEND", "transactions");
        $receiveReference = perahp_unique_reference($pdo, "RCV", "transactions");

        $sendTransactionId = perahp_insert_transaction($pdo, [
            "reference_code" => $sendReference,
            "user_id" => $payerId,
            "counterparty_user_id" => $requesterId,
            "wallet_id" => $payerWallet["id"],
            "transaction_type" => "send",
            "amount" => $amount,
            "currency_code" => $currency,
            "php_value" => $phpValue,
            "status" => "completed",
            "description" => "Paid request " . $request["reference_code"]
        ]);

        perahp_insert_transaction($pdo, [
            "reference_code" => $receiveReference,
            "user_id" => $requesterId,
            "counterparty_user_id" => $payerId,
            "wallet_id" => $requesterWallet["id"],
            "transaction_type" => "receive",
            "amount" => $amount,
            "currency_code" => $currency,
            "php_value" => $phpValue,
            "status" => "completed",
            "description" => "Received payment for request " . $request["reference_code"]
        ]);

        $updateRequest = $pdo->prepare(
            "UPDATE payment_requests
             SET payer_user_id = :payer_user_id,
                 status = 'paid',
                 paid_at = NOW()
             WHERE id = :id"
        );
        $updateRequest->execute([
            "payer_user_id" => $payerId,
            "id" => $request["id"]
        ]);

        $updateRequestTransaction = $pdo->prepare(
            "UPDATE transactions
             SET counterparty_user_id = :payer_user_id,
                 status = 'completed',
                 description = :description,
                 completed_at = NOW()
             WHERE reference_code = :reference_code
               AND user_id = :requester_user_id
               AND transaction_type = 'request'"
        );
        $updateRequestTransaction->execute([
            "payer_user_id" => $payerId,
            "description" => "Request paid by " . $payerUser["email"],
            "reference_code" => $request["reference_code"],
            "requester_user_id" => $requesterId
        ]);

        perahp_insert_audit_log($pdo, $payerId, "wallet.payment_request_paid", "payment_requests", $request["id"], [
            "request_reference" => $request["reference_code"],
            "send_reference" => $sendReference,
            "receive_reference" => $receiveReference,
            "requester_user_id" => $requesterId,
            "amount" => $amount,
            "currency" => $currency
        ]);

        $pdo->commit();
        perahp_set_flash("success", "Payment request paid. Reference: {$sendReference}");
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function perahp_decline_payment_request($payerUser, $requestId) {
    $payerId = perahp_current_user_id($payerUser);

    if ($payerId <= 0) {
        throw new RuntimeException("Use a registered account to decline requests.");
    }

    $pdo = perahp_db();

    if (!$pdo) {
        throw new RuntimeException("Database connection is not ready.");
    }

    $pdo->beginTransaction();

    try {
        $request = perahp_payment_request_for_update($pdo, $requestId, $payerUser);

        $updateRequest = $pdo->prepare(
            "UPDATE payment_requests
             SET payer_user_id = :payer_user_id,
                 status = 'cancelled'
             WHERE id = :id"
        );
        $updateRequest->execute([
            "payer_user_id" => $payerId,
            "id" => $request["id"]
        ]);

        $updateRequestTransaction = $pdo->prepare(
            "UPDATE transactions
             SET counterparty_user_id = :payer_user_id,
                 status = 'cancelled',
                 description = :description
             WHERE reference_code = :reference_code
               AND user_id = :requester_user_id
               AND transaction_type = 'request'"
        );
        $updateRequestTransaction->execute([
            "payer_user_id" => $payerId,
            "description" => "Request declined by " . $payerUser["email"],
            "reference_code" => $request["reference_code"],
            "requester_user_id" => (int) $request["requester_user_id"]
        ]);

        perahp_insert_audit_log($pdo, $payerId, "wallet.payment_request_declined", "payment_requests", $request["id"], [
            "request_reference" => $request["reference_code"],
            "requester_user_id" => (int) $request["requester_user_id"],
            "amount" => (float) $request["amount"],
            "currency" => $request["currency_code"]
        ]);

        $pdo->commit();
        perahp_set_flash("success", "Payment request declined. Reference: " . $request["reference_code"]);
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function perahp_handle_payment_request_response_post($user) {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        return;
    }

    if (!csrf_token_is_valid($_POST["csrf_token"] ?? null)) {
        perahp_set_flash("error", "Your session token expired. Please try again.");
        return;
    }

    $action = (string) ($_POST["action"] ?? "");
    $requestId = (int) ($_POST["request_id"] ?? 0);

    if ($requestId <= 0) {
        perahp_set_flash("error", "Choose a valid payment request.");
        return;
    }

    try {
        if ($action === "pay_payment_request") {
            perahp_pay_payment_request($user, $requestId);
        } elseif ($action === "decline_payment_request") {
            perahp_decline_payment_request($user, $requestId);
        } else {
            perahp_set_flash("error", "Unknown payment request action.");
        }
    } catch (Throwable $exception) {
        error_log("PeraHP payment request action failed: " . $exception->getMessage());
        perahp_set_flash("error", $exception->getMessage());
    }
}
