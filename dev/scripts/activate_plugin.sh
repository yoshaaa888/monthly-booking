#!/usr/bin/env bash
set -euo pipefail
wp plugin activate monthly-booking
wp plugin list --status=active | grep monthly-booking || (echo "Activation failed" && exit 1)
echo "Plugin activated."
