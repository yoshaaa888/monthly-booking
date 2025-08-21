#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/../../.."

docker compose -f dev/docker-compose.yml up -d

echo "Waiting for WordPress to be ready..."
ATTEMPTS=60
until docker compose -f dev/docker-compose.yml run --rm wpcli wp core is-installed >/dev/null 2>&1; do
  ATTEMPTS=$((ATTEMPTS-1))
  if [ "$ATTEMPTS" -le 0 ]; then
    echo "WordPress did not become ready in time"
    exit 1
  fi
  sleep 2
done

docker compose -f dev/docker-compose.yml run --rm wpcli wp plugin activate monthly-booking || true
if [ -f dist/sample-data.sql ]; then
  echo "Importing sample data..."
  docker compose -f dev/docker-compose.yml run --rm wpcli bash /scripts/import_sample_sql.sh
fi

docker compose -f dev/docker-compose.yml run --rm wpcli wp user get admin || \
  docker compose -f dev/docker-compose.yml run --rm wpcli wp user create admin admin@example.com --role=administrator --user_pass=password

ATTEMPTS=60
until curl -fsS http://localhost:8080/wp-login.php >/dev/null 2>&1; do
  ATTEMPTS=$((ATTEMPTS-1))
  if [ "$ATTEMPTS" -le 0 ]; then
    echo "Health check failed: WordPress not reachable at http://localhost:8080/wp-login.php"
    exit 1
  fi
  sleep 2
done

echo "Base URL: http://localhost:8080"
