#!/usr/bin/env bash
set -e
pkill -f '@wp-now/wp-now' 2>/dev/null || true
nohup npx -y @wp-now/wp-now start --port 8888 >/tmp/wp-now.log 2>&1 &
disown
for i in $(seq 1 40); do
  code=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8888/ || echo 000)
  echo "health=$code"
  [[ "$code" == 200 || "$code" == 301 ]] && exit 0
  sleep 1
done
echo "Server did not become healthy in time"; exit 1

# --- auto-install test MU plugins (stable) ---
if [ -f "$PWD/test-environment/mu-plugins/mb-test-rest.php" ]; then
  mkdir -p ~/.wp-now/mu-plugins ~/.wp-now/wordpress-versions/6.8.2/wp-content/mu-plugins
  cp -f "$PWD/test-environment/mu-plugins/mb-test-rest.php" ~/.wp-now/mu-plugins/mb-test-rest.php
  cp -f "$PWD/test-environment/mu-plugins/mb-test-rest.php" ~/.wp-now/wordpress-versions/6.8.2/wp-content/mu-plugins/mb-test-rest.php
fi

## install mb-test-rest
if [ -f "$PWD/test-environment/mu-plugins/mb-test-rest.php" ]; then
  mkdir -p ~/.wp-now/mu-plugins \
          ~/.wp-now/wordpress-versions/6.8.2/wp-content/mu-plugins
  cp -f "$PWD/test-environment/mu-plugins/mb-test-rest.php" \
        ~/.wp-now/mu-plugins/mb-test-rest.php
  cp -f "$PWD/test-environment/mu-plugins/mb-test-rest.php" \
        ~/.wp-now/wordpress-versions/6.8.2/wp-content/mu-plugins/mb-test-rest.php
fi
