#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

if ! php "$ROOT_DIR/artisan" queue:work --stop-when-empty --tries=3 --timeout=120; then
  php "$ROOT_DIR/artisan" ops:notify "Falha na execucao do worker de fila (modo cron)" --level=error --context="queue-cron" >/dev/null 2>&1 || true
  exit 1
fi
