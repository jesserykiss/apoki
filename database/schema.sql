SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(30) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nickname VARCHAR(50) NOT NULL DEFAULT '',
    phone VARCHAR(30) NULL,
    email VARCHAR(190) NULL,
    status TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    KEY idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS relatives (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,
    relation VARCHAR(30) NOT NULL DEFAULT '',
    family_side VARCHAR(20) NOT NULL DEFAULT 'other',
    category VARCHAR(20) NOT NULL DEFAULT 'other',
    gender VARCHAR(10) NOT NULL DEFAULT 'unknown',
    phone VARCHAR(30) NULL,
    remark VARCHAR(500) NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_relatives_tenant_id (user_id, id),
    KEY idx_relatives_user_active_name (user_id, is_deleted, name),
    KEY idx_relatives_user_active_side (user_id, is_deleted, family_side),
    KEY idx_relatives_user_active_category (user_id, is_deleted, category),
    CONSTRAINT fk_relatives_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS banquet_types (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(30) NOT NULL,
    name VARCHAR(50) NOT NULL,
    sort INT NOT NULL DEFAULT 0,
    is_system TINYINT(1) NOT NULL DEFAULT 1,
    user_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_banquet_types_code (code),
    KEY idx_banquet_types_sort (is_system, sort),
    KEY idx_banquet_types_user (user_id),
    CONSTRAINT fk_banquet_types_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS banquets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    type_id BIGINT UNSIGNED NOT NULL,
    host_type TINYINT UNSIGNED NOT NULL,
    host_relative_id BIGINT UNSIGNED NULL,
    title VARCHAR(100) NOT NULL,
    banquet_date DATE NOT NULL,
    location VARCHAR(200) NULL,
    remark VARCHAR(500) NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_banquets_tenant_id (user_id, id),
    KEY idx_banquets_user_active_date (user_id, is_deleted, banquet_date),
    KEY idx_banquets_user_type (user_id, type_id, is_deleted),
    KEY idx_banquets_host_relative (user_id, host_relative_id, is_deleted),
    CONSTRAINT fk_banquets_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_banquets_type
        FOREIGN KEY (type_id) REFERENCES banquet_types (id)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_banquets_host_relative
        FOREIGN KEY (user_id, host_relative_id) REFERENCES relatives (user_id, id)
        ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS gift_records (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    banquet_id BIGINT UNSIGNED NOT NULL,
    relative_id BIGINT UNSIGNED NOT NULL,
    direction TINYINT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    gift_type TINYINT UNSIGNED NOT NULL,
    gift_desc VARCHAR(200) NULL,
    gift_date DATE NOT NULL,
    remark VARCHAR(500) NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_gift_records_tenant_id (user_id, id),
    KEY idx_gifts_user_active_date (user_id, is_deleted, gift_date),
    KEY idx_gifts_user_relative (user_id, relative_id, is_deleted),
    KEY idx_gifts_user_banquet (user_id, banquet_id, is_deleted),
    KEY idx_gifts_user_direction (user_id, direction, is_deleted),
    CONSTRAINT fk_gift_records_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_gift_records_relative
        FOREIGN KEY (user_id, relative_id) REFERENCES relatives (user_id, id)
        ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT fk_gift_records_banquet
        FOREIGN KEY (user_id, banquet_id) REFERENCES banquets (user_id, id)
        ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
