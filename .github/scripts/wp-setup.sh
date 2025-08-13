#!/usr/bin/env bash
set -euo pipefail

echo "Starting wp-env (with update)..."
npx wp-env start --update

echo "Activate plugin just in case..."
npx wp-env run cli wp plugin activate monthly-booking || true

echo "Seeding via wp-cli (container)..."
npx wp-env run cli wp eval-file wp-content/plugins/monthly-booking/seed.php | tee seed.log

echo "Dumping siteurl..."
BASE_URL=$(npx wp-env run cli wp option get siteurl | tr -d '\r')
echo "BASE_URL=${BASE_URL}"
echo "BASE_URL=${BASE_URL}" >> "$GITHUB_ENV"

echo "Show seed tail (for grep)"
tail -n 50 seed.log || true
