#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
URL="${1:-http://127.0.0.1:8000/healthz}"

notify() {
  local level="$1"
  local msg="$2"
  php "$ROOT_DIR/artisan" ops:notify "$msg" --level="$level" --context="healthz" >/dev/null 2>&1 || true
}

if ! BODY="$(curl -fsS --max-time 10 "$URL")"; then
  notify "error" "Health check inacessivel em $URL"
  echo "[ERRO] Falha ao consultar health endpoint: $URL" >&2
  exit 2
fi

STATUS="$(printf '%s' "$BODY" | php -r '$j=json_decode(stream_get_contents(STDIN), true); echo $j["status"] ?? "unknown";')"

if [[ "$STATUS" == "fail" || "$STATUS" == "unknown" ]]; then
  notify "error" "Health check com falha: status=$STATUS"
  echo "[ERRO] Health check falhou: $BODY" >&2
  exit 2
fi

if [[ "$STATUS" == "degraded" ]]; then
  notify "warning" "Health check degradado"
  echo "[ALERTA] Health check degradado: $BODY"
  exit 1
fi

echo "[OK] Health check saudavel."
