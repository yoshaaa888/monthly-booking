#!/usr/bin/env bash
set -euo pipefail

echo "::notice::Starting wp-env (with update)"
npx wp-env start --update

echo "::notice::Configure permalinks"
npx wp-env run cli wp rewrite structure '/%postname%/' --hard
npx wp-env run cli wp rewrite flush --hard

echo "::notice::Ensure admin user exists"
if ! npx wp-env run cli wp user get admin >/dev/null 2>&1; then
  npx wp-env run cli wp user create admin admin@example.com --role=administrator --user_pass="password"
fi

echo "::notice::Activate plugin"
npx wp-env run cli wp plugin activate monthly-booking || true

echo "::notice::Ensure monthly-calendar page with correct shortcode"
PAGE_ID=$(npx wp-env run cli wp post list --post_type=page --pagename=monthly-calendar --format=ids | tail -n1 || true)
if [ -z "$PAGE_ID" ]; then
  npx wp-env run cli wp post create --post_type=page --post_status=publish --post_title="Monthly Calendar" --post_name="monthly-calendar" --post_content='[monthly_booking_calendar]'
else
  echo "::notice::monthly-calendar exists: ID=$PAGE_ID"
fi
npx wp-env run cli wp rewrite flush --hard

echo "::notice::Run seed.php and capture output"
npx wp-env run cli wp eval-file wp-content/plugins/monthly-booking/tests/fixtures/seed.php | tee seed.log

echo "::notice::Export BASE_URL"
BASE_URL=$(npx wp-env run cli wp option get siteurl | tr -d '\r')
echo "BASE_URL=${BASE_URL}"
echo "BASE_URL=${BASE_URL}" >> "$GITHUB_ENV"

echo "::notice::Seed tail for grep"
tail -n 50 seed.log || true
