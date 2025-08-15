#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.."; pwd)"
SRC="$ROOT/mu"
FILES=(mb-qa.php 00-mb-boot.php)

TARGETS=(
  "$HOME/.wp-now/mu-plugins"
  $HOME/.wp-now/wordpress-versions/*/wp-content/mu-plugins
)

for t in "${TARGETS[@]}"; do
  [ -d "$t" ] || continue
  mkdir -p "$t"
  # 旧実験ファイル（redeclare防止）
  rm -f "$t/mb-qa-live.php" 2>/dev/null || true
  for f in "${FILES[@]}"; do
    [ -f "$SRC/$f" ] || continue
    install -m 0644 "$SRC/$f" "$t/$f"
  done
  echo "Synced MU -> $t"
done
