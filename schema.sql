CREATE DATABASE IF NOT EXISTS perahp
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE perahp;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(40) NULL,
    address VARCHAR(255) NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    status ENUM('active', 'pending', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_status (status),
    KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wallets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    currency_code CHAR(3) NOT NULL,
    balance DECIMAL(18, 2) NOT NULL DEFAULT 0.00,
    status ENUM('active', 'suspended', 'closed') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_wallets_user_currency (user_id, currency_code),
    KEY idx_wallets_currency (currency_code),
    CONSTRAINT fk_wallets_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reference_code VARCHAR(40) NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    counterparty_user_id BIGINT UNSIGNED NULL,
    wallet_id BIGINT UNSIGNED NULL,
    transaction_type ENUM('send', 'receive', 'request', 'exchange', 'cash_in', 'cash_out') NOT NULL,
    amount DECIMAL(18, 2) NOT NULL,
    currency_code CHAR(3) NOT NULL,
    php_value DECIMAL(18, 2) NULL,
    status ENUM('pending', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'pending',
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_transactions_reference (reference_code),
    KEY idx_transactions_user_created (user_id, created_at),
    KEY idx_transactions_status (status),
    KEY idx_transactions_type (transaction_type),
    CONSTRAINT fk_transactions_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_transactions_counterparty
        FOREIGN KEY (counterparty_user_id) REFERENCES users (id)
        ON DELETE SET NULL,
    CONSTRAINT fk_transactions_wallet
        FOREIGN KEY (wallet_id) REFERENCES wallets (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS deposit_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reference_code VARCHAR(40) NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    wallet_id BIGINT UNSIGNED NULL,
    transaction_id BIGINT UNSIGNED NULL,
    amount DECIMAL(18, 2) NOT NULL,
    currency_code CHAR(3) NOT NULL,
    php_value DECIMAL(18, 2) NULL,
    proof_reference VARCHAR(120) NULL,
    note VARCHAR(255) NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at TIMESTAMP NULL,
    rejection_reason VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_deposit_requests_reference (reference_code),
    KEY idx_deposit_requests_user_created (user_id, created_at),
    KEY idx_deposit_requests_status (status),
    KEY idx_deposit_requests_reviewed_by (reviewed_by),
    CONSTRAINT fk_deposit_requests_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_deposit_requests_wallet
        FOREIGN KEY (wallet_id) REFERENCES wallets (id)
        ON DELETE SET NULL,
    CONSTRAINT fk_deposit_requests_transaction
        FOREIGN KEY (transaction_id) REFERENCES transactions (id)
        ON DELETE SET NULL,
    CONSTRAINT fk_deposit_requests_reviewer
        FOREIGN KEY (reviewed_by) REFERENCES users (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reference_code VARCHAR(40) NOT NULL,
    requester_user_id BIGINT UNSIGNED NOT NULL,
    payer_user_id BIGINT UNSIGNED NULL,
    payer_email VARCHAR(190) NOT NULL,
    amount DECIMAL(18, 2) NOT NULL,
    currency_code CHAR(3) NOT NULL,
    status ENUM('pending', 'paid', 'cancelled', 'expired') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_payment_requests_reference (reference_code),
    KEY idx_payment_requests_requester (requester_user_id),
    KEY idx_payment_requests_payer_email (payer_email),
    CONSTRAINT fk_payment_requests_requester
        FOREIGN KEY (requester_user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_payment_requests_payer
        FOREIGN KEY (payer_user_id) REFERENCES users (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exchange_rates (
    currency_code CHAR(3) NOT NULL,
    php_rate DECIMAL(18, 6) NOT NULL,
    source VARCHAR(80) NOT NULL DEFAULT 'manual',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (currency_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS exchange_transactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    reference_code VARCHAR(40) NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    from_wallet_id BIGINT UNSIGNED NOT NULL,
    to_wallet_id BIGINT UNSIGNED NOT NULL,
    from_currency CHAR(3) NOT NULL,
    to_currency CHAR(3) NOT NULL,
    from_amount DECIMAL(18, 2) NOT NULL,
    to_amount DECIMAL(18, 2) NOT NULL,
    from_php_rate DECIMAL(18, 6) NOT NULL,
    to_php_rate DECIMAL(18, 6) NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'completed',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_exchange_transactions_reference (reference_code),
    KEY idx_exchange_transactions_user_created (user_id, created_at),
    CONSTRAINT fk_exchange_transactions_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_exchange_transactions_from_wallet
        FOREIGN KEY (from_wallet_id) REFERENCES wallets (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_exchange_transactions_to_wallet
        FOREIGN KEY (to_wallet_id) REFERENCES wallets (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity_type VARCHAR(60) NULL,
    entity_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    details JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_logs_user_created (user_id, created_at),
    KEY idx_audit_logs_action (action),
    CONSTRAINT fk_audit_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_settings (
    user_id BIGINT UNSIGNED NOT NULL,
    default_currency CHAR(3) NOT NULL DEFAULT 'PHP',
    email_notifications TINYINT(1) NOT NULL DEFAULT 1,
    sms_notifications TINYINT(1) NOT NULL DEFAULT 0,
    two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_user_settings_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO exchange_rates (currency_code, php_rate, source)
VALUES
    ('PHP', 1.000000, 'seed'),
    ('USD', 58.500000, 'seed'),
    ('EUR', 63.200000, 'seed'),
    ('JPY', 0.390000, 'seed'),
    ('SGD', 43.400000, 'seed'),
    ('KRW', 0.040200, 'seed')
ON DUPLICATE KEY UPDATE
    php_rate = VALUES(php_rate),
    source = VALUES(source);

INSERT INTO users (full_name, email, password_hash, phone, address, role, status)
VALUES
    ('PeraHP Administrator', 'admin@perahp.test', '$2y$10$6KxMDJbPMcb8./PJaL7kj.EjZUAz7DVk1ZpF3XHnNo.muz0H95RIm', '+63 917 000 0000', 'PeraHP Operations Center', 'admin', 'active')
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    phone = VALUES(phone),
    address = VALUES(address),
    role = VALUES(role),
    status = VALUES(status);

-- Give the seeded administrator the same related records created for new users.
INSERT INTO wallets (user_id, currency_code, balance, status)
SELECT id, 'PHP', 0.00, 'active'
FROM users
WHERE email = 'admin@perahp.test'
ON DUPLICATE KEY UPDATE
    status = VALUES(status);

INSERT INTO user_settings (user_id, default_currency)
SELECT id, 'PHP'
FROM users
WHERE email = 'admin@perahp.test'
ON DUPLICATE KEY UPDATE
    default_currency = VALUES(default_currency);
