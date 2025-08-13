#!/usr/bin/env bash
set -euo pipefail

: "${BASE_URL:?BASE_URL is required}"
: "${WP_ADMIN_USER:?WP_ADMIN_USER is required}"
: "${WP_ADMIN_PASS:?WP_ADMIN_PASS is required}"

wp option update home "$BASE_URL" || true
wp option update siteurl "$BASE_URL" || true

wp plugin activate monthly-booking || true

if ! wp user get "$WP_ADMIN_USER" >/dev/null 2>&1; then
  wp user create "$WP_ADMIN_USER" "$WP_ADMIN_USER@example.com" --role=administrator --user_pass="$WP_ADMIN_PASS"
fi

wp eval 'function_exists("monthly_booking_backfill_room_id") && monthly_booking_backfill_room_id();' || true
wp eval 'function_exists("monthly_booking_seed") && monthly_booking_seed(["reservations"=>6]);' || true

echo "wp-setup done"
