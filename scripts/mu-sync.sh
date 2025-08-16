#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.."; pwd)"
SRC_MAIN="$ROOT/mu"
SRC_TEST="$ROOT/test-environment/mu-plugins"
HOME_MU="$HOME/.wp-now/mu-plugins"
VERSIONS="$HOME/.wp-now/wordpress-versions"

mkdir -p "$HOME_MU"
rsync -a "$SRC_MAIN"/ "$HOME_MU"/
if [ -d "$SRC_TEST" ]; then
  rsync -a "$SRC_TEST"/ "$HOME_MU"/
fi
rm -f "$HOME_MU/mb-qa-live.php" 2>/dev/null || true
echo "Synced MU -> $HOME_MU"

if [ -d "$VERSIONS" ]; then
  for v in "$VERSIONS"/*; do
    [ -d "$v" ] || continue
    if [ -d "$v/wp-content" ]; then
      dst="$v/wp-content/mu-plugins"
      mkdir -p "$dst"
      rsync -a "$SRC_MAIN"/ "$dst"/
      if [ -d "$SRC_TEST" ]; then
        rsync -a "$SRC_TEST"/ "$dst"/
      fi
      rm -f "$dst/mb-qa-live.php" 2>/dev/null || true
      echo "Synced MU -> $dst"
    fi
  done
fi
