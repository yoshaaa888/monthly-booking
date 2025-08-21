#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/../../.."

MB_BASE_URL="${MB_BASE_URL:-http://localhost:8080}"
MB_READY_TOTAL_SEC="${MB_READY_TOTAL_SEC:-180}"
MB_READY_INTERVAL_SEC="${MB_READY_INTERVAL_SEC:-3}"
ATTEMPTS=$(( MB_READY_TOTAL_SEC / MB_READY_INTERVAL_SEC ))

docker compose -f dev/docker-compose.yml up -d

echo "Waiting for WordPress to be ready (core)..."
i=$ATTEMPTS
until docker compose -f dev/docker-compose.yml run --rm wpcli wp core is-installed >/dev/null 2>&1; do
  i=$((i-1))
  if [ "$i" -le 0 ]; then
    echo "WordPress did not become ready in time (core)"
    docker ps
    docker compose -f dev/docker-compose.yml logs --tail=200
    exit 1
  fi
  sleep "$MB_READY_INTERVAL_SEC"
done

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
    code="$(curl -L -sS -o /dev/null -w "%{http_code}" "$url" || true)"
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

echo "Base URL: ${MB_BASE_URL}"
