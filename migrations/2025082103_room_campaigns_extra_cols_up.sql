ALTER TABLE __PREFIX__monthly_room_campaigns
  ADD COLUMN IF NOT EXISTS priority INT DEFAULT 1,
  ADD COLUMN IF NOT EXISTS custom_start_date DATE NULL,
  ADD COLUMN IF NOT EXISTS custom_end_date DATE NULL;
