export NPX_YES=1
export npm_config_yes=true
export npm_config_legacy_peer_deps=true
export CI=1
export ADBLOCK=1

# kill previous on $PORT (idempotent)
if command -v lsof >/dev/null 2>&1; then lsof -t -i :"${PORT}" | xargs -r kill -9 || true; fi

#!/usr/bin/env bash
# ---- Playwright browsers ensure & npm peer-deps relax ----
export NPX_YES=${NPX_YES:-1}
export npm_config_legacy_peer_deps=${npm_config_legacy_peer_deps:-1}
npx -y playwright install chromium >/dev/null 2>&1 || true
# -----------------------------------------------------------
set -euo pipefail
CURL_OPTS="--connect-timeout 2 --max-time 10 -sS"
cleanup() {
  s=$?
  echo "::group::cleanup"
  if [ -f wp-now.pid ]; then pkill -F wp-now.pid 2>/dev/null || true; fi
  if [ -n "${WP_NOW_PID:-}" ]; then kill "$WP_NOW_PID" 2>/dev/null || true; fi
  echo "::endgroup::"
  echo "::group::wp-now.log (tail 200)"; tail -n 200 wp-now.log 2>/dev/null || true; echo "::endgroup::"
  exit $s
}
trap 'cleanup' EXIT
PORT="${PORT:-8888}"
WP_VER="${WP_VER:-6.8.2}"
BASE_URL="http://127.0.0.1:${PORT}"
export MB_FIXER_ACTIVE="${MB_FIXER_ACTIVE:-}"
rest_ready=0
has_ok_or_success_true() {
  grep -Eq '"ok"[[:space:]]*:[[:space:]]*true' "$1" \
  || grep -Eq '"success"[[:space:]]*:[[:space:]]*true' "$1" \
  || (command -v jq >/dev/null 2>&1 && jq -e '(.ok==true) or (.success==true) or (.data.ok==true) or (.data.success==true)' "$1" >/dev/null 2>&1);
}

echo "::group::wp-now env"
npx -y @wp-now/wp-now@latest --version || true
node -v || true
echo "WP_VER=${WP_VER:-unknown}  PORT=${PORT:-unknown}  BASE_URL=${BASE_URL:-unknown}"
echo "::endgroup::"

echo "== kill any previous wp-now on :${PORT}/:8888/:8890 =="
pkill -f "wp-now start" 2>/dev/null || true
for P in 8888 8890 "$PORT"; do
  PID="$(ss -lntp 2>/dev/null | { grep -F ":${P} " || true; } | sed -n 's/.*pid=\([0-9]\+\).*/\1/p' | head -n1)"
  [ -n "${PID:-}" ] && kill -9 "$PID" || true
done
PIDS=$(ss -lntp 2>/dev/null | sed -n "s/.*:${PORT}[[:space:]].*pid=\([0-9]\+\).*/\1/p" | sort -u)
[ -n "${PIDS:-}" ] && kill -9 $PIDS 2>/dev/null || true

echo "== reset wp-now cache for WP_VER =="
rm -rf "$HOME/.wp-now/wordpress-versions/$WP_VER" || true
mkdir -p "$HOME/.wp-now/wordpress-versions" || true
ls -la "$HOME/.wp-now/wordpress-versions" || true

MU_PHP='<?php
add_action("rest_api_init", function(){ register_rest_route("mb-qa/v1","/ping",[
  "methods"=>"GET","permission_callback"=>"__return_true",
  "callback"=>function(){ return ["success"=>true,"ok"=>true,"ts"=>time(),"src"=>"mu"]; }
]);});
function _mb_qa_echo(){ wp_send_json_success(["ok"=>true,"ts"=>time(),"src"=>"mu"]); }
add_action("wp_ajax_mb_qa_echo","_mb_qa_echo");
add_action("wp_ajax_nopriv_mb_qa_echo","_mb_qa_echo");'

MU_HOST="$HOME/.wp-now/mu-plugins"
mkdir -p "$MU_HOST"
printf "%s" "$MU_PHP" > "$MU_HOST/zzz-mb-qa-temp.php"
php -l "$MU_HOST/zzz-mb-qa-temp.php" || exit 1


echo "== wp-now version =="
npx -y @wp-now/wp-now@latest --version || true
echo "== start wp-now on :${PORT} =="
MB_FIXER_ACTIVE="${MB_FIXER_ACTIVE:-1}" nohup npx -y @wp-now/wp-now@latest start --wp " --skip-browser$WP_VER" --port "$PORT" > wp-now.log 2>&1 &
WP_NOW_PID=$!
echo "$WP_NOW_PID" > wp-now.pid
sleep 3


echo "::group::Health check (max 240s)::"
ok=0
for i in $(seq 1 120); do
  code=$(curl $CURL_OPTS -o /dev/null -w '%{http_code}' "$BASE_URL/" || echo 000)
  echo "health[$i]=$code"
  if [ "$code" = 200 ]; then ok=1; break; fi
  sleep 2
done
echo "::endgroup::"
if [ "$ok" != 1 ]; then
  echo "::group::wp-now.log (head 80)"; head -n 80 wp-now.log || true; echo "::endgroup::"
  echo "::group::wp-now.log (tail 200)"; tail -n 200 wp-now.log || true; echo "::endgroup::"
  exit 1
fi
echo "::group::REST readiness (<=30s)::"
rest_ready=0
for i in $(seq 1 30); do
  rcode=$(curl $CURL_OPTS -o rest.json -w '%{http_code}\n' "$BASE_URL/wp-json/mb-qa/v1/ping" || echo 000)
  echo "rest_status[$i]=$rcode"
  if [ "$rcode" = "200" ]; then rest_ready=1; break; fi
  sleep 1
done
echo "::endgroup::"
echo "== inject MU plugin proactively (runtime write, no wp-load) =="
for ROOT in /var/www/html /wordpress; do
  npx -y @wp-now/wp-now@latest php -r '
    $root = getenv("ROOT");
    $dir = $root."/wp-content/mu-plugins";
    @mkdir($dir,0777,true);
    $ok = @file_put_contents($dir."/zzz-mb-qa-temp.php", getenv("CODE"))!==false;
    echo ($ok?"mu_dir_ok=":"mu_dir_try=").$dir.PHP_EOL;
  ' >/dev/null 2>&1 ROOT="$ROOT" CODE="$MU_PHP" || true
done
npx -y @wp-now/wp-now@latest php -r '
  $dirs=["/var/www/html/wp-content/mu-plugins","/wordpress/wp-content/mu-plugins"];
  $exists="mu=missing";
  foreach($dirs as $d){ if (file_exists($d."/zzz-mb-qa-temp.php")) { $exists="mu=exists"; } }
  echo $exists.PHP_EOL;
' || true

echo "::group::REST after MU (<=30s)::"
for i in $(seq 1 30); do
  rcode=$(curl $CURL_OPTS -o rest.json -w '%{http_code}\n' "$BASE_URL/wp-json/mb-qa/v1/ping" || echo 000)
  echo "rest_status_after_mu[$i]=$rcode"
  [ "$rcode" = "200" ] && break
  sleep 1
done
echo "::endgroup::"


echo "::group::Sanity REST::"
curl $CURL_OPTS -o rest.json -w 'rest_status=%{http_code}\n' "$BASE_URL/wp-json/mb-qa/v1/ping" || true
echo; head -c 300 rest.json || true; echo
echo "::endgroup::"

echo "::group::Sanity AJAX (with MU rescue if needed)::"
code=$(curl $CURL_OPTS -H 'Content-Type: application/x-www-form-urlencoded' -o ajax.json -w '%{http_code}\n' -X POST -d "action=mb_qa_echo" "$BASE_URL/wp-admin/admin-ajax.php" || true)
echo "ajax_status_initial=$code"
head -c 300 ajax.json || true; echo
if [ "$code" != "200" ] || ! has_ok_or_success_true ajax.json; then
  echo "Inject MU in runtime and retry (no wp-load)"
  for ROOT in /var/www/html /wordpress; do
    npx -y @wp-now/wp-now@latest php -r '
      $r=getenv("ROOT"); $d=$r."/wp-content/mu-plugins";
      @mkdir($d,0777,true);
      $ok=@file_put_contents($d."/zzz-mb-qa-temp.php", getenv("CODE"))!==false;
      echo ($ok?"mu_rescue_ok=":"mu_rescue_try=").$d.PHP_EOL;
    ' >/dev/null 2>&1 ROOT="$ROOT" CODE="$MU_PHP" || true
  done

  echo "::group::REST after MU (<=30s)::"
  rest_after=0
  for i in $(seq 1 30); do
    rcode=$(curl $CURL_OPTS -o rest.json -w '%{http_code}\n' "$BASE_URL/wp-json/mb-qa/v1/ping" || echo 000)
    echo "rest_status_after_mu[$i]=$rcode"
    if [ "$rcode" = "200" ]; then rest_after=1; break; fi
    sleep 1
  done
  echo "::endgroup::"

  for i in $(seq 1 30); do
    code=$(curl $CURL_OPTS -H 'Content-Type: application/x-www-form-urlencoded' -o ajax.json -w '%{http_code}\n' -X POST -d "action=mb_qa_echo" "$BASE_URL/wp-admin/admin-ajax.php" || true)
    echo "ajax_status_after_mu[$i]=$code"
    head -c 300 ajax.json || true; echo
    if [ "$code" = "200" ] && has_ok_or_success_true ajax.json; then break; fi
    sleep 1
  done
fi

echo "::endgroup::"

if ! has_ok_or_success_true ajax.json; then
  echo "Rescue failed"
  echo "::group::wp-now.log (head 80)"; head -n 80 wp-now.log || true; echo "::endgroup::"
  echo "::group::wp-now.log (tail 200)"; tail -n 200 wp-now.log || true; echo "::endgroup::"
  echo "::group::MU/REST diagnostics"
  npx -y @wp-now/wp-now@latest php -r '
    $dirs = [
      "/var/www/html/wp-content/mu-plugins",
      "/wordpress/wp-content/mu-plugins",
    ];
    $exists = "mu=missing\n";
    foreach ($dirs as $d) {
      if (file_exists($d . "/zzz-mb-qa-temp.php")) { $exists = "mu=exists\n"; }
      echo (is_dir($d) ? "mu_dir=" : "mu_dir_try=") . $d . "\n";
    }
    echo $exists;
  ' || true
  echo "::group::Routes from /wp-json"
  curl $CURL_OPTS "$BASE_URL/wp-json" | head -c 2000 | sed -n '1,200p' || true
  echo
  echo "::endgroup::"
  echo "::endgroup::"
  exit 1
fi

echo "== run smoke tests =="
BASE_URL="$BASE_URL" npx playwright test -c playwright.smoke.config.js --reporter=list,html
if [ -n "${GITHUB_STEP_SUMMARY:-}" ]; then
  {
    echo "### E2E Smoke Summary"
    echo "- BASE_URL: ${BASE_URL}"
    echo "- REST ok: $(grep -o '\"ok\"[[:space:]]*:[[:space:]]*true' rest.json >/dev/null && echo 'true' || echo 'unknown')"
    echo "- AJAX success: $(grep -o '\"success\"[[:space:]]*:[[:space:]]*true' ajax.json >/dev/null && echo 'true' || echo 'unknown')"
  } >> "$GITHUB_STEP_SUMMARY"
fi
