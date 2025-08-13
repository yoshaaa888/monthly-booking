#!/usr/bin/env bash
set -euo pipefail

: "${BASE_URL:?BASE_URL is required}"
: "${WP_ADMIN_USER:?WP_ADMIN_USER is required}"
: "${WP_ADMIN_PASS:?WP_ADMIN_PASS is required}"

if ! command -v wp >/dev/null 2>&1; then
  echo "::error::wp CLI not found on PATH"
  exit 127
fi

echo "::notice::Setting home/siteurl to ${BASE_URL}"
wp option update home "$BASE_URL" || true
wp option update siteurl "$BASE_URL" || true

echo "::notice::Activating plugin monthly-booking (idempotent)"
wp plugin activate monthly-booking || true

echo "::notice::Ensuring admin user ${WP_ADMIN_USER} exists"
if ! wp user get "$WP_ADMIN_USER" >/dev/null 2>&1; then
  wp user create "$WP_ADMIN_USER" "$WP_ADMIN_USER@example.com" --role=administrator --user_pass="$WP_ADMIN_PASS"
fi

echo "::notice::Seeding test data (idempotent)"
wp eval 'function_exists("monthly_booking_backfill_room_id") && monthly_booking_backfill_room_id();' || true
RES_COUNT=$(wp eval 'echo function_exists("monthly_booking_seed") ? (string)monthly_booking_seed(["reservations"=>6]) : "N/A";' || echo "N/A")
echo "RES_COUNT=${RES_COUNT}"

echo "wp-setup done"
