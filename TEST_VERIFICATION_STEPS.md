# Monthly Booking – Codespaces (wp-now) Verification Steps

This document describes the exact steps and success criteria to verify MU plugin loading in wp-now (Codespaces) and to validate the admin UI changes in PR #60.

Base tunnel URL (example):
- export BASE="https://stunning-space-system-gp6g6w499qqf9pg6-8881.app.github.dev"

Note:
- Use Ports panel to ensure the port is Public.
- If curl to /wp-json shows 302 to github.dev/pf-signin, it is expected via curl; use Browser Network to confirm 200.

## 1) Start wp-now with plugin auto-activation

pkill -f "@wp-now/wp-now" 2>/dev/null || true
nohup npx -y @wp-now/wp-now@latest start --wp=6.8.2 --skip-browser --port=8881 --plugin . > /tmp/wp-now.log 2>&1 &
sleep 5
echo "BASE=$BASE"
echo "LOG_URL=$(grep -o 'https://[^ ]*app.github.dev' /tmp/wp-now.log | tail -1)"

## 2) Fix WordPress URLs and admin settings

npx -y @wp-now/wp-now@latest cli -- wp option update home "$BASE"
npx -y @wp-now/wp-now@latest cli -- wp option update siteurl "$BASE"
npx -y @wp-now/wp-now@latest cli -- wp config set FORCE_SSL_ADMIN true --type=constant --raw
npx -y @wp-now/wp-now@latest cli -- wp config set CONCATENATE_SCRIPTS false --type=constant --raw
npx -y @wp-now/wp-now@latest cli -- wp config set WP_DEBUG_LOG true --type=constant --raw
npx -y @wp-now/wp-now@latest cli -- wp config set WP_MEMORY_LIMIT 256M --type=constant
npx -y @wp-now/wp-now@latest cli -- wp option update timezone_string "Asia/Tokyo"
npx -y @wp-now/wp-now@latest cli -- wp rewrite structure '/%postname%/'
npx -y @wp-now/wp-now@latest cli -- wp rewrite flush --hard

## 3) Verify admin assets (accept 302 in curl; confirm 200 in Browser Network)

curl -I "$BASE/wp-admin/load-styles.php" || true
curl -I "$BASE/wp-admin/load-scripts.php" || true

Success criteria:
- In the browser Network panel, load-styles.php and load-scripts.php are 200 and styled admin UI is visible.

## 4) MU loader verification

Headers (expect X-MB-Ping: 1 and X-MB-Test: mb=ok_project):
curl -s -D - "$BASE/?mb_ping=1" -o /dev/null | grep -i -E 'X-MB-(Ping|Test)' || echo "no headers"

Body (expect first line mb=ok_project):
curl -s "$BASE/?mb_ping=1" | head -1

Marker (expect on homepage):
curl -s "$BASE/" | grep 'MB_MU_OK' || true

Logs (collect last 50 lines for submission):
tail -n 50 /tmp/wp-now.log

PRESENT MU OK when:
- X-MB-Ping: 1 and X-MB-Test: mb=ok_project appear in headers
- Body shows: mb=ok_project
- Homepage includes: <!-- MB_MU_OK -->

## 5) Admin UI verification (PR #60)

Pages:
- Calendar: $BASE/wp-admin/admin.php?page=monthly-room-booking-calendar
- Fees (Options): $BASE/wp-admin/admin.php?page=monthly-room-booking-options
- Campaigns: $BASE/wp-admin/admin.php?page=monthly-campaign-manager

5.1) Reservation Calendar
- Verify layout: weekdays across columns, dates down rows.
- Each date cell shows buttons: 予約登録, 編集, 削除.
- Action: pick a date cell → 予約登録 → 保存 → list updated.
- Capture one screenshot showing success/list update.

5.2) Fees (Options)
- New → 保存 → list updated.
- Capture one screenshot.

5.3) Campaigns
- New → 保存 → list updated.
- Capture one HAR (Network) for the create flow and one success notice screenshot.

If you see 403 on CRUD:
- Re-login at /wp-admin/ in a browser tab and retry.

## 6) Deliverables (Phase 1 first, then UI)

Phase 1 (submit first):
1) /?mb_ping=1
   - Header extract showing X-MB-Ping and X-MB-Test
   - Body first line: mb=ok_project
2) Browser Network screenshots (with headers) for:
   - load-styles.php (200)
   - load-scripts.php (200)
3) /tmp/wp-now.log last 50 lines

Phase 2 (UI evidence):
- Calendar registration flow screenshot (登録→保存→一覧反映)
- Fees new→save→list screenshot
- Campaigns: one HAR for create flow, plus success notice screenshot

## 7) Diagnostics if failing

- If MU headers missing:
  - Verify loaders exist at:
    - ~/.wp-now/wordpress-versions/6.8.2/wp-content/mu-plugins/mb-loader.php
    - ~/.wp-now/mu-plugins/mb-loader.php
  - Project MU path: /workspaces/monthly-booking/wp-content/mu-plugins
  - Check /tmp/wp-now.log for “[MB Project MU Loader] loaded …” or “… not found …”
  - Ensure BASE matches the latest LOG_URL printed from /tmp/wp-now.log

- If admin unstyled:
  - Re-run wp option update home/siteurl "$BASE"
  - Hard reload (Ctrl/Cmd+Shift+R)
  - Ensure FORCE_SSL_ADMIN true and port 8881 is Public

## 8) Notes

- The MU loader immediately includes project MU plugins and logs a line indicating whether files were loaded.
- The PR #60 contains calendar reorientation and CRUD improvements; verify these after MU OK.
- Attach all artifacts to PR #60 and mark “Ready for review”.
