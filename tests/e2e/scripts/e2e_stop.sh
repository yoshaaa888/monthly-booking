#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/../../.."
docker compose -f dev/docker-compose.yml down -v || true
