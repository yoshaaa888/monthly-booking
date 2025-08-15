#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.."; pwd)"
SRC="$ROOT/mu"
HOME_MU="$HOME/.wp-now/mu-plugins"
VERSIONS="$HOME/.wp-now/wordpress-versions"

mkdir -p "$HOME_MU"
rsync -a --delete "$SRC"/ "$HOME_MU"/
rm -f "$HOME_MU/mb-qa-live.php" 2>/dev/null || true
echo "Synced MU -> $HOME_MU"

if [ -d "$VERSIONS" ]; then
  for v in "$VERSIONS"/*; do
    [ -d "$v" ] || continue
    if [ -d "$v/wp-content" ]; then
      dst="$v/wp-content/mu-plugins"
      mkdir -p "$dst"
      rsync -a --delete "$SRC"/ "$dst"/
      rm -f "$dst/mb-qa-live.php" 2>/dev/null || true
      echo "Synced MU -> $dst"
    fi
  done
fi
