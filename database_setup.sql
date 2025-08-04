
CREATE TABLE IF NOT EXISTS `wp_monthly_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_name` varchar(255) NOT NULL,
  `room_name` varchar(255) NOT NULL,
  `daily_rent` decimal(10,2) NOT NULL DEFAULT 3000.00,
  `daily_utilities` decimal(10,2) NOT NULL DEFAULT 2500.00,
  `cleaning_fee` decimal(10,2) NOT NULL DEFAULT 38500.00,
  `key_fee` decimal(10,2) NOT NULL DEFAULT 11000.00,
  `bedding_fee` decimal(10,2) NOT NULL DEFAULT 11000.00,
  `max_occupancy` int(11) NOT NULL DEFAULT 2,
  `address` text,
  `station_access` text,
  `amenities` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `wp_monthly_options` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `option_name` varchar(100) NOT NULL,
  `option_description` text,
  `price` decimal(10,2) NOT NULL,
  `is_discount_target` tinyint(1) DEFAULT 1,
  `display_order` int(3) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `wp_monthly_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `discount_rate` decimal(5,2) NOT NULL,
  `condition_type` varchar(50) NOT NULL,
  `condition_value` int(11) NOT NULL,
  `badge_text` varchar(50),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `wp_monthly_customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50),
  `address` text,
  `emergency_contact` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `wp_monthly_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `move_in_date` date NOT NULL,
  `move_out_date` date NOT NULL,
  `num_adults` int(11) NOT NULL DEFAULT 1,
  `num_children` int(11) NOT NULL DEFAULT 0,
  `plan_type` varchar(10) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `non_taxable_amount` decimal(10,2) NOT NULL DEFAULT 0,
  `taxable_amount` decimal(10,2) NOT NULL DEFAULT 0,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0,
  `campaign_discount` decimal(10,2) NOT NULL DEFAULT 0,
  `options_discount` decimal(10,2) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `room_id` (`room_id`),
  KEY `move_in_date` (`move_in_date`),
  KEY `move_out_date` (`move_out_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `wp_monthly_booking_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `option_id` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO `wp_monthly_rooms` (`property_name`, `room_name`, `daily_rent`, `daily_utilities`, `cleaning_fee`, `key_fee`, `bedding_fee`, `max_occupancy`, `address`, `station_access`) VALUES
('立川マンスリー', 'Room A-101', 3000.00, 2500.00, 38500.00, 11000.00, 11000.00, 2, '東京都立川市曙町2-1-1', 'JR立川駅徒歩5分'),
('新宿マンスリー', 'Room B-201', 3500.00, 2500.00, 38500.00, 11000.00, 11000.00, 2, '東京都新宿区新宿3-1-1', 'JR新宿駅徒歩3分'),
('渋谷マンスリー', 'Room C-301', 4000.00, 2500.00, 38500.00, 11000.00, 11000.00, 3, '東京都渋谷区渋谷1-1-1', 'JR渋谷駅徒歩2分'),
('池袋マンスリー', 'Room D-401', 3200.00, 2500.00, 38500.00, 11000.00, 11000.00, 2, '東京都豊島区池袋2-1-1', 'JR池袋駅徒歩4分'),
('品川マンスリー', 'Room E-501', 3800.00, 2500.00, 38500.00, 11000.00, 11000.00, 2, '東京都港区港南2-1-1', 'JR品川駅徒歩6分');

INSERT INTO `wp_monthly_options` (`option_name`, `price`, `is_discount_target`, `option_description`, `display_order`, `is_active`) VALUES
('調理器具セット', 3000.00, 1, '基本的な調理器具一式', 1, 1),
('食器セット', 2000.00, 1, '食器・カトラリー一式', 2, 1),
('タオルセット', 1500.00, 1, 'バスタオル・フェイスタオル', 3, 1),
('シーツセット', 1000.00, 1, 'ベッドシーツ・枕カバー', 4, 1),
('枕セット', 800.00, 1, '枕2個セット', 5, 1),
('ハンガーセット', 600.00, 1, 'ハンガー10本セット', 6, 1),
('洗剤セット', 500.00, 1, '洗濯洗剤・食器洗剤', 7, 1),
('Wi-Fi', 5000.00, 0, '高速インターネット接続', 8, 1),
('駐車場', 8000.00, 0, '専用駐車場1台分', 9, 1);

INSERT INTO `wp_monthly_campaigns` (`name`, `discount_rate`, `condition_type`, `condition_value`, `badge_text`, `is_active`) VALUES
('早割キャンペーン', 10.00, 'advance_days', 30, '早割', 1),
('即入居割', 20.00, 'immediate_days', 7, '即入居', 1);

INSERT INTO `wp_monthly_customers` (`name`, `email`, `phone`, `address`) VALUES
('田中太郎', 'tanaka@example.com', '090-1234-5678', '東京都新宿区西新宿1-1-1'),
('佐藤花子', 'sato@example.com', '090-2345-6789', '東京都渋谷区恵比寿1-1-1'),
('鈴木一郎', 'suzuki@example.com', '090-3456-7890', '東京都品川区大崎1-1-1');


CREATE INDEX idx_bookings_dates ON `wp_monthly_bookings` (`move_in_date`, `move_out_date`);
CREATE INDEX idx_bookings_status ON `wp_monthly_bookings` (`status`);
CREATE INDEX idx_options_discount ON `wp_monthly_options` (`is_discount_target`);
CREATE INDEX idx_campaigns_active ON `wp_monthly_campaigns` (`is_active`);
