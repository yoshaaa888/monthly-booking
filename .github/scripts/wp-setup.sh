#!/usr/bin/env bash
set -euo pipefail

CAL_URL_DEFAULT="http://localhost:8888"
CAL_URL="${CAL_URL:-$CAL_URL_DEFAULT}"

echo "Starting wp-env..."
npx wp-env start

echo "Waiting for WordPress to be available at ${CAL_URL} ..."
for i in {1..12}; do
  if curl -fsSL "${CAL_URL}" >/dev/null 2>&1; then
    echo "WordPress is up."
    break
  fi
  sleep 5
done

echo "Activating plugin and configuring permalinks..."
npx wp-env run cli wp plugin activate monthly-booking || true
npx wp-env run cli wp rewrite structure '/%postname%/'
npx wp-env run cli wp rewrite flush --hard

echo "Creating calendar page if missing..."
if ! npx wp-env run cli wp post list --post_type=page --field=post_name | tr -d '\r' | grep -q '^monthly-calendar$'; then
  npx wp-env run cli wp post create --post_type=page --post_title='予約カレンダー' --post_name='monthly-calendar' --post_status=publish --post_content='[monthly_booking_calendar]'
fi

echo "Seeding database..."
SEED_OUTPUT="$(npx wp-env run cli php /var/www/html/wp-content/plugins/monthly-booking/tests/fixtures/seed.php || true)"
echo "${SEED_OUTPUT}"
echo "${SEED_OUTPUT}" | tee seed.log
if ! echo "${SEED_OUTPUT}" | grep -q 'RES_COUNT=6'; then
  echo "Seed did not report RES_COUNT=6; continuing but marking for investigation."
fi

echo "Warming calendar endpoint and checking for DB errors..."
curl -fsSL "${CAL_URL}/monthly-calendar/" | tee calendar.html
if grep -qi 'wpdberror' calendar.html; then
  echo "Detected WPDB error on calendar page."
  exit 1
fi

echo "Bootstrap complete."
