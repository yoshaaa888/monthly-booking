#!/usr/bin/env bash
set -euo pipefail
echo "Starting wp-env..."
npx wp-env start
echo "Seeding..."
wp eval-file seed.php
echo "Dumping siteurl..."
BASE_URL=$(wp option get siteurl)
echo "BASE_URL=${BASE_URL}"
