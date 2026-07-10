<?php
function perahp_db_config() {
    return [
        "host" => getenv("PERAHP_DB_HOST") ?: "localhost",
        "name" => getenv("PERAHP_DB_NAME") ?: "perahp",
        "user" => getenv("PERAHP_DB_USER") ?: "root",
        "pass" => getenv("PERAHP_DB_PASS") ?: "",
        "charset" => getenv("PERAHP_DB_CHARSET") ?: "utf8mb4"
    ];
}

function perahp_db() {
    static $pdo = null;
    static $failed = false;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if ($failed) {
        return null;
    }

    $config = perahp_db_config();
    $dsn = "mysql:host={$config["host"]};dbname={$config["name"]};charset={$config["charset"]}";

    try {
        $pdo = new PDO($dsn, $config["user"], $config["pass"], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);

        return $pdo;
    } catch (Throwable $exception) {
        $failed = true;
        error_log("PeraHP database connection failed: " . $exception->getMessage());
        return null;
    }
}
