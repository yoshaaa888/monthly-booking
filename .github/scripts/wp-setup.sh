#!/usr/bin/env bash
set -euo pipefail

echo "Starting wp-env (with update)..."
npx wp-env start --update

echo "Activate plugin just in case..."
npx wp-env run cli wp plugin activate monthly-booking || true

echo "Configure permalinks..."
npx wp-env run cli wp rewrite structure '/%postname%/' || true
npx wp-env run cli wp rewrite flush --hard || true

echo "Ensure calendar page exists at /monthly-calendar/..."
if ! npx wp-env run cli wp post list --post_type=page --name='monthly-calendar' --field=ID | tr -d '\r' | grep -q '^[0-9]\+$'; then
  npx wp-env run cli wp post create --post_type=page --post_title='予約カレンダー' --post_name='monthly-calendar' --post_status=publish --post_content='[monthly_booking_calendar]' || true
else
  PAGE_ID=$(npx wp-env run cli wp post list --post_type=page --name='monthly-calendar' --field=ID | tr -d '\r')
  npx wp-env run cli wp post update "$PAGE_ID" --post_status=publish --post_content='[monthly_booking_calendar]' || true
fi

echo "Seeding via wp-cli (container)..."
npx wp-env run cli wp eval-file wp-content/plugins/monthly-booking/seed.php | tee seed.log

echo "Dumping siteurl..."
BASE_URL=$(npx wp-env run cli wp option get siteurl | tr -d '\r')
echo "BASE_URL=${BASE_URL}"
echo "BASE_URL=${BASE_URL}" >> "$GITHUB_ENV"

echo "Show seed tail (for grep)"
tail -n 50 seed.log || true
