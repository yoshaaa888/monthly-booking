#!/usr/bin/env bash
set -u  # 未定義変数を即エラー
PORT="${PORT:-8891}"
WP_VER="${WP_VER:-6.8.2}"
BASE_URL="http://127.0.0.1:${PORT}"

echo "== kill any previous wp-now on :${PORT}/:8888/:8890 =="
pkill -f "wp-now start" 2>/dev/null || true
for P in 8888 8890 "$PORT"; do
  PID=$(ss -lntp | awk -v p=":${P} " '$0 ~ p {match($0,/pid=([0-9]+)/,m); if(m[1]) print m[1]}' | head -n1)
  [ -n "${PID:-}" ] && kill -9 "$PID" || true
done

echo "== start wp-now on :${PORT} =="
nohup npx wp-now start --wp "$WP_VER" --port "$PORT" --path . > wp-now.log 2>&1 &
WP_NOW_PID=$!
sleep 2

cleanup() {
  echo "== cleanup =="
  kill "$WP_NOW_PID" 2>/dev/null || pkill -f "wp-now start" || true
}
trap cleanup EXIT INT TERM

echo "== health check (max 120s) =="
ok=0
timeout 120 bash -c '
  for i in {1..60}; do
    code=$(curl -s -o /dev/null -w "%{http_code}" "'"$BASE_URL"'/") || code=000
    echo "health[$i]=$code"
    [ "$code" = 200 ] && exit 0
    sleep 2
  done
  exit 1
'
[ $? -eq 0 ] && ok=1
if [ "$ok" != 1 ]; then
  echo "❌ not healthy"; tail -n 200 wp-now.log || true; exit 1
fi

echo "== sanity REST =="
curl -s -o rest.json -w 'rest_status=%{http_code}\n' "$BASE_URL/wp-json/mb-qa/v1/ping"; echo; head -c 300 rest.json; echo

echo "== sanity AJAX =="
curl -s -o ajax.json -w 'ajax_status=%{http_code}\n' -X POST -d "action=mb_qa_echo" "$BASE_URL/wp-admin/admin-ajax.php"; echo; head -c 300 ajax.json; echo

echo "== run smoke tests =="
BASE_URL="$BASE_URL" npm run test:smoke
