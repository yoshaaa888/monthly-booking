#!/usr/bin/env bash
set -euo pipefail
PORT="${PORT:-8888}"
WP_VER="${WP_VER:-6.8.2}"
BASE_URL="http://127.0.0.1:${PORT}"

echo "== kill any previous wp-now on :${PORT}/:8888/:8890 =="
pkill -f "wp-now start" 2>/dev/null || true
for P in 8888 8890 "$PORT"; do
  PID="$(ss -lntp | { grep -F ":${P} " || true; } | sed -n 's/.*pid=\([0-9]\+\).*/\1/p' | head -n1)"
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
add_action('wp_head', function () {
  echo "<!-- MB_FIXER_ACTIVE -->\n";
});
add_action('wp_footer', function () {
  echo "<!-- MB_FIXER_ACTIVE -->\n";
});
add_action('wp_body_open', function () {
  echo "<!-- MB_FIXER_ACTIVE -->\n";
});
add_filter('the_content', function ($c) {
  return "<!-- MB_FIXER_ACTIVE -->\n" . $c;
});
function _mb_qa_echo() {
  wp_send_json_success(['ok' => true, 'ts' => time(), 'src' => 'root']);
}
add_action('wp_ajax_mb_qa_echo', '_mb_qa_echo');
add_action('wp_ajax_nopriv_mb_qa_echo', '_mb_qa_echo');
PHP
php -l wp-now-plugin.php || true

echo "== wp-now version =="
npx wp-now --version || true
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

echo "::group::Health check (max 240s)::"
ok=0
for i in $(seq 1 120); do
  code=$(curl --connect-timeout 2 --max-time 10 -sS -o /dev/null -w '%{http_code}' "$BASE_URL/" || echo 000)
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
echo "::group::REST readiness (<=60s)::"
rest_ready=0
for i in $(seq 1 30); do
  rcode=$(curl --connect-timeout 2 --max-time 10 -s -o rest.json -w '%{http_code}\n' "$BASE_URL/wp-json/mb-qa/v1/ping" || echo 000)
  echo "rest_status[$i]=$rcode"
  if [ "$rcode" = "200" ]; then rest_ready=1; break; fi
  sleep 1
done
echo "::endgroup::"
if [ "$rest_ready" != 1 ]; then
  echo "REST not ready yet, will proceed to MU rescue if needed"
fi


echo "::group::Sanity REST::"
curl --connect-timeout 2 --max-time 10 -s -o rest.json -w 'rest_status=%{http_code}\n' "$BASE_URL/wp-json/mb-qa/v1/ping" || true
echo; head -c 300 rest.json || true; echo
echo "::endgroup::"

echo "::group::Sanity AJAX (with MU rescue if needed)::"
code=$(curl --connect-timeout 2 --max-time 10 -s -o ajax.json -w '%{http_code}\n' -X POST -d "action=mb_qa_echo" "$BASE_URL/wp-admin/admin-ajax.php" || true)
echo "ajax_status_initial=$code"
head -c 300 ajax.json || true; echo
if [ "$code" != "200" ] || ! grep -Eq '("success"[[:space:]]*:[[:space:]]*true|"ok"[[:space:]]*:[[:space:]]*true)' ajax.json; then
  echo "Inject MU in runtime and retry (no wp-load; write to multiple candidate dirs)"
  npx wp-now php -r '
    $php = stream_get_contents(STDIN);
    $dirs = [
      "/var/www/html/wp-content/mu-plugins",
      "/wordpress/wp-content/mu-plugins",
    ];
    foreach ($dirs as $d) {
      @mkdir($d, 0777, true);
      @file_put_contents($d . "/zzz-mb-qa-temp.php", $php);
      if (file_exists($d . "/zzz-mb-qa-temp.php")) {
        echo "mu_dir_ok=$d\n";
      } else {
        echo "mu_dir_try=$d\n";
      }
    }
    $exists = 0;
    foreach ($dirs as $d) {
      if (file_exists($d . "/zzz-mb-qa-temp.php")) { $exists = 1; }
    }
    echo $exists ? "mu=exists\n" : "mu=missing\n";
  ' <<'MU'
<?php
add_action("rest_api_init", function () {
  register_rest_route("mb-qa/v1", "/ping", [
    "methods" => "GET",
    "permission_callback" => "__return_true",
    "callback" => function () { return ["ok" => true, "ts" => time(), "src" => "mu"]; },
  ]);
});
function _mb_qa_echo(){ wp_send_json_success(["ok"=>true, "ts"=>time(), "src"=>"mu"]); }
add_action("wp_ajax_mb_qa_echo", "_mb_qa_echo");
add_action("wp_ajax_nopriv_mb_qa_echo", "_mb_qa_echo");
MU

  echo "::group::REST after MU (<=30s)::"
  rest_after=0
  for i in $(seq 1 30); do
    rcode=$(curl --connect-timeout 2 --max-time 10 -s -o rest.json -w '%{http_code}\n' "$BASE_URL/wp-json/mb-qa/v1/ping" || echo 000)
    echo "rest_status_after_mu[$i]=$rcode"
    if [ "$rcode" = "200" ]; then rest_after=1; break; fi
    sleep 1
  done
  echo "::endgroup::"

  for i in $(seq 1 30); do
    code=$(curl --connect-timeout 2 --max-time 10 -s -o ajax.json -w '%{http_code}\n' -X POST -d "action=mb_qa_echo" "$BASE_URL/wp-admin/admin-ajax.php" || true)
    echo "ajax_status_after_mu[$i]=$code"
    head -c 300 ajax.json || true; echo
    if [ "$code" = "200" ] && grep -Eq '("success"[[:space:]]*:[[:space:]]*true|"ok"[[:space:]]*:[[:space:]]*true)' ajax.json; then break; fi
    sleep 1
  done
fi

echo "::endgroup::"

if ! grep -Eq '("success"[[:space:]]*:[[:space:]]*true|"ok"[[:space:]]*:[[:space:]]*true)' ajax.json; then
  echo "Rescue failed"
  echo "::group::wp-now.log (head 80)"; head -n 80 wp-now.log || true; echo "::endgroup::"
  echo "::group::wp-now.log (tail 200)"; tail -n 200 wp-now.log || true; echo "::endgroup::"
  npx wp-now php -r 'foreach(["/var/www/html/wp-content/mu-plugins","/wordpress/wp-content/mu-plugins"] as $d){echo (file_exists($d."/zzz-mb-qa-temp.php")?"mu=exists ":"mu=missing ").$d."\n";}' || true
  exit 1
fi

echo "== run smoke tests =="
BASE_URL="$BASE_URL" npx playwright test -c playwright.smoke.config.js --reporter=list,html
{
  echo "### E2E Smoke Summary"
  echo "- BASE_URL: ${BASE_URL}"
  echo "- REST ok: $(grep -o '\"ok\"[[:space:]]*:[[:space:]]*true' rest.json >/dev/null && echo 'true' || echo 'unknown')"
  echo "- AJAX success: $(grep -o '\"success\"[[:space:]]*:[[:space:]]*true' ajax.json >/dev/null && echo 'true' || echo 'unknown')"
} >> "$GITHUB_STEP_SUMMARY" 2>/dev/null || true
