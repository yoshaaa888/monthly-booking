#!/usr/bin/env bash
set -Eeuo pipefail

# Basic settings
BASE_URL="${BASE_URL:-http://127.0.0.1:8888}"
ROOT_DIR="$(cd -- "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PLAY_CONFIG="$ROOT_DIR/test-environment/playwright/playwright.config.js"
SPEC="$ROOT_DIR/test-environment/playwright/tests/crud/campaigns.spec.js"

# Options
STEP_PREP=false
SKIP_START=false
PROJECT="chromium"
TRACE="on"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --prepare)    STEP_PREP=true; shift ;;
    --skip-start) SKIP_START=true; shift ;;
    --project)    PROJECT="${2:-chromium}"; shift 2 ;;
    --trace)      TRACE="${2:-on}"; shift 2 ;;
    -h|--help)
      echo "Usage: $0 [--prepare] [--skip-start] [--project chromium|firefox|webkit] [--trace on|retain-on-failure|off]"
      exit 0
      ;;
    *) echo "Unknown option: $1"; exit 1 ;;
  esac
done

cd "$ROOT_DIR"

if $STEP_PREP; then
  echo "[prep] npm deps"
  npm ci || npm install
  echo "[prep] playwright browsers"
  npx playwright install --with-deps
fi

if ! $SKIP_START; then
  echo "[wp] starting..."
  bash scripts/wp-start.sh
fi

echo "[check] /mb-test/v1/nonces"
code=$(curl -sS -o /dev/null -w '%{http_code}' "$BASE_URL/wp-json/mb-test/v1/nonces")
if [[ "$code" != "200" ]]; then
  echo "ERROR: nonce route not ready (HTTP $code)"
  echo "Tip: scripts/wp-start.sh should auto-copy test MU plugin (mb-test-rest.php)."
  exit 1
fi

echo "[migrate] name column backfill"
curl -sS -X POST "$BASE_URL/wp-json/mb-test/v1/campaigns-migrate-name" >/dev/null || true

echo "[test] Playwright: campaigns CRUD only"
BASE_URL="$BASE_URL" npx playwright test \
  -c "$PLAY_CONFIG" \
  "$SPEC" \
  --project="$PROJECT" \
  --reporter=list \
  --trace="$TRACE" \
  --workers=1
