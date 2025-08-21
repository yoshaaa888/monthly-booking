# Deployment Verification Report — v1.0.0

Date
- UTC: [fill-in]
- Operator: Devin AI

Objective
- Verify Monthly Booking v1.0.0 clean install, sample data import, and core calendar operations on a fresh WordPress environment.

Environment
- WordPress: 6.8.2 (wp-now)
- PHP: 8.x (wp-now runtime)
- DB: internal (wp-now)
- Plugin: monthly-booking-1.0.0.zip (release asset)
- Sample data: dist/sample-data.sql (uses CURDATE())

Setup Steps
1) Start clean WordPress (wp-now)
   - scripts/wp-start.sh
   - Health check 200 at / and /wp-json
2) Install plugin
   - Admin → Plugins → Upload → monthly-booking-1.0.0.zip → Activate
   - Confirm plugin version shows 1.0.0
3) Import sample data
   - Using Adminer/phpMyAdmin or WP-CLI equivalent (replace wp_ prefix if needed)
   - Confirm tables:
     - wp_monthly_rooms
     - wp_monthly_reservations
     - wp_monthly_campaigns
     - wp_monthly_room_campaigns

Verification Scenarios
A) Calendar symbol verification (today)
- Expected:
  - R1: ◎ (vacant + active campaign)
  - R2: ○ (vacant, no campaign)
  - R3: ◆ (occupied [checkin, checkout))
  - R4: △ (cleaning [checkout, +5 days))
  - R5: × (inactive)
- Actual:
  - [fill: pass/fail per row]
- Notes:
  - Precedence: ◆ > △ > ◎ > ○ > ×

B) Right panel behavior
- Click on a calendar cell
- Expected:
  - Room summary populated
  - Active/upcoming campaigns listed
  - Shortcuts available: キャンペーン紐づけ / 清掃済み切替
- Actual:
  - [fill]
- Network/Console:
  - [fill logs if errors]

C) Row actions
- From calendar row:
  - [キャンペーン紐づけ] opens assignment modal
  - [清掃済み切替] toggles cleaning; refresh updates symbols
- Expected: Actions succeed with 200 status, UI reflects changes
- Actual:
  - [fill]

Screenshots
- docs/reports/v1.0.0/calendar-symbols.png
- docs/reports/v1.0.0/sidepanel.png
- docs/reports/v1.0.0/row-actions.png
- Note: If not available from headless verification, capture in Local WP as alternates.

Troubleshooting Highlights
- If symbols mismatch:
  - Validate reservation windows [checkin, checkout) and cleaning [checkout, +5]
  - Confirm campaign assignment overlaps “today” and is_active = 1
  - Ensure room is_active for non-× states
- If AJAX nonce error:
  - Re-login admin; check console network responses
- Timezone:
  - Settings → General → Timezone correctness

Outcome
- Result: [Pass/Conditional Pass/Fail]
- Summary:
  - [short summary of results, any workarounds, and next steps]

References
- INSTALLATION.md
- QUICK_START.md
- GitHub Release v1.0.0
