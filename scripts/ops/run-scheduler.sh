#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

if ! php "$ROOT_DIR/artisan" schedule:run; then
  php "$ROOT_DIR/artisan" ops:notify "Falha na execucao do scheduler" --level=error --context="schedule-cron" >/dev/null 2>&1 || true
  exit 1
fi
