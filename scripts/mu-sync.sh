#!/usr/bin/env bash
set -e
SRC_DIR="$(cd "$(dirname "$0")/.."; pwd)/mu"
DEST="$HOME/.wp-now/mu-plugins"
mkdir -p "$DEST"
cp -f "$SRC_DIR/"*.php "$DEST/"
echo "Synced MU from $SRC_DIR -> $DEST"
