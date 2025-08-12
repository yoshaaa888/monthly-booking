#!/usr/bin/env bash
set -euo pipefail

notice() { echo "::notice::$*"; }
warn()   { echo "::warning::$*"; }
err()    { echo "::error::$*"; }

notice "Starting WordPress bootstrap (wp-env)"

if npx wp-env run cli wp core is-installed; then
  notice "WP already installed"
else
  notice "Installing WP core (dev site)"
  npx wp-env run cli wp core install \
    --url=http://localhost:8888 \
    --title="Monthly Booking E2E" \
    --admin_user=admin --admin_password=admin --admin_email=admin@example.com
fi

npx wp-env run cli wp plugin activate monthly-booking || true
npx wp-env run cli wp rewrite structure '/%postname%/' --hard
npx wp-env run cli wp rewrite flush --hard

PAGE_ID=$(npx wp-env run cli wp post list --post_type=page --name=monthly-calendar --field=ID --format=ids | tr -d '[:space:]' || true)
if [ -z "$PAGE_ID" ] || [ "$PAGE_ID" = "0" ]; then
  notice "Creating monthly-calendar page"
  npx wp-env run cli wp post create \
    --post_type=page --post_status=publish \
    --post_title="Monthly Calendar" \
    --post_name="monthly-calendar" \
    --post_content="[monthly_calendar]"
else
  notice "monthly-calendar exists: ID=${PAGE_ID}"
fi

HOME_URL=$(npx wp-env run cli wp option get home | tr -d '[:space:]' || true)
notice "WP HOME_URL=${HOME_URL}"
