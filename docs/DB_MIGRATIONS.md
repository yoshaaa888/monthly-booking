# Database Migrations

This plugin supports SQL-file based migrations with a PHP runner and WP-CLI.

- Directory: migrations/
- Naming: YYYYMMDDHHMM_{name}_up.sql and matching _down.sql
- Prefix: Use __PREFIX__ placeholder; it will be replaced with $wpdb->prefix.

How to apply
- Admin: On admin_init, when schema version differs, migrations run automatically and record applied files in option monthly_booking_applied_migrations.
- CLI: wp mb migrate
  - Run all pending UP migrations.
  - Flags:
    - --dry-run to print which files would run
    - --down=YYYYMMDDHHMM_name to run a matching _down.sql

Current migrations
- 2025082101_campaign_contract_types: Creates monthly_booking_campaign_contract_types
- 2025082102_campaigns_extra_cols: Adds discount_percent, period_type, relative_days to monthly_campaigns
- 2025082103_room_campaigns_extra_cols: Adds priority/custom_start_date/custom_end_date
- 2025082104_audit_logs: Creates monthly_booking_audit_logs
- 2025082105_data_migrations: Disables auto campaigns, removes their assignments, seeds contract types with 'S'

Docker verification
- make up
- docker compose -f dev/docker-compose.yml run --rm wpcli wp plugin activate monthly-booking
- docker compose -f dev/docker-compose.yml run --rm wpcli wp mb migrate
- docker compose -f dev/docker-compose.yml run --rm wpcli wp db query "SHOW CREATE TABLE wp_monthly_booking_campaign_contract_types\G"
- docker compose -f dev/docker-compose.yml run --rm wpcli wp db query "SHOW COLUMNS FROM wp_monthly_campaigns LIKE 'discount_percent';"
- docker compose -f dev/docker-compose.yml run --rm wpcli wp db query "SHOW COLUMNS FROM wp_monthly_room_campaigns LIKE 'priority';"
- docker compose -f dev/docker-compose.yml run --rm wpcli wp db query "SHOW CREATE TABLE wp_monthly_booking_audit_logs\G"
- docker compose -f dev/docker-compose.yml run --rm wpcli wp db query "SELECT COUNT(*) FROM wp_monthly_campaigns WHERE type IN ('earlybird','immediate') AND is_active=0;"
- docker compose -f dev/docker-compose.yml run --rm wpcli wp db query "SELECT COUNT(*) FROM wp_monthly_booking_campaign_contract_types WHERE contract_type='S';"

Rollback (data only)
- docker compose -f dev/docker-compose.yml run --rm wpcli wp mb migrate --down=2025082105_data_migrations

Notes
- Keep discount_type values as existing strings ('percentage'/'fixed') for runtime stability.
- dbDelta() is used for fresh installs; migrations handle upgrades.
