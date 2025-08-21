#!/usr/bin/env bash
set -euo pipefail
set -x

if [ -f "$PWD/test-environment/mu-plugins/mb-test-rest.php" ]; then
  mkdir -p ~/.wp-now/mu-plugins ~/.wp-now/wordpress-versions/6.8.2/wp-content/mu-plugins
  install -m 0644 "$PWD/test-environment/mu-plugins/mb-test-rest.php" ~/.wp-now/mu-plugins/mb-test-rest.php
  install -m 0644 "$PWD/test-environment/mu-plugins/mb-test-rest.php" ~/.wp-now/wordpress-versions/6.8.2/wp-content/mu-plugins/mb-test-rest.php
fi
if [ -f "$PWD/test-environment/mu-plugins/mb-qa.php" ]; then
  mkdir -p ~/.wp-now/mu-plugins ~/.wp-now/wordpress-versions/6.8.2/wp-content/mu-plugins
  install -m 0644 "$PWD/test-environment/mu-plugins/mb-qa.php" ~/.wp-now/mu-plugins/mb-qa.php
  install -m 0644 "$PWD/test-environment/mu-plugins/mb-qa.php" ~/.wp-now/wordpress-versions/6.8.2/wp-content/mu-plugins/mb-qa.php
fi

pkill -f '@wp-now/wp-now' 2>/dev/null || true
: > /tmp/wp-now.log || true
nohup npx -y @wp-now/wp-now start --port 8888 >/tmp/wp-now.log 2>&1 &
disown

for i in $(seq 1 40); do
  code=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8888/ || echo 000)
  echo "health=$code"
  if [[ "$code" == "200" || "$code" == "301" ]]; then
    exit 0
  fi
  sleep 1
done

echo "Server did not become healthy in time"
tail -n 200 /tmp/wp-now.log || true
curl -sS http://127.0.0.1:8888/ || true
exit 1
