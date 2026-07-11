USE perahp;

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
