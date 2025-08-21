# Monthly Room Booking v1.0.0 — Installation Manual

This guide explains how to install the Monthly Room Booking WordPress plugin (v1.0.0), set up sample data that demonstrates all calendar symbols (◎, ○, ◆, △, ×), and verify core features.

Contents
- Requirements
- Plugin Installation
- Sample Database Setup (Optional for Demo)
- Feature Overview
- Troubleshooting
- Uninstall/Cleanup Notes

Requirements
- WordPress: 6.0+ (tested)
- PHP: 7.4+ (8.x compatible)
- MySQL: 5.7+ or MariaDB equivalent
- User permission: Administrator (for plugin install and data import)

Plugin Installation
1) Download the plugin ZIP
   - You will receive monthly-booking-1.0.0.zip from the release package.

2) Install the plugin
   - In WordPress admin: Plugins → Add New → Upload Plugin → Choose File (monthly-booking-1.0.0.zip) → Install Now
   - Click Activate

3) Verify activation
   - In Plugins list, confirm “Monthly Room Booking” is active
   - Version shown should be 1.0.0

Sample Database Setup (Optional for Demo)
Use this to quickly demonstrate all calendar symbols (◎, ○, ◆, △, ×). The plugin creates tables automatically on activation; the following SQL inserts sample rows.

Important: The SQL file uses the default wp_ table prefix. If your site uses a different prefix, search/replace wp_ → yourprefix_ before importing.

1) Locate the SQL
   - dist/sample-data.sql in the repository/package

2) Import the SQL
   - Option A: phpMyAdmin → Import → choose sample-data.sql
   - Option B: WP-CLI: wp db import dist/sample-data.sql
   - Option C: MySQL client: mysql -u USER -p DBNAME < dist/sample-data.sql

3) Confirm data
   - Rooms: 5 sample rooms (R1–R5)
   - Reservations: present for R3 and R4 (occupied/cleaning scenarios)
   - Campaigns: an active campaign assigned to R1
   - Room R5 is set inactive

Feature Overview
- Menus
  - ダッシュボード / カレンダー / 部屋一覧 / キャンペーン / 予約 / 料金 / オプション / 設定
- Calendar symbols and precedence
  - ◆ 予約中（occupied）: [checkin, checkout)
  - △ 清掃バッファ（cleaning）: [checkout, checkout+5days)
  - ◎ 空室＋キャンペーン（vacant + active campaign today）
  - ○ 空室（vacant, no campaign）
  - × 利用不可（unavailable, e.g., room inactive）
  - Precedence: ◆ > △ > ◎ > ○ > ×
- Right side panel
  - Shows room summary, active/upcoming campaigns, and quick actions (キャンペーン紐づけ / 清掃済み切替)
- Row actions
  - Each room row has [キャンペーン紐づけ] [清掃済み切替] for quick operations
- Booking registration (admin)
  - Estimate recalculation and prevention of double-discount

Troubleshooting
- No rooms appear in calendar/selector
  - Check wp_monthly_rooms has records; ensure is_active = 1 where necessary
- Symbols don’t match expectations
  - Verify reservation periods (◆ = [checkin, checkout))
  - Verify cleaning periods (△ = [checkout, checkout+5))
  - Confirm campaign assignment period covers the date and is_active = 1
- AJAX nonce or permission errors
  - Re-login as an administrator and retry
- Timezone/date mismatch
  - Ensure WordPress timezone settings match your expectation (Settings → General → Timezone)
- Feature toggles
  - Some reservation features depend on internal feature flags. If a function seems disabled, contact the maintainer to confirm flags.

Uninstall/Cleanup Notes
- Deactivating the plugin preserves data in the database by default
- To fully remove data, drop the custom tables (wp_monthly_*). Backup first
- If you imported sample-data.sql, you can remove seeded rows from:
  - wp_monthly_rooms
  - wp_monthly_reservations
  - wp_monthly_campaigns
  - wp_monthly_room_campaigns
