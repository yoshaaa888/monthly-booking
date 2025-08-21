CREATE TABLE IF NOT EXISTS __PREFIX__monthly_booking_campaign_contract_types (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  campaign_id BIGINT UNSIGNED NOT NULL,
  contract_type ENUM('SS','S','M','L') NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_campaign_contract_type (campaign_id, contract_type),
  KEY idx_campaign (campaign_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE __PREFIX__monthly_booking_campaign_contract_types
  ADD CONSTRAINT fk_mb_cct_campaign
  FOREIGN KEY (campaign_id) REFERENCES __PREFIX__monthly_campaigns(id)
  ON DELETE CASCADE;
