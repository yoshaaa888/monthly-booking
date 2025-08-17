#!/usr/bin/env bash
set -euo pipefail
PORT="${PORT:-8891}"
WP_VER="${WP_VER:-6.8.2}"
BASE_URL="http://127.0.0.1:${PORT}"

echo "== kill any previous wp-now on :${PORT}/:8888/:8890 =="
pkill -f "wp-now start" 2>/dev/null || true
for P in 8888 8890 "$PORT"; do
  PID=$(ss -lntp | awk -v p=":${P} " '$0 ~ p {match($0,/pid=([0-9]+)/,m); if(m[1]) print m[1]}' | head -n1)
  [ -n "${PID:-}" ] && kill -9 "$PID" || true
done

echo "== reset wp-now cache for WP_VER =="
rm -rf "$HOME/.wp-now/wordpress-versions/$WP_VER" || true
mkdir -p "$HOME/.wp-now/wordpress-versions" || true
ls -la "$HOME/.wp-now/wordpress-versions" || true

echo "== create temp root plugin (QA endpoints) =="
cat > wp-now-plugin.php <<'PHP'
<?php
/**
 * Plugin Name: MB QA Temp Plugin
 */
add_action('rest_api_init', function () {
  register_rest_route('mb-qa/v1', '/ping', [
    'methods' => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function () {
      return ['ok' => true, 'ts' => time(), 'src' => 'root'];
    },
  ]);
});
function _mb_qa_echo() { wp_send_json(['ok' => true, 'ts' => time(), 'src' => 'root']); }
add_action('wp_ajax_mb_qa_echo', '_mb_qa_echo');
add_action('wp_ajax_nopriv_mb_qa_echo', '_mb_qa_echo');
PHP
php -l wp-now-plugin.php || true

echo "== start wp-now on :${PORT} =="
nohup npx wp-now start --wp "$WP_VER" --port "$PORT" --path . > wp-now.log 2>&1 &
WP_NOW_PID=$!
echo "$WP_NOW_PID" > wp-now.pid
sleep 3

cleanup() {
  echo "== cleanup =="
  kill "$WP_NOW_PID" 2>/dev/null || pkill -f "wp-now start" || true
}
trap cleanup EXIT INT TERM

echo "== health check (max 240s) =="
ok=0
for i in $(seq 1 120); do
  code=$(curl -sS -o /dev/null -w '%{http_code}' "$BASE_URL/" || echo 000)
  echo "health[$i]=$code"
  if [ "$code" = 200 ]; then ok=1; break; fi
  sleep 2
done
if [ "$ok" != 1 ]; then
  echo "not healthy"; tail -n 200 wp-now.log || true; exit 1
fi
echo "== wait for REST mb-qa ping (<=60s) =="
rest_ready=0
for i in $(seq 1 30); do
  rcode=$(curl -s -o rest.json -w '%{http_code}\n' "$BASE_URL/wp-json/mb-qa/v1/ping" || echo 000)
  echo "rest_status[$i]=$rcode"
  if [ "$rcode" = "200" ]; then rest_ready=1; break; fi
  sleep 2
done
if [ "$rest_ready" != 1 ]; then
  echo "REST not ready yet, will proceed to MU rescue if needed"
fi


echo "== sanity REST =="
curl -s -o rest.json -w 'rest_status=%{http_code}\n' "$BASE_URL/wp-json/mb-qa/v1/ping" || true
echo; head -c 300 rest.json || true; echo

echo "== sanity AJAX (with MU rescue if needed) =="
code=$(curl -s -o ajax.json -w '%{http_code}\n' -X POST -d "action=mb_qa_echo" "$BASE_URL/wp-admin/admin-ajax.php" || true)
echo "ajax_status_initial=$code"
head -c 300 ajax.json || true; echo
if [ "$code" != "200" ] || ! grep -Eq '"ok"[[:space:]]*:[[:space:]]*true' ajax.json; then
  echo "Inject MU in runtime and retry"
  cat > mb-qa-mu-temp.php <<'PHP'
<?php
add_action('rest_api_init', function () {
  register_rest_route('mb-qa/v1', '/ping', [
    'methods' => 'GET',
    'permission_callback' => '__return_true',
    'callback' => function () { return ['ok' => true, 'ts' => time(), 'src' => 'mu']; },
  ]);
});
function _mb_qa_echo_mu() { wp_send_json(['ok' => true, 'ts' => time(), 'src' => 'mu']); }
add_action('wp_ajax_mb_qa_echo', '_mb_qa_echo_mu');
add_action('wp_ajax_nopriv_mb_qa_echo', '_mb_qa_echo_mu');
PHP
  npx wp-now php -r 'mkdir("/var/www/html/wp-content/mu-plugins",0777,true); file_put_contents("/var/www/html/wp-content/mu-plugins/zzz-mb-qa-temp.php", file_get_contents("php://stdin")); echo "injected\n";' < mb-qa-mu-temp.php || true

  for i in $(seq 1 30); do
    code=$(curl -s -o ajax.json -w '%{http_code}\n' -X POST -d "action=mb_qa_echo" "$BASE_URL/wp-admin/admin-ajax.php" || true)
    echo "ajax_status_after_mu[$i]=$code"
    head -c 300 ajax.json || true; echo
    if [ "$code" = "200" ] && grep -Eq '"ok"[[:space:]]*:[[:space:]]*true' ajax.json; then break; fi
    sleep 1
  done
fi

if ! grep -Eq '"ok"[[:space:]]*:[[:space:]]*true' ajax.json; then
  echo "Rescue failed"
  tail -n 200 wp-now.log || true
  exit 1
fi

echo "== run smoke tests =="
BASE_URL="$BASE_URL" npm run test:smoke
