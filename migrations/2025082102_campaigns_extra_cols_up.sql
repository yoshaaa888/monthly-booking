ALTER TABLE __PREFIX__monthly_campaigns
  ADD COLUMN IF NOT EXISTS discount_percent DECIMAL(5,2) NULL,
  ADD COLUMN IF NOT EXISTS period_type ENUM('fixed','checkin_relative','unlimited') DEFAULT 'fixed',
  ADD COLUMN IF NOT EXISTS relative_days INT NULL;
