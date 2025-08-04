
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

INSERT INTO `wp_monthly_options` (`option_name`, `option_description`, `price`, `is_discount_target`, `display_order`, `is_active`) VALUES
('調理器具セット', 'まな板、お玉、フライ返し、包丁、菜箸(2本セット)、片手鍋、フライパン', 6600.00, 1, 1, 1),
('食器類', 'スープ皿、大皿、小皿、茶碗、箸、スプーン、フォーク、コップ2個セット', 3900.00, 1, 2, 1),
('洗剤類', 'トイレットペーパー、ウェットティッシュ、食器洗剤、浴室スポンジ、浴室洗剤、トイレ洗剤、ハンドソープ', 3800.00, 1, 3, 1),
('タオル類', 'フェイスタオル2枚、バスタオル', 2900.00, 1, 4, 1),
('アメニティ類', 'シャンプー、リンス、ボディーソープ', 3500.00, 1, 5, 1),
('寝具カバーセット', '敷パット、掛布団、枕、各カバー', 4530.00, 1, 6, 1),
('毛布', '毛布', 3950.00, 1, 7, 1),
('アイロン', 'アイロン＋アイロン台セット', 6860.00, 0, 8, 1),
('炊飯器（4合炊き）', '炊飯器（4合炊き）※メーカー直送', 6600.00, 0, 9, 1);


INSERT INTO `wp_monthly_customers` (`name`, `email`, `phone`, `address`) VALUES
('田中太郎', 'tanaka@example.com', '090-1234-5678', '東京都新宿区西新宿1-1-1'),
('佐藤花子', 'sato@example.com', '090-2345-6789', '東京都渋谷区恵比寿1-1-1'),
('鈴木一郎', 'suzuki@example.com', '090-3456-7890', '東京都品川区大崎1-1-1');










CREATE INDEX idx_bookings_dates ON `wp_monthly_bookings` (`move_in_date`, `move_out_date`);
CREATE INDEX idx_bookings_status ON `wp_monthly_bookings` (`status`);
CREATE INDEX idx_options_discount ON `wp_monthly_options` (`is_discount_target`);
