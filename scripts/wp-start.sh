#!/usr/bin/env bash
set -euo pipefail
set -x

WP_NOW_VER="6.8.2"
WP_NOW_DIR="${HOME}/.wp-now/wordpress-versions/${WP_NOW_VER}"
WP_NOW_PKG_VERSION="${WP_NOW_PKG_VERSION:-latest}"

mkdir -p "${WP_NOW_DIR}"

if [ ! -f "${WP_NOW_DIR}/wp-config-sample.php" ]; then
  tmpfile="$(mktemp)"
  if command -v curl >/dev/null 2>&1; then
    curl -fsSL https://raw.githubusercontent.com/WordPress/WordPress/master/wp-config-sample.php -o "${tmpfile}" || true
  elif command -v wget >/dev/null 2>&1; then
    wget -qO "${tmpfile}" https://raw.githubusercontent.com/WordPress/WordPress/master/wp-config-sample.php || true
  fi
  if [ -s "${tmpfile}" ]; then
    install -m 0644 "${tmpfile}" "${WP_NOW_DIR}/wp-config-sample.php"
  fi
  rm -f "${tmpfile}"
fi

if [ ! -f "${WP_NOW_DIR}/wp-config-sample.php" ]; then
  cat > "${WP_NOW_DIR}/wp-config-sample.php" <<'PHP'
<?php
define( 'DB_NAME', 'database_name_here' );
define( 'DB_USER', 'username_here' );
define( 'DB_PASSWORD', 'password_here' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );
$table_prefix = 'wp_';
define( 'WP_DEBUG', false );
PHP
fi
if [ ! -f "${WP_NOW_DIR}/wp-load.php" ]; then
  rm -rf "${WP_NOW_DIR}" || true
  mkdir -p "${WP_NOW_DIR}"
fi

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
nohup npx -y @wp-now/wp-now@${WP_NOW_PKG_VERSION} start --port 8888 >/tmp/wp-now.log 2>&1 &
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

pkill -f '@wp-now/wp-now' 2>/dev/null || true
: > /tmp/wp-now.log || true
nohup npx -y @wp-now/wp-now@0.10.0 start --port 8888 >/tmp/wp-now.log 2>&1 &
disown

for i in $(seq 1 40); do
  code=$(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8888/ || echo 000)
  echo "health=$code"
  if [[ "$code" == "200" || "$code" == "301" ]]; then
    exit 0
  fi
  sleep 1
done

echo "Server did not become healthy in time (after fallback)"
tail -n 200 /tmp/wp-now.log || true
curl -sS http://127.0.0.1:8888/ || true
exit 1
