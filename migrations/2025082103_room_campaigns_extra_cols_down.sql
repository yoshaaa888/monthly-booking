ALTER TABLE __PREFIX__monthly_room_campaigns
  DROP COLUMN IF EXISTS priority,
  DROP COLUMN IF EXISTS custom_start_date,
  DROP COLUMN IF EXISTS custom_end_date;
