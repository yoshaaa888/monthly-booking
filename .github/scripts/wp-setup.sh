#!/usr/bin/env bash

set -euo pipefail

: "${DB_WAIT_MAX:=60}"         # 最大待機秒
: "${DB_WAIT_INTERVAL:=2}"     # リトライ間隔秒
: "${WP_TITLE:=Monthly Booking CI}"
: "${WP_ADMIN_USER:=admin}"
: "${WP_ADMIN_PASS:=password}"
: "${WP_ADMIN_EMAIL:=admin@example.com}"
: "${SITE_URL:=}"              # 未指定なら WordPress の home を取得して使用

log() { echo "::notice::[$(date -u +'%H:%M:%S')] $*"; }
warn() { echo "::warning:: $*"; }
err() { echo "::error:: $*"; }

wpcli() {
  wp-env run cli -- wp "$@"
}

log "Waiting for MySQL to become healthy (<= ${DB_WAIT_MAX}s)"
end_ts=$(( $(date +%s) + DB_WAIT_MAX ))
while true; do
  if wpcli db check >/dev/null 2>&1; then
    log "MySQL is healthy."
    break
  fi
  if [ "$(date +%s)" -ge "$end_ts" ]; then
    err "MySQL did not become ready in time (${DB_WAIT_MAX}s)."
    warn "Dumping quick context."
    wpcli option get siteurl || true
    wpcli option get home || true
    exit 1
  fi
  sleep "${DB_WAIT_INTERVAL}"
done

if ! wpcli core is-installed >/dev/null 2>&1; then
  log "WordPress core not installed. Installing..."
  if [ -z "${SITE_URL}" ]; then
    SITE_URL="$(wpcli option get home 2>/dev/null || true)"
    SITE_URL="${SITE_URL:-http://localhost:8888}"
  fi
  log "Installing WP at: ${SITE_URL}"
  wpcli core install \
    --url="${SITE_URL}" \
    --title="${WP_TITLE}" \
    --admin_user="${WP_ADMIN_USER}" \
    --admin_password="${WP_ADMIN_PASS}" \
    --admin_email="${WP_ADMIN_EMAIL}" \
    --skip-email
else
  log "WordPress core already installed."
fi

log "Setting permalink structure -> /%postname%/ and flushing rewrite rules."
wpcli rewrite structure '/%postname%/' --hard
wpcli rewrite flush --hard

log "Activating monthly-booking plugin (idempotent)."
wpcli plugin activate monthly-booking || true

log "Ensuring feature flag option (mb_feature_reservations_mvp=1)."
wpcli option update mb_feature_reservations_mvp 1 --autoload=yes || true

if wpcli help mb >/dev/null 2>&1; then
  log "Running wp mb bootstrap (idempotent)."
  wpcli mb bootstrap || true
else
  warn "wp mb bootstrap is not registered. Skipping."
fi

if [ -z "${SITE_URL}" ]; then
  SITE_URL="$(wpcli option get home 2>/dev/null || true)"
  SITE_URL="${SITE_URL:-http://localhost:8888}"
fi
CAL_URL="${SITE_URL%/}/monthly-calendar/"
log "Warming up: ${CAL_URL}"
curl -sS -I "${CAL_URL}" || true

log "Bootstrap complete."
# CI Status Check - Sun Aug 10 03:04:26 UTC 2025
