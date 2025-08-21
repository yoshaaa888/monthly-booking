ALTER TABLE __PREFIX__monthly_campaigns
  DROP COLUMN IF EXISTS discount_percent,
  DROP COLUMN IF EXISTS period_type,
  DROP COLUMN IF EXISTS relative_days;
