/* Monthly Room Booking — Sample Data (uses CURDATE()) */
/* Replace the prefix `wp_` with your actual table prefix if different. */

/* Rooms table (minimal schema for demo) */
CREATE TABLE IF NOT EXISTS `wp_monthly_rooms` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `room_id` INT UNIQUE,
  `property_name` VARCHAR(255) NOT NULL,
  `room_name` VARCHAR(255) NOT NULL,
  `daily_rent` INT NOT NULL DEFAULT 7000,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Reservations table (as used by plugin) */
CREATE TABLE IF NOT EXISTS `wp_monthly_reservations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `room_id` BIGINT UNSIGNED NOT NULL,
  `checkin_date` DATE NOT NULL,
  `checkout_date` DATE NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'confirmed',
  `guest_name` VARCHAR(190) NOT NULL,
  `guest_email` VARCHAR(190) NULL,
  `base_daily_rate` INT NULL,
  `total_price` INT NULL,
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_room_period` (`room_id`, `checkin_date`),
  KEY `idx_room_period2` (`room_id`, `checkout_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Campaigns table (subset of fields used; matches plugin schema) */
CREATE TABLE IF NOT EXISTS `wp_monthly_campaigns` (
  `id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
  `campaign_name` VARCHAR(100) NOT NULL,
  `campaign_description` TEXT,
  `type` VARCHAR(20) DEFAULT NULL,
  `discount_type` VARCHAR(20) NOT NULL,
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_stay_days` INT(3) DEFAULT 1,
  `earlybird_days` INT(3) DEFAULT NULL,
  `max_discount_amount` DECIMAL(10,2),
  `max_discount_days` INT(3) DEFAULT 30,
  `max_stay_days` INT(3) DEFAULT NULL,
  `tax_type` VARCHAR(20) DEFAULT 'taxable',
  `target_plan` VARCHAR(50) DEFAULT 'ALL',
  `applicable_rooms` TEXT,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `booking_start_date` DATE,
  `booking_end_date` DATE,
  `usage_limit` INT(5),
  `usage_count` INT(5) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `period_type` VARCHAR(32) DEFAULT NULL,
  `relative_days` INT(3) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `discount_type` (`discount_type`),
  KEY `type` (`type`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Room-Campaign assignments */
CREATE TABLE IF NOT EXISTS `wp_monthly_room_campaigns` (
  `id` MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
  `room_id` MEDIUMINT(9) NOT NULL,
  `campaign_id` MEDIUMINT(9) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_campaign_period` (`room_id`, `start_date`, `end_date`),
  KEY `room_id` (`room_id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `start_date` (`start_date`),
  KEY `end_date` (`end_date`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/* Seed Rooms (R1–R5) */
DELETE FROM `wp_monthly_rooms`;
INSERT INTO `wp_monthly_rooms` (`room_id`, `property_name`, `room_name`, `daily_rent`, `is_active`) VALUES
(101, 'P-Alpha', 'R1', 8000, 1),  /* ◎ will be shown here */
(102, 'P-Alpha', 'R2', 7500, 1),  /* ○ */
(103, 'P-Beta',  'R3', 8200, 1),  /* ◆ */
(104, 'P-Beta',  'R4', 7000, 1),  /* △ */
(105, 'P-Gamma', 'R5', 7800, 0);  /* × (inactive) */

/* Seed Reservations */
/* R3 occupied today: [today-1, today+3) shows ◆ today */
DELETE FROM `wp_monthly_reservations`;
INSERT INTO `wp_monthly_reservations`
(`room_id`, `checkin_date`, `checkout_date`, `status`, `guest_name`, `guest_email`, `base_daily_rate`, `total_price`, `notes`, `created_at`, `updated_at`) VALUES
(103, DATE_SUB(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'confirmed', 'Guest A', 'a@example.com', 8200, 8200*4, 'Demo occupied stay', NOW(), NOW()),
/* R4 cleaning today: reservation ended today-2 so today ∈ [checkout, checkout+5) */
(104, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'confirmed', 'Guest B', 'b@example.com', 7000, 7000*8, 'Demo cleaning buffer', NOW(), NOW());

/* Seed Campaigns */
/* Active 10% campaign covering today */
DELETE FROM `wp_monthly_campaigns`;
INSERT INTO `wp_monthly_campaigns`
(`campaign_name`, `campaign_description`, `type`, `discount_type`, `discount_value`, `min_stay_days`, `tax_type`, `target_plan`, `start_date`, `end_date`, `is_active`, `period_type`, `relative_days`, `created_at`, `updated_at`)
VALUES
('10% OFF', 'Sample 10 percent off', 'earlybird', 'percentage', 10.00, 1, 'taxable', 'ALL',
 DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 20 DAY), 1, 'fixed', NULL, NOW(), NOW());

/* Assign the campaign to R1 only (◎ when vacant) */
DELETE FROM `wp_monthly_room_campaigns`;
INSERT INTO `wp_monthly_room_campaigns`
(`room_id`, `campaign_id`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`)
SELECT 101, c.id, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 20 DAY), 1, NOW(), NOW()
FROM `wp_monthly_campaigns` c
WHERE c.campaign_name = '10% OFF'
LIMIT 1;

/* Outcome after import (on Calendar for today):
   - R1: ◎ (vacant + assigned active campaign)
   - R2: ○ (vacant, no campaign)
   - R3: ◆ (occupied; today ∈ [checkin, checkout))
   - R4: △ (cleaning; today ∈ [checkout, checkout+5))
   - R5: × (inactive room)
*/
