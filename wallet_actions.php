<?php
require_once __DIR__ . "/transaction_data.php";

function perahp_set_flash($type, $message) {
    $_SESSION["perahp_flash"] = [
        "type" => $type,
        "message" => $message
    ];
}

function perahp_take_flash() {
    $flash = $_SESSION["perahp_flash"] ?? null;
    unset($_SESSION["perahp_flash"]);
    return is_array($flash) ? $flash : null;
}

function perahp_current_user_id($user) {
    return (int) ($user["id"] ?? 0);
}

function perahp_amount_from_post($key) {
    $amount = (float) ($_POST[$key] ?? 0);
    return round($amount, 2);
}

function perahp_unique_reference($pdo, $prefix, $table) {
    $allowedTables = ["transactions", "payment_requests", "exchange_transactions"];

    if (!in_array($table, $allowedTables, true)) {
        throw new RuntimeException("Invalid reference table.");
    }

    $statement = $pdo->prepare("SELECT 1 FROM {$table} WHERE reference_code = :reference_code LIMIT 1");

    for ($i = 0; $i < 8; $i++) {
        $reference = $prefix . "-" . date("ymd") . "-" . strtoupper(bin2hex(random_bytes(2)));
        $statement->execute(["reference_code" => $reference]);

        if (!$statement->fetch()) {
            return $reference;
        }
    }

    throw new RuntimeException("Could not create a unique reference code.");
}

function perahp_rate_or_fail($rates, $currency) {
    if (!isset($rates[$currency]) || (float) $rates[$currency] <= 0) {
        throw new RuntimeException("Exchange rate is missing for {$currency}.");
    }

    return (float) $rates[$currency];
}

function perahp_money_value_php($amount, $currency, $rates) {
    return round($amount * perahp_rate_or_fail($rates, $currency), 2);
}

function perahp_convert_money($amount, $fromCurrency, $toCurrency, $rates) {
    $phpValue = perahp_money_value_php($amount, $fromCurrency, $rates);
    return round($phpValue / perahp_rate_or_fail($rates, $toCurrency), 2);
}

function perahp_wallet_for_update($pdo, $userId, $currency) {
    $statement = $pdo->prepare(
        "SELECT id, user_id, currency_code, balance, status
         FROM wallets
         WHERE user_id = :user_id AND currency_code = :currency_code
         LIMIT 1
         FOR UPDATE"
    );
    $statement->execute([
        "user_id" => $userId,
        "currency_code" => $currency
    ]);

    return $statement->fetch() ?: null;
}

function perahp_active_wallet_for_update($pdo, $userId, $currency) {
    $wallet = perahp_wallet_for_update($pdo, $userId, $currency);

    if (!$wallet || $wallet["status"] !== "active") {
        throw new RuntimeException("Active {$currency} wallet was not found.");
    }

    return $wallet;
}

function perahp_ensure_active_wallet_for_update($pdo, $userId, $currency) {
    $wallet = perahp_wallet_for_update($pdo, $userId, $currency);

    if (!$wallet) {
        $insert = $pdo->prepare(
            "INSERT INTO wallets (user_id, currency_code, balance, status)
             VALUES (:user_id, :currency_code, 0.00, 'active')"
        );
        $insert->execute([
            "user_id" => $userId,
            "currency_code" => $currency
        ]);

        $wallet = perahp_wallet_for_update($pdo, $userId, $currency);
    }

    if (!$wallet || $wallet["status"] !== "active") {
        throw new RuntimeException("Active {$currency} wallet was not found.");
    }

    return $wallet;
}

function perahp_update_wallet_balance($pdo, $walletId, $balance) {
    $statement = $pdo->prepare(
        "UPDATE wallets
         SET balance = :balance
         WHERE id = :wallet_id"
    );
    $statement->execute([
        "balance" => round($balance, 2),
        "wallet_id" => $walletId
    ]);
}

function perahp_insert_transaction($pdo, $data) {
    $statement = $pdo->prepare(
        "INSERT INTO transactions
            (reference_code, user_id, counterparty_user_id, wallet_id, transaction_type,
             amount, currency_code, php_value, status, description, completed_at)
         VALUES
            (:reference_code, :user_id, :counterparty_user_id, :wallet_id, :transaction_type,
             :amount, :currency_code, :php_value, :status, :description, :completed_at)"
    );
    $statement->execute([
        "reference_code" => $data["reference_code"],
        "user_id" => $data["user_id"],
        "counterparty_user_id" => $data["counterparty_user_id"] ?? null,
        "wallet_id" => $data["wallet_id"] ?? null,
        "transaction_type" => $data["transaction_type"],
        "amount" => round((float) $data["amount"], 2),
        "currency_code" => $data["currency_code"],
        "php_value" => round((float) $data["php_value"], 2),
        "status" => $data["status"],
        "description" => $data["description"] ?? null,
        "completed_at" => ($data["status"] ?? "") === "completed" ? date("Y-m-d H:i:s") : null
    ]);

    return (int) $pdo->lastInsertId();
}

function perahp_insert_audit_log($pdo, $userId, $action, $entityType, $entityId, $details = []) {
    $statement = $pdo->prepare(
        "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_address, user_agent, details)
         VALUES (:user_id, :action, :entity_type, :entity_id, :ip_address, :user_agent, :details)"
    );
    $statement->execute([
        "user_id" => $userId,
        "action" => $action,
        "entity_type" => $entityType,
        "entity_id" => $entityId,
        "ip_address" => $_SERVER["REMOTE_ADDR"] ?? null,
        "user_agent" => substr((string) ($_SERVER["HTTP_USER_AGENT"] ?? ""), 0, 255),
        "details" => json_encode($details)
    ]);
}

function perahp_handle_send_money($user) {
    $senderId = perahp_current_user_id($user);

    if ($senderId <= 0) {
        throw new RuntimeException("Use a registered account to send money.");
    }

    $pdo = perahp_db();

    if (!$pdo) {
        throw new RuntimeException("Database connection is not ready.");
    }

    $recipientEmail = trim((string) ($_POST["recipient_email"] ?? ""));
    $amount = perahp_amount_from_post("amount");
    $fromCurrency = perahp_currency_code($_POST["send_from"] ?? "");
    $toCurrency = perahp_currency_code($_POST["send_to"] ?? "");

    if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException("Enter a valid recipient email.");
    }

    if ($amount <= 0) {
        throw new RuntimeException("Enter an amount greater than zero.");
    }

    if ($fromCurrency === "" || $toCurrency === "") {
        throw new RuntimeException("Choose valid currencies.");
    }

    $recipient = find_user_by_email($recipientEmail);

    if (!$recipient || ($recipient["status"] ?? "") !== "active") {
        throw new RuntimeException("Recipient account was not found or is not active.");
    }

    $recipientId = (int) $recipient["id"];

    if ($recipientId === $senderId) {
        throw new RuntimeException("You cannot send money to your own account.");
    }

    $rates = perahp_exchange_rates();
    $phpValue = perahp_money_value_php($amount, $fromCurrency, $rates);
    $recipientAmount = perahp_convert_money($amount, $fromCurrency, $toCurrency, $rates);

    $pdo->beginTransaction();

    try {
        $senderWallet = perahp_active_wallet_for_update($pdo, $senderId, $fromCurrency);

        if ((float) $senderWallet["balance"] < $amount) {
            throw new RuntimeException("Insufficient {$fromCurrency} balance.");
        }

        $recipientWallet = perahp_ensure_active_wallet_for_update($pdo, $recipientId, $toCurrency);

        perahp_update_wallet_balance($pdo, $senderWallet["id"], (float) $senderWallet["balance"] - $amount);
        perahp_update_wallet_balance($pdo, $recipientWallet["id"], (float) $recipientWallet["balance"] + $recipientAmount);

        $sendReference = perahp_unique_reference($pdo, "SEND", "transactions");
        $receiveReference = perahp_unique_reference($pdo, "RCV", "transactions");

        $sendTransactionId = perahp_insert_transaction($pdo, [
            "reference_code" => $sendReference,
            "user_id" => $senderId,
            "counterparty_user_id" => $recipientId,
            "wallet_id" => $senderWallet["id"],
            "transaction_type" => "send",
            "amount" => $amount,
            "currency_code" => $fromCurrency,
            "php_value" => $phpValue,
            "status" => "completed",
            "description" => "Sent to {$recipientEmail}"
        ]);

        perahp_insert_transaction($pdo, [
            "reference_code" => $receiveReference,
            "user_id" => $recipientId,
            "counterparty_user_id" => $senderId,
            "wallet_id" => $recipientWallet["id"],
            "transaction_type" => "receive",
            "amount" => $recipientAmount,
            "currency_code" => $toCurrency,
            "php_value" => $phpValue,
            "status" => "completed",
            "description" => "Received from " . $user["email"]
        ]);

        perahp_insert_audit_log($pdo, $senderId, "wallet.send_money", "transactions", $sendTransactionId, [
            "send_reference" => $sendReference,
            "receive_reference" => $receiveReference,
            "recipient_email" => $recipientEmail,
            "amount" => $amount,
            "from_currency" => $fromCurrency,
            "to_currency" => $toCurrency
        ]);

        $pdo->commit();
        perahp_set_flash("success", "Payment sent. Reference: {$sendReference}");
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function perahp_handle_payment_request($user) {
    $requesterId = perahp_current_user_id($user);

    if ($requesterId <= 0) {
        throw new RuntimeException("Use a registered account to request payment.");
    }

    $pdo = perahp_db();

    if (!$pdo) {
        throw new RuntimeException("Database connection is not ready.");
    }

    $payerEmail = trim((string) ($_POST["payer_email"] ?? ""));
    $amount = perahp_amount_from_post("amount");
    $currency = perahp_currency_code($_POST["request_currency"] ?? "");

    if (!filter_var($payerEmail, FILTER_VALIDATE_EMAIL)) {
        throw new RuntimeException("Enter a valid payer email.");
    }

    if ($amount <= 0) {
        throw new RuntimeException("Enter an amount greater than zero.");
    }

    if ($currency === "") {
        throw new RuntimeException("Choose a valid currency.");
    }

    $rates = perahp_exchange_rates();
    $phpValue = perahp_money_value_php($amount, $currency, $rates);
    $payer = find_user_by_email($payerEmail);
    $payerId = $payer ? (int) $payer["id"] : null;

    if ($payerId === $requesterId) {
        throw new RuntimeException("You cannot request payment from your own account.");
    }

    $pdo->beginTransaction();

    try {
        $reference = perahp_unique_reference($pdo, "REQ", "payment_requests");

        $request = $pdo->prepare(
            "INSERT INTO payment_requests
                (reference_code, requester_user_id, payer_user_id, payer_email, amount, currency_code, status)
             VALUES
                (:reference_code, :requester_user_id, :payer_user_id, :payer_email, :amount, :currency_code, 'pending')"
        );
        $request->execute([
            "reference_code" => $reference,
            "requester_user_id" => $requesterId,
            "payer_user_id" => $payerId,
            "payer_email" => $payerEmail,
            "amount" => $amount,
            "currency_code" => $currency
        ]);
        $paymentRequestId = (int) $pdo->lastInsertId();

        perahp_insert_transaction($pdo, [
            "reference_code" => $reference,
            "user_id" => $requesterId,
            "counterparty_user_id" => $payerId,
            "wallet_id" => null,
            "transaction_type" => "request",
            "amount" => $amount,
            "currency_code" => $currency,
            "php_value" => $phpValue,
            "status" => "pending",
            "description" => "Requested from {$payerEmail}"
        ]);

        perahp_insert_audit_log($pdo, $requesterId, "wallet.request_payment", "payment_requests", $paymentRequestId, [
            "reference" => $reference,
            "payer_email" => $payerEmail,
            "amount" => $amount,
            "currency" => $currency
        ]);

        $pdo->commit();
        perahp_set_flash("success", "Payment request created. Reference: {$reference}");
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function perahp_handle_cash_in($user) {
    $userId = perahp_current_user_id($user);

    if ($userId <= 0) {
        throw new RuntimeException("Use a registered account to cash in.");
    }

    $pdo = perahp_db();

    if (!$pdo) {
        throw new RuntimeException("Database connection is not ready.");
    }

    $amount = perahp_amount_from_post("amount");
    $currency = perahp_currency_code($_POST["cash_in_currency"] ?? "");

    if ($amount <= 0) {
        throw new RuntimeException("Enter an amount greater than zero.");
    }

    if ($currency === "") {
        throw new RuntimeException("Choose a valid currency.");
    }

    $rates = perahp_exchange_rates();
    $phpValue = perahp_money_value_php($amount, $currency, $rates);

    $pdo->beginTransaction();

    try {
        $wallet = perahp_ensure_active_wallet_for_update($pdo, $userId, $currency);
        perahp_update_wallet_balance($pdo, $wallet["id"], (float) $wallet["balance"] + $amount);

        $reference = perahp_unique_reference($pdo, "CASH", "transactions");

        $transactionId = perahp_insert_transaction($pdo, [
            "reference_code" => $reference,
            "user_id" => $userId,
            "counterparty_user_id" => null,
            "wallet_id" => $wallet["id"],
            "transaction_type" => "cash_in",
            "amount" => $amount,
            "currency_code" => $currency,
            "php_value" => $phpValue,
            "status" => "completed",
            "description" => "Instant cash in"
        ]);

        perahp_insert_audit_log($pdo, $userId, "wallet.cash_in", "transactions", $transactionId, [
            "reference" => $reference,
            "amount" => $amount,
            "currency" => $currency
        ]);

        $pdo->commit();
        perahp_set_flash("success", "Cash in complete. Reference: {$reference}");
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function perahp_handle_exchange_funds($user) {
    $userId = perahp_current_user_id($user);

    if ($userId <= 0) {
        throw new RuntimeException("Use a registered account to exchange funds.");
    }

    $pdo = perahp_db();

    if (!$pdo) {
        throw new RuntimeException("Database connection is not ready.");
    }

    $amount = perahp_amount_from_post("amount");
    $fromCurrency = perahp_currency_code($_POST["exchange_from"] ?? "");
    $toCurrency = perahp_currency_code($_POST["exchange_to"] ?? "");

    if ($amount <= 0) {
        throw new RuntimeException("Enter an amount greater than zero.");
    }

    if ($fromCurrency === "" || $toCurrency === "" || $fromCurrency === $toCurrency) {
        throw new RuntimeException("Choose two different valid currencies.");
    }

    $rates = perahp_exchange_rates();
    $fromRate = perahp_rate_or_fail($rates, $fromCurrency);
    $toRate = perahp_rate_or_fail($rates, $toCurrency);
    $phpValue = round($amount * $fromRate, 2);
    $convertedAmount = round($phpValue / $toRate, 2);

    $pdo->beginTransaction();

    try {
        $fromWallet = perahp_active_wallet_for_update($pdo, $userId, $fromCurrency);

        if ((float) $fromWallet["balance"] < $amount) {
            throw new RuntimeException("Insufficient {$fromCurrency} balance.");
        }

        $toWallet = perahp_ensure_active_wallet_for_update($pdo, $userId, $toCurrency);
        perahp_update_wallet_balance($pdo, $fromWallet["id"], (float) $fromWallet["balance"] - $amount);
        perahp_update_wallet_balance($pdo, $toWallet["id"], (float) $toWallet["balance"] + $convertedAmount);

        $reference = perahp_unique_reference($pdo, "EXCH", "exchange_transactions");

        $exchange = $pdo->prepare(
            "INSERT INTO exchange_transactions
                (reference_code, user_id, from_wallet_id, to_wallet_id, from_currency, to_currency,
                 from_amount, to_amount, from_php_rate, to_php_rate, status)
             VALUES
                (:reference_code, :user_id, :from_wallet_id, :to_wallet_id, :from_currency, :to_currency,
                 :from_amount, :to_amount, :from_php_rate, :to_php_rate, 'completed')"
        );
        $exchange->execute([
            "reference_code" => $reference,
            "user_id" => $userId,
            "from_wallet_id" => $fromWallet["id"],
            "to_wallet_id" => $toWallet["id"],
            "from_currency" => $fromCurrency,
            "to_currency" => $toCurrency,
            "from_amount" => $amount,
            "to_amount" => $convertedAmount,
            "from_php_rate" => $fromRate,
            "to_php_rate" => $toRate
        ]);
        $exchangeId = (int) $pdo->lastInsertId();

        perahp_insert_transaction($pdo, [
            "reference_code" => $reference,
            "user_id" => $userId,
            "counterparty_user_id" => null,
            "wallet_id" => $fromWallet["id"],
            "transaction_type" => "exchange",
            "amount" => $amount,
            "currency_code" => $fromCurrency,
            "php_value" => $phpValue,
            "status" => "completed",
            "description" => "Exchanged {$fromCurrency} to {$toCurrency}"
        ]);

        perahp_insert_audit_log($pdo, $userId, "wallet.exchange_funds", "exchange_transactions", $exchangeId, [
            "reference" => $reference,
            "from_currency" => $fromCurrency,
            "to_currency" => $toCurrency,
            "from_amount" => $amount,
            "to_amount" => $convertedAmount
        ]);

        $pdo->commit();
        perahp_set_flash("success", "Exchange complete. Reference: {$reference}");
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function perahp_handle_wallet_post($user, $allowedActions) {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        return;
    }

    $action = (string) ($_POST["action"] ?? "");

    if (!csrf_token_is_valid($_POST["csrf_token"] ?? null)) {
        perahp_set_flash("error", "Your session token expired. Please try again.");
        return;
    }

    if (!in_array($action, $allowedActions, true)) {
        perahp_set_flash("error", "Unknown wallet action.");
        return;
    }

    try {
        if ($action === "send_money") {
            perahp_handle_send_money($user);
        } elseif ($action === "request_payment") {
            perahp_handle_payment_request($user);
        } elseif ($action === "cash_in") {
            perahp_handle_cash_in($user);
        } elseif ($action === "exchange_funds") {
            perahp_handle_exchange_funds($user);
        }
    } catch (Throwable $exception) {
        error_log("PeraHP wallet action failed: " . $exception->getMessage());
        perahp_set_flash("error", $exception->getMessage());
    }
}
