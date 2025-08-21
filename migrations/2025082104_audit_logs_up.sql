CREATE TABLE IF NOT EXISTS __PREFIX__monthly_booking_audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_id BIGINT UNSIGNED NOT NULL,
  action ENUM('assign','unassign','cleaning_complete') NOT NULL,
  room_id BIGINT UNSIGNED NOT NULL,
  campaign_id BIGINT UNSIGNED NULL,
  meta JSON NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY idx_room_created (room_id, created_at),
  KEY idx_actor_created (actor_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
