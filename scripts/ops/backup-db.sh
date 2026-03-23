#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
ENV_FILE="${ROOT_DIR}/.env"
OUT_DIR="${1:-${ROOT_DIR}/storage/backups}"

notify() {
  local level="$1"
  local msg="$2"
  php "$ROOT_DIR/artisan" ops:notify "$msg" --level="$level" --context="backup-db" >/dev/null 2>&1 || true
}

on_error() {
  notify "error" "Falha no backup de banco"
}
trap on_error ERR

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Arquivo .env nao encontrado em $ENV_FILE" >&2
  exit 1
fi

mkdir -p "$OUT_DIR"

# Carrega variaveis do .env ignorando comentarios.
set -a
source <(grep -E '^[A-Za-z_][A-Za-z0-9_]*=' "$ENV_FILE" | sed 's/\r$//')
set +a

TS="$(date +%Y%m%d_%H%M%S)"

case "${DB_CONNECTION:-}" in
  mysql)
    : "${DB_DATABASE:?DB_DATABASE ausente}"
    : "${DB_USERNAME:?DB_USERNAME ausente}"
    : "${DB_HOST:=127.0.0.1}"
    : "${DB_PORT:=3306}"

    OUT_FILE="$OUT_DIR/backup_mysql_${TS}.sql"
    mysqldump \
      --host="$DB_HOST" \
      --port="$DB_PORT" \
      --user="$DB_USERNAME" \
      --password="$DB_PASSWORD" \
      "$DB_DATABASE" > "$OUT_FILE"
    ;;
  pgsql)
    : "${DB_DATABASE:?DB_DATABASE ausente}"
    : "${DB_USERNAME:?DB_USERNAME ausente}"
    : "${DB_HOST:=127.0.0.1}"
    : "${DB_PORT:=5432}"

    OUT_FILE="$OUT_DIR/backup_pgsql_${TS}.sql"
    PGPASSWORD="$DB_PASSWORD" pg_dump \
      --host="$DB_HOST" \
      --port="$DB_PORT" \
      --username="$DB_USERNAME" \
      --dbname="$DB_DATABASE" \
      --format=plain \
      --no-owner \
      --no-privileges > "$OUT_FILE"
    ;;
  *)
    echo "DB_CONNECTION nao suportado: ${DB_CONNECTION:-vazio}. Use mysql ou pgsql." >&2
    exit 1
    ;;
esac

# Mantem backups dos ultimos 14 dias.
find "$OUT_DIR" -type f -name 'backup_*.sql' -mtime +14 -delete

echo "Backup criado em: $OUT_FILE"
