#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/../../.."

MB_BASE_URL="${MB_BASE_URL:-http://127.0.0.1:8080}"
MB_READY_TOTAL_SEC="${MB_READY_TOTAL_SEC:-240}"
MB_READY_INTERVAL_SEC="${MB_READY_INTERVAL_SEC:-3}"
ATTEMPTS=$(( MB_READY_TOTAL_SEC / MB_READY_INTERVAL_SEC ))

docker compose -f dev/docker-compose.yml up -d

echo "Ensuring WordPress core is installed..."
i=$ATTEMPTS
MB_ADMIN_EMAIL="${MB_ADMIN_EMAIL:-admin@example.com}"
MB_SITE_TITLE="${MB_SITE_TITLE:-Monthly Booking CI}"
until docker compose -f dev/docker-compose.yml run --rm wpcli wp core is-installed >/dev/null 2>&1; do
  docker compose -f dev/docker-compose.yml run --rm wpcli wp core install \
    --url="${MB_BASE_URL%/}" \
    --title="${MB_SITE_TITLE}" \
    --admin_user="${MB_ADMIN_USER:-admin}" \
    --admin_password="${MB_ADMIN_PASS:-password}" \
    --admin_email="${MB_ADMIN_EMAIL}" \
    --skip-email >/dev/null 2>&1 || true
  i=$((i-1))
  if [ "$i" -le 0 ]; then
    echo "WordPress did not become ready in time (core)"
    docker ps
    docker compose -f dev/docker-compose.yml logs --tail=200
    exit 1
  fi
  sleep "$MB_READY_INTERVAL_SEC"
done
docker compose -f dev/docker-compose.yml run --rm wpcli wp option update home "${MB_BASE_URL%/}" || true
docker compose -f dev/docker-compose.yml run --rm wpcli wp option update siteurl "${MB_BASE_URL%/}" || true
docker compose -f dev/docker-compose.yml run --rm wpcli wp config set WP_HOME "${MB_BASE_URL%/}" --type=constant --raw || true
docker compose -f dev/docker-compose.yml run --rm wpcli wp config set WP_SITEURL "${MB_BASE_URL%/}" --type=constant --raw || true
docker compose -f dev/docker-compose.yml run --rm wpcli wp db query "UPDATE \`wp_options\` SET option_value='${MB_BASE_URL%/}' WHERE option_name IN ('home','siteurl');" || true
docker compose -f dev/docker-compose.yml run --rm wpcli wp cache flush || true

docker compose -f dev/docker-compose.yml exec -T wordpress bash -lc "mkdir -p /var/www/html/wp-content/mu-plugins && printf '%s\n' \
'<?php' \
'\$__mb_base = \"${MB_BASE_URL%/}\";' \
'add_filter(\"pre_option_home\", function(){ global \$__mb_base; return \$__mb_base; });' \
'add_filter(\"pre_option_siteurl\", function(){ global \$__mb_base; return \$__mb_base; });' \
'add_filter(\"option_home\", function(\$v){ global \$__mb_base; return \$__mb_base;});' \
'add_filter(\"option_siteurl\", function(\$v){ global \$__mb_base; return \$__mb_base;});' \
'add_filter(\"home_url\", function(\$url){ global \$__mb_base; return \$__mb_base . parse_url(\$url, PHP_URL_PATH); }, 10, 1);' \
'add_filter(\"site_url\", function(\$url){ global \$__mb_base; return \$__mb_base . parse_url(\$url, PHP_URL_PATH); }, 10, 1);' \
'add_filter(\"rest_url\", function(\$url){ global \$__mb_base; \$p = parse_url(\$url, PHP_URL_PATH); return rtrim(\$__mb_base, \"/\") . \$p; }, 10, 1);' \
> /var/www/html/wp-content/mu-plugins/mb-test-url.php && chown www-data:www-data /var/www/html/wp-content/mu-plugins/mb-test-url.php && chmod 644 /var/www/html/wp-content/mu-plugins/mb-test-url.php"

docker compose -f dev/docker-compose.yml run --rm wpcli wp plugin activate monthly-booking || true
if [ -f dist/sample-data.sql ]; then
  echo "Importing sample data..."
  docker compose -f dev/docker-compose.yml run --rm wpcli bash /scripts/import_sample_sql.sh
fi

docker compose -f dev/docker-compose.yml run --rm wpcli wp user get admin || \
  docker compose -f dev/docker-compose.yml run --rm wpcli wp user create admin admin@example.com --role=administrator --user_pass=password

echo "Waiting for WordPress to be reachable (http)..."
HTTP_READY=""
for j in $(seq 1 "$ATTEMPTS"); do
  for ep in "" "/wp-json/" "/wp-admin/" "/wp-login.php"; do
    url="${MB_BASE_URL%/}$ep"
    code="$(curl -sS -o /dev/null -I -w "%{http_code}" "$url" || true)"
    if [[ "$code" =~ ^[23][0-9][0-9]$ ]]; then
      HTTP_READY=1
      break
    fi
  done
  if [ -n "${HTTP_READY}" ]; then
    break
  fi
  sleep "$MB_READY_INTERVAL_SEC"
done

if [ -z "${HTTP_READY}" ]; then
  echo "Health check failed: WordPress not reachable at ${MB_BASE_URL}"
  docker ps
  docker compose -f dev/docker-compose.yml logs --tail=200
  exit 1
fi

REST_TOTAL=30
REST_INT=3
REST_ATTEMPTS=$(( REST_TOTAL / REST_INT ))
REST_READY=""
for k in $(seq 1 "$REST_ATTEMPTS"); do
  code="$(curl -sS -o /dev/null -I -w "%{http_code}" "${MB_BASE_URL%/}/wp-json/" || true)"
  if [[ "$code" =~ ^[23][0-9][0-9]$ ]]; then
    REST_READY=1
    break
  fi
  sleep "$REST_INT"
done

for ep in "/wp-admin/" "/wp-admin/admin.php?page=monthly-room-booking" "/wp-admin/admin.php?page=monthly-room-booking-calendar" "/wp-admin/admin.php?page=monthly-room-booking-campaigns"; do
  curl -sS -o /dev/null -I -w "%{http_code}" "${MB_BASE_URL%/}$ep" || true
done

sleep 3

echo "Base URL: ${MB_BASE_URL}"
