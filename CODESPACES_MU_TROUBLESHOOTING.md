# Codespaces wp-now MU Loader + Admin Assets Troubleshooting

Use these exact steps if the WordPress admin looks unstyled or the MU headers/body are missing in Codespaces with wp-now 6.8.2.

Tunnel URL example:
export BASE="https://stunning-space-system-gp6g6w499qqf9pg6-8881.app.github.dev"

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

## 3) Verify admin assets (curl may 302; confirm 200 in Browser Network)

curl -I "$BASE/wp-admin/load-styles.php" || true
curl -I "$BASE/wp-admin/load-scripts.php" || true

Expected:
- In Browser Network: both endpoints are 200 and admin is fully styled.

## 4) MU loader verification

Headers:
curl -s -D - "$BASE/?mb_ping=1" -o /dev/null | grep -i -E 'X-MB-(Ping|Test)' || echo "no headers"

Body:
curl -s "$BASE/?mb_ping=1" | head -1

Marker:
curl -s "$BASE/" | grep 'MB_MU_OK' || true

PRESENT MU OK when:
- Headers include: X-MB-Ping: 1 and X-MB-Test: mb=ok_project
- Body first line: mb=ok_project
- Homepage contains: <!-- MB_MU_OK -->

## 5) If still failing

- Ensure loaders exist and immediately include project MU:
  - ~/.wp-now/wordpress-versions/6.8.2/wp-content/mu-plugins/mb-loader.php
  - ~/.wp-now/mu-plugins/mb-loader.php
  - Project MU path: /workspaces/monthly-booking/wp-content/mu-plugins

- Check logs:
  tail -n 200 /tmp/wp-now.log

- Confirm 8881 port is Public in Codespaces Ports panel.
- Re-run the home/siteurl updates and hard reload the browser (Cmd/Ctrl+Shift+R).
- If /wp-json curls to 302 (pf-signin), ignore for curl; verify Browser Network is 200.

## 6) UI verification targets (PR #60)

- Calendar: $BASE/wp-admin/admin.php?page=monthly-room-booking-calendar
  - Weekdays across columns, dates down rows; buttons 予約登録/編集/削除 per cell.
  - Register a booking → Save → list reflects.

- Fees: $BASE/wp-admin/admin.php?page=monthly-room-booking-options
  - New → Save → list reflects.

- Campaigns: $BASE/wp-admin/admin.php?page=monthly-campaign-manager
  - New → Save → list reflects. Capture one HAR of create flow.

Artifacts to submit:
1) /?mb_ping=1 headers (X-MB-Ping/X-MB-Test) and body first line (mb=ok_project)
2) Network screenshots showing 200 for load-styles.php and load-scripts.php (with headers)
3) /tmp/wp-now.log last 50 lines
Then:
- Calendar registration screenshot, Fees create screenshot, one Campaign create HAR + success notice screenshot.
