#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Uso: ./scripts/ops/restore-db.sh /caminho/do/backup.sql" >&2
  exit 1
fi

BACKUP_FILE="$1"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
ENV_FILE="${ROOT_DIR}/.env"

if [[ ! -f "$BACKUP_FILE" ]]; then
  echo "Arquivo de backup nao encontrado: $BACKUP_FILE" >&2
  exit 1
fi

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Arquivo .env nao encontrado em $ENV_FILE" >&2
  exit 1
fi

set -a
source <(grep -E '^[A-Za-z_][A-Za-z0-9_]*=' "$ENV_FILE" | sed 's/\r$//')
set +a

case "${DB_CONNECTION:-}" in
  mysql)
    : "${DB_DATABASE:?DB_DATABASE ausente}"
    : "${DB_USERNAME:?DB_USERNAME ausente}"
    : "${DB_HOST:=127.0.0.1}"
    : "${DB_PORT:=3306}"

    mysql \
      --host="$DB_HOST" \
      --port="$DB_PORT" \
      --user="$DB_USERNAME" \
      --password="$DB_PASSWORD" \
      "$DB_DATABASE" < "$BACKUP_FILE"
    ;;
  pgsql)
    : "${DB_DATABASE:?DB_DATABASE ausente}"
    : "${DB_USERNAME:?DB_USERNAME ausente}"
    : "${DB_HOST:=127.0.0.1}"
    : "${DB_PORT:=5432}"

    PGPASSWORD="$DB_PASSWORD" psql \
      --host="$DB_HOST" \
      --port="$DB_PORT" \
      --username="$DB_USERNAME" \
      --dbname="$DB_DATABASE" < "$BACKUP_FILE"
    ;;
  *)
    echo "DB_CONNECTION nao suportado: ${DB_CONNECTION:-vazio}. Use mysql ou pgsql." >&2
    exit 1
    ;;
esac

echo "Restore concluido com sucesso."
