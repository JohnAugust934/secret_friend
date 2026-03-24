# Cron na Hostinger (Laravel Scheduler com 1 comando)

Este guia esta ajustado para seu caminho real:
`/home/u810081012/domains/on3digital.com.br/public_html/secretFriend`

Objetivo:
- Rodar tudo via Scheduler do Laravel.
- Usar apenas 1 cron no hPanel.
- Sem dependencia dos scripts `.sh` para producao.

## 1) Pre-requisitos (uma vez via SSH)

```bash
cd /home/u810081012/domains/on3digital.com.br/public_html/secretFriend
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

## 2) Configuracao no .env (producao)

```env
QUEUE_CONNECTION=database
MAIL_MAILER=smtp

# Alertas (Telegram tem prioridade)
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=
OPS_ALERT_EMAIL=ops@on3digital.com.br

# URL usada pelo comando ops:health-check
OPS_HEALTHCHECK_URL=${APP_URL}/healthz

# Retencao dos backups em dias
OPS_BACKUP_RETENTION_DAYS=14

# Binario de dump MySQL/MariaDB (Hostinger)
OPS_MYSQL_DUMP_BINARY=/usr/bin/mariadb-dump

# Painel operacional
OPS_STATUS_ALLOWED_EMAILS=admin@on3digital.com.br
```

## 3) Unico cron para cadastrar no hPanel

No `hPanel > Advanced > Cron Jobs`, cadastre:

Tempo:
- `* * * * *`

Comando:

```cron
/opt/alt/php84/usr/bin/php /home/u810081012/domains/on3digital.com.br/public_html/secretFriend/artisan schedule:run >> /dev/null 2>&1
```

## 4) O que esse cron unico vai executar

Agendamentos definidos dentro do sistema:

- Fila (envio de notificacoes/e-mails em fila): a cada 1 minuto
- Health check: a cada 5 minutos
- Backup de banco: diariamente as 02:30

Tudo controlado em `routes/console.php` via Laravel Scheduler.

## 5) Validacao rapida apos configurar

```bash
cd /home/u810081012/domains/on3digital.com.br/public_html/secretFriend
php artisan schedule:list
php artisan ops:health-check
php artisan ops:backup-db
php artisan queue:work --stop-when-empty --tries=1
php artisan queue:failed
```

Se `queue:failed` vier vazio e os comandos retornarem sem erro, cron e notificacoes estao operando corretamente.

## 6) Troubleshooting rapido

- Se backup falhar, valide se `mysqldump` (MySQL) ou `pg_dump` (PostgreSQL) existe no servidor.
- Se health check falhar, valide `APP_URL` e `OPS_HEALTHCHECK_URL`.
- Sempre que alterar `.env`, rode:

```bash
php artisan optimize:clear
php artisan optimize
```

