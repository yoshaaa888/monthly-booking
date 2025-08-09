
CREATE TABLE IF NOT EXISTS wp_monthly_reservations (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    room_id mediumint(9) NOT NULL,
    customer_name varchar(100) NOT NULL,
    customer_email varchar(100) NOT NULL,
    customer_phone varchar(20),
    checkin_date date NOT NULL,
    checkout_date date NOT NULL,
    num_adults int(2) DEFAULT 1,
    num_children int(2) DEFAULT 0,
    base_price decimal(10,2) NOT NULL,
    total_price decimal(10,2) NOT NULL,
    status enum('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    notes text,
    created_by mediumint(9) NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY room_id (room_id),
    KEY checkin_date (checkin_date),
    KEY checkout_date (checkout_date),
    KEY status (status),
    KEY created_by (created_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX IF NOT EXISTS idx_reservation_dates ON wp_monthly_reservations (room_id, checkin_date, checkout_date);
CREATE INDEX IF NOT EXISTS idx_reservation_status ON wp_monthly_reservations (status, created_at);



INSERT INTO wp_options (option_name, option_value, autoload) 
VALUES ('monthly_booking_db_version', '1.7.0', 'no')
ON DUPLICATE KEY UPDATE option_value = '1.7.0';
