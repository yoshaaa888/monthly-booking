# v1.0.0 Verification Checklist (Text-Only)

Purpose
- Provide a concise, text-based verification reference for Monthly Booking v1.0.0 without screenshots or environment-specific tooling requirements.

Scope
- Admin features relevant to day-to-day operations: Calendar, Rooms, Campaigns, Booking registration, and core i18n.

How to Use
- For each checklist item, mark Pass/Fail and capture brief notes if unexpected results occur. Use any standard WordPress environment (e.g., Local WP) as available; screenshots are not required.

A. Installation and Data
1) Plugin installation
   - Upload and activate monthly-booking-1.0.0.zip
   - Plugins list shows “Monthly Room Booking” version 1.0.0

2) Database seed (optional for demo)
   - Execute dist/sample-data.sql
   - Tables present:
     - wp_monthly_rooms
     - wp_monthly_reservations
     - wp_monthly_campaigns
     - wp_monthly_room_campaigns
   - Note: SQL uses CURDATE(); import near current date to reflect intended states

B. Calendar — Symbols and Interactions
3) Symbols for today reflect seed logic
   - ◎ Vacant + active campaign
   - ○ Vacant (no campaign)
   - ◆ Occupied [checkin, checkout)
   - △ Cleaning buffer [checkout, checkout+5)
   - × Inactive room
   - Precedence: ◆ > △ > ◎ > ○ > ×

4) Right panel updates on cell selection
   - Shows room summary (name/status)
   - Lists active/upcoming campaigns for the selected date
   - Provides shortcuts: キャンペーン紐づけ / 清掃済み切替

5) Row actions operate
   - キャンペーン紐づけ opens assignment modal
   - 清掃済み切替 returns success and calendar reflects updated state (refresh if necessary)

C. Rooms — Operational List
6) Rooms list displays key fields
   - Display name / status / campaign badges (if applicable)
   - Filters or basic batch actions operate without errors

7) Cleaning/vacancy consistency
   - Cleaning buffer respected (5 days after checkout)
   - Inactive rooms do not present as reservable

D. Campaigns — Admin Management
8) List columns and actions
   - Name / Type (icon + label) / Discount (¥ or %) / Period / Linked rooms count / Status / Actions (edit/duplicate/disable)

9) Create/edit form
   - Discount mode: fixed or rate
   - Contract types (SS/S/M/L) multi-select
   - Period type: fixed / check-in-relative / unlimited
   - Preview shows discounted price for sample rent

E. Booking — Admin Workflow
10) Registration and recalculation
   - Price recalculation works; no double-discount
   - Validation prevents overlaps based on [checkin, checkout) logic

F. Internationalization
11) i18n keys render Japanese
   - Menus, labels, and major actions use __()/_e() and internal dictionary entries

G. Security and Robustness
12) Admin-only access
   - Protected pages/screens are inaccessible to non-admins
   - AJAX endpoints enforce nonce and capability checks

13) Basic stability
   - No PHP warnings/notices in error log under normal operations
   - No fatal errors on activation or navigation

Feature Completion Status (v1.0.0)
- Calendar Management View (4-3): Complete (symbols ◎○◆△×, right panel, row actions)
- Campaign List Improvements (4-1): Complete (columns, counts, icons, period types)
- Room List Enhancements (4-2): Complete (badges, filters, bulk ops, vacancy/cleaning)
- Booking Registration (4-4): Complete (admin recalc, double-discount prevention)
- i18n: Major surfaces unified to Japanese keys/dictionary

Known Limitations (Operational Notes)
- Accessibility: Keyboard navigation, aria-live announcements to be expanded post-release
- Performance: At 450-room scale, consider virtualization/pagination and availability caching
- Configurability: Cleaning buffer defaults to 5 days; settings exposure is a planned enhancement
- Sample Data: Uses CURDATE()—for intended demo states, import near current date

References
- docs/INSTALLATION.md
- docs/QUICK_START.md
- GitHub Release v1.0.0 (Monthly Booking v1.0.0 - Fresh Food Time-Sale Management System)
