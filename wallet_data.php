<?php
require_once __DIR__ . "/auth.php";

function perahp_currency_meta($code) {
    $code = perahp_currency_code($code);
    $meta = [
        "PHP" => ["name" => "Philippine Peso", "accent" => "neutral"],
        "USD" => ["name" => "US Dollar", "accent" => "success"],
        "EUR" => ["name" => "Euro", "accent" => "warning"],
        "JPY" => ["name" => "Japanese Yen", "accent" => "neutral"],
        "SGD" => ["name" => "Singapore Dollar", "accent" => "success"]
    ];

    return $meta[$code] ?? ["name" => $code, "accent" => "neutral"];
}

function perahp_currency_code($code) {
    $code = strtoupper(preg_replace("/[^A-Z]/", "", (string) $code));
    return substr($code, 0, 3);
}

function perahp_default_rates() {
    return [
        "PHP" => 1,
        "USD" => 58.5,
        "EUR" => 63.2,
        "JPY" => 0.39,
        "SGD" => 43.4
    ];
}

function perahp_default_wallets() {
    return [
        ["code" => "PHP", "name" => "Philippine Peso", "balance" => 25000, "accent" => "neutral"],
        ["code" => "USD", "name" => "US Dollar", "balance" => 850, "accent" => "success"],
        ["code" => "EUR", "name" => "Euro", "balance" => 320, "accent" => "warning"],
        ["code" => "JPY", "name" => "Japanese Yen", "balance" => 45000, "accent" => "neutral"],
        ["code" => "SGD", "name" => "Singapore Dollar", "balance" => 440, "accent" => "success"]
    ];
}

function perahp_available_currencies($rates = null) {
    $rates = $rates ?: perahp_default_rates();
    $currencies = [];

    foreach ($rates as $code => $rate) {
        $code = perahp_currency_code($code);

        if ($code === "") {
            continue;
        }

        $meta = perahp_currency_meta($code);
        $currencies[] = [
            "code" => $code,
            "name" => $meta["name"],
            "accent" => $meta["accent"],
            "rate" => (float) $rate
        ];
    }

    usort($currencies, function($a, $b) {
        $order = ["PHP" => 0, "USD" => 1, "EUR" => 2, "JPY" => 3, "SGD" => 4];
        return ($order[$a["code"]] ?? 99) <=> ($order[$b["code"]] ?? 99);
    });

    return $currencies;
}

function perahp_exchange_rates() {
    $rates = perahp_default_rates();
    $pdo = perahp_db();

    if (!$pdo) {
        return $rates;
    }

    try {
        $rows = $pdo->query("SELECT currency_code, php_rate FROM exchange_rates")->fetchAll();

        foreach ($rows as $row) {
            $code = perahp_currency_code($row["currency_code"]);

            if ($code !== "") {
                $rates[$code] = (float) $row["php_rate"];
            }
        }
    } catch (Throwable $exception) {
        error_log("PeraHP exchange rate lookup failed: " . $exception->getMessage());
    }

    return $rates;
}

function perahp_user_wallets($userId) {
    if (!$userId) {
        return perahp_default_wallets();
    }

    $pdo = perahp_db();

    if (!$pdo) {
        return [];
    }

    try {
        $statement = $pdo->prepare(
            "SELECT currency_code, balance, status
             FROM wallets
             WHERE user_id = :user_id AND status = 'active'
             ORDER BY FIELD(currency_code, 'PHP', 'USD', 'EUR', 'JPY', 'SGD'), currency_code"
        );
        $statement->execute(["user_id" => $userId]);
        $wallets = [];

        foreach ($statement->fetchAll() as $row) {
            $code = perahp_currency_code($row["currency_code"]);

            if ($code === "") {
                continue;
            }

            $meta = perahp_currency_meta($code);

            $wallets[] = [
                "code" => $code,
                "name" => $meta["name"],
                "balance" => (float) $row["balance"],
                "accent" => $meta["accent"],
                "status" => $row["status"]
            ];
        }

        return $wallets;
    } catch (Throwable $exception) {
        error_log("PeraHP wallet lookup failed: " . $exception->getMessage());
        return [];
    }
}

function perahp_wallet_page_data($user) {
    $userId = (int) ($user["id"] ?? 0);
    $isDatabaseUser = $userId > 0;

    if (!$isDatabaseUser) {
        $rates = perahp_default_rates();
        return [
            "wallets" => perahp_default_wallets(),
            "ratesToPhp" => $rates,
            "currencies" => perahp_available_currencies($rates),
            "walletSource" => "demo",
            "databaseReady" => false
        ];
    }

    $rates = perahp_exchange_rates();

    return [
        "wallets" => perahp_user_wallets($userId),
        "ratesToPhp" => $rates,
        "currencies" => perahp_available_currencies($rates),
        "walletSource" => "database",
        "databaseReady" => perahp_db() !== null
    ];
}

function perahp_json($value) {
    $json = json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    return $json === false ? "{}" : $json;
}
