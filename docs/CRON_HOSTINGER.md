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

# Binarios de dump/restore MySQL-MariaDB (Hostinger)
OPS_MYSQL_DUMP_BINARY=/usr/bin/mariadb-dump
OPS_MYSQL_RESTORE_BINARY=/usr/bin/mariadb

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
- Health check com alerta de divergencias: a cada 5 minutos
- Monitor de falhas de e-mail na fila: a cada 5 minutos
- Backup de banco com validacao de integridade basica: diariamente as 02:30

Tudo controlado em `routes/console.php` via Laravel Scheduler.

## 5) Validacao rapida apos configurar

```bash
cd /home/u810081012/domains/on3digital.com.br/public_html/secretFriend
php artisan schedule:list
php artisan ops:health-check
php artisan ops:check-failed-mails
php artisan ops:backup-db
php artisan queue:work --stop-when-empty --tries=1
php artisan queue:failed
```

Se `queue:failed` vier vazio e os comandos retornarem sem erro, cron e notificacoes estao operando corretamente.

## 6) Troubleshooting rápido

### E-mails de verificação pararam de chegar

**Passo 1** — Diagnóstico rápido (roda SMTP + verifica fila):
```bash
php artisan ops:diagnose-email --to=seu@email.com
```
Se retornar `❌ FALHA no envio SMTP`, o problema é na credencial/conectividade SMTP.  
Se retornar `✅ E-mail de diagnóstico enviado com sucesso!`, o SMTP está OK — o problema é que o **worker de fila não está processando** os jobs pendentes.

**Passo 2** — Verificar jobs presos na fila:
```bash
php artisan queue:work --stop-when-empty --queue=emails,default --tries=3
```

**Passo 3** — Verificar se o cron está rodando na Hostinger:
- Acesse hPanel → Advanced → Cron Jobs e confirme que o cron `schedule:run` está ativo.
- Se o cron foi removido ou parou, cadastre novamente conforme a seção 3 acima.

**Passo 4** — Limpar cache de configuração e otimizar:
```bash
php artisan optimize:clear
php artisan optimize
```

---

- Se backup falhar, valide se `mariadb-dump`/`mysqldump` (MySQL/MariaDB) ou `pg_dump` (PostgreSQL) existe no servidor.
- Em Hostinger, prefira `DB_HOST=127.0.0.1` para evitar tentativas via `::1`.
- Se health check alertar divergência, veja `storage/logs/laravel.log`.
- Se houver falha de e-mail em fila, rode `php artisan queue:failed` e veja `storage/logs/laravel.log`.
- Sempre que alterar `.env`, rode:

```bash
php artisan optimize:clear
php artisan optimize
```

