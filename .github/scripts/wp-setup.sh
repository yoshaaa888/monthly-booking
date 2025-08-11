#!/usr/bin/env bash
set -euo pipefail

note() { echo "::notice::$*"; }
warn() { echo "::warning::$*"; }
err()  { echo "::error::$*"; }

DB_WAIT_MAX="${DB_WAIT_MAX:-60}"
DB_WAIT_INTERVAL="${DB_WAIT_INTERVAL:-2}"

wp_cli() {
  npx wp-env run cli -- bash -lc "WP_CLI_DISABLE_AUTO_PAGER=1 PAGER=cat LESS=-R wp $*"
}

note "Waiting for MySQL to become healthy (max=${DB_WAIT_MAX}s)..."
SECS=0
until wp_cli "db check" >/dev/null 2>&1; do
  sleep "${DB_WAIT_INTERVAL}"
  SECS=$((SECS + DB_WAIT_INTERVAL))
  if [ "${SECS}" -ge "${DB_WAIT_MAX}" ]; then
    err "MySQL did not become healthy within ${DB_WAIT_MAX}s"
    exit 1
  fi
  if (( SECS % 10 == 0 )); then note "Still waiting... ${SECS}s"; fi
done
note "MySQL is healthy."

if wp_cli "core is-installed"; then
  note "WordPress already installed."
else
  warn "WordPress not detected as installed; relying on wp-env defaults."
fi

note "Setting permalink structure and flushing rewrite rules..."
wp_cli "rewrite structure '/%postname%/' --hard"
wp_cli "rewrite flush --hard"

note "Activating plugin monthly-booking (idempotent)..."
wp_cli "plugin activate monthly-booking || true"
wp_cli "plugin list --status=active" | grep -q "^monthly-booking" || { err "Plugin monthly-booking failed to activate"; exit 1; }

note "Enabling feature flag if present..."
wp_cli "option update mb_feature_reservations_mvp 1 || true"

HOME_URL="$(npx wp-env run cli -- wp option get home || true)"
CAL_URL="${HOME_URL%/}/monthly-calendar/"
note "Warming up ${CAL_URL}"
curl -sS -I "${CAL_URL}" || true

note "Bootstrap finished."
