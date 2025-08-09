
CREATE TABLE IF NOT EXISTS wp_monthly_reservations (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    room_id BIGINT UNSIGNED NOT NULL,
    checkin_date DATE NOT NULL,
    checkout_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'confirmed',
    guest_name VARCHAR(190) NOT NULL,
    guest_email VARCHAR(190) NULL,
    base_daily_rate INT NULL,
    total_price INT NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_room_period (room_id, checkin_date),
    KEY idx_room_period2 (room_id, checkout_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



INSERT INTO wp_options (option_name, option_value, autoload) 
VALUES ('monthly_booking_db_version', '1.7.0', 'no')
ON DUPLICATE KEY UPDATE option_value = '1.7.0';
