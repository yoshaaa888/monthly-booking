#!/usr/bin/env bash
set -euo pipefail

# ==== basic settings ====
TS="$(date +'%Y%m%d-%H%M%S')"
OUT_DIR="diagnostics"
OUT="$OUT_DIR/diag-$TS.txt"
JSON="$OUT_DIR/diag-$TS.json"
mkdir -p "$OUT_DIR"

log()      { echo "[$(date +'%H:%M:%S')] $*" | tee -a "$OUT" ; }
section()  { echo -e "\n=== $* ===" | tee -a "$OUT" ; }
have()     { command -v "$1" >/dev/null 2>&1; }

json_obj_begin(){ echo "{" > "$JSON"; }
json_obj_end(){  echo "}" >> "$JSON"; }
json_add(){ # json_add "key" "value"
  local k="$1" v="${2//\"/\\\"}"
  if [ "$(tail -1 "$JSON")" != "{" ]; then sed -i '$ s/$/,/' "$JSON"; fi
  echo "  \"${k}\":\"${v}\"" >> "$JSON"
}

detect_public_url() {
  local p="${1:-8888}"
  if [ -n "${CODESPACE_NAME:-}" ]; then
    echo "https://${CODESPACE_NAME}-${p}.app.github.dev"
  else
    echo ""
  fi
}

try_head() {
  local u="$1"
  ( set -o pipefail; curl --connect-timeout 2 --max-time 10 -sS -I "$u" | head -n1 )
}

nodejs_ver()  { node -v 2>/dev/null || true; }
npm_ver()     { npm -v 2>/dev/null || true; }
pnpm_ver()    { pnpm -v 2>/dev/null || true; }
pw_ver()      { npx --no-install playwright --version 2>/dev/null || true; }

json_obj_begin

section "Codespaces / Environment"
log "CODESPACES: ${CODESPACES:-}"
log "CODESPACE_NAME: ${CODESPACE_NAME:-}"
log "GITHUB_USER: ${GITHUB_USER:-}"
log "SHELL: ${SHELL:-}"
log "PWD: $PWD"
json_add "codespaces" "${CODESPACES:-}"
json_add "codespace_name" "${CODESPACE_NAME:-}"
json_add "github_user" "${GITHUB_USER:-}"
json_add "pwd" "$PWD"

section "Node / Playwright / Package Manager versions"
log "node:      $(nodejs_ver)"
log "npm:       $(npm_ver)"
log "pnpm:      $(pnpm_ver)"
log "playwright $(pw_ver)"
json_add "node" "$(nodejs_ver)"
json_add "npm" "$(npm_ver)"
json_add "pnpm" "$(pnpm_ver)"
json_add "playwright" "$(pw_ver)"

section "Git status"
log "branch: $(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo 'n/a')"
log "last-commit: $(git log -1 --pretty=format:'%h %ad %s' --date=iso 2>/dev/null || echo 'n/a')"
log "dirty?: $(test -n "$(git status --porcelain 2>/dev/null)" && echo 'YES' || echo 'clean')"
json_add "git_branch" "$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo 'n/a')"
json_add "git_dirty"  "$(test -n "$(git status --porcelain 2>/dev/null)" && echo 'YES' || echo 'NO')"

section "Ports / Processes"
if have ss; then
  log "LISTEN (top 50):"
  ss -ltnp | head -n 50 | tee -a "$OUT" >/dev/null || true
elif have lsof; then
  log "LISTEN (top 50 via lsof):"
  lsof -i -P -n | grep LISTEN | head -n 50 | tee -a "$OUT" >/dev/null || true
else
  log "No ss/lsof available."
fi
if have ss; then
  json_add "port_8888_listen" "$(ss -ltn 'sport = :8888' 2>/dev/null | wc -l | tr -d ' ')"
fi

section "WP endpoints / Connectivity"
BASE_LOCAL="${BASE_URL:-http://127.0.0.1:8888}"
BASE_PUBLIC="$(detect_public_url 8888)"
log "BASE_LOCAL:  $BASE_LOCAL"
log "BASE_PUBLIC: ${BASE_PUBLIC:-'(unknown)'}"
json_add "base_local" "$BASE_LOCAL"
json_add "base_public" "${BASE_PUBLIC:-}"

if have curl; then
  for U in \
    "$BASE_LOCAL/wp-json/mb-qa/v1/ping" \
    "$BASE_LOCAL/wp-admin/admin-ajax.php" \
  ; do
    log "--- HEAD $U"
    if ! try_head "$U" 2>>"$OUT"; then
      log "FAIL: $U"
    fi
  done
else
  log "curl not available, skipping connectivity checks."
fi

section "wp-env / wp-now status (best effort)"
if have wp-env; then
  log "wp-env version: $(wp-env --version 2>/dev/null || true)"
  log "wp-env list:"; wp-env list 2>&1 | tee -a "$OUT" >/dev/null || true
fi
if have ps; then
  log "ps | grep 'wp-now|wp-env|php|mysql' (top 30)"
  ps aux | egrep 'wp-now|wp-env|php|mysql' | head -n 30 | tee -a "$OUT" >/dev/null || true
fi

section "WordPress siteurl/home (wp-cli via wp-env if available)"
if have wp-env; then
  WP="wp-env run cli -- wp"
  HOME_VAL="$($WP option get home 2>/dev/null || echo 'n/a')"
  SITE_VAL="$($WP option get siteurl 2>/dev/null || echo 'n/a')"
  log "home:    $HOME_VAL"
  log "siteurl: $SITE_VAL"
  json_add "wp_home" "$HOME_VAL"
  json_add "wp_siteurl" "$SITE_VAL"
else
  log "wp-env not available; skipping wp-cli checks."
fi

section "Spec integrity check (Playwright tests)"
SUSPICIOUS=0
if [ -d test-environment/playwright/tests ]; then
  while IFS= read -r -d '' f; do
    if grep -qE 'JS;|<<|ed'\'';\s*async\s*\(\{ page \}\)\s*=>|^\s*<<<<<<<|^\s*>>>>>>>' "$f"; then
      log "[WARN] suspicious partial-save patterns: $f"
      SUSPICIOUS=$((SUSPICIOUS+1))
    fi
  done < <(find test-environment/playwright/tests -name '*.spec.js' -print0 2>/dev/null)
  log "suspicious_files_count: $SUSPICIOUS"
  json_add "spec_suspicious_count" "$SUSPICIOUS"
else
  log "No spec directory found."
  json_add "spec_suspicious_count" "n/a"
fi

section "Disk / Memory / CPU"
have df   && df -h   | tee -a "$OUT" >/dev/null || true
have free && free -h | tee -a "$OUT" >/dev/null || true
have nproc && log "CPU cores: $(nproc)"

section "Network egress quick check"
if have curl; then
  for URL in "https://www.google.com" "https://wordpress.org"; do
    log "--> $(try_head "$URL" || echo FAIL)"
  done
fi

section "Summary / Hints"
if [ "${SUSPICIOUS:-0}" -gt 0 ]; then
  log "HINT: Detected $SUSPICIOUS suspicious spec(s). Restore safe copies if tests fail."
fi
if ! curl --connect-timeout 2 --max-time 10 -sS -I "$BASE_LOCAL" >/dev/null 2>&1; then
  log "HINT: 8888 not responding. Run: bash scripts/wp-start.sh"
fi
if [ -n "${BASE_PUBLIC:-}" ]; then
  log "HINT: Public URL (guess): $BASE_PUBLIC"
fi

json_obj_end

echo
echo "== Report files =="
echo "Text:   $OUT"
echo "JSON:   $JSON"
