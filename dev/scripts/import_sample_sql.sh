#!/usr/bin/env bash
set -euo pipefail
SQL_PATH=${1:-/var/www/html/wp-content/plugins/monthly-booking/dist/sample-data.sql}
if [ ! -f "$SQL_PATH" ]; then
  echo "SQL not found at $SQL_PATH"
  exit 1
fi
wp db import "$SQL_PATH"
echo "Sample data imported from $SQL_PATH"
