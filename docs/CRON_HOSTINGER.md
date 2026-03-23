# Cron na Hostinger (Linux)

Este guia assume que seu projeto esta em:
`/home/SEU_USUARIO/domains/SEU_DOMINIO/public_html`

Ajuste o caminho conforme seu painel Hostinger.

## 1) Configurar canal de alerta

No `.env` de producao:

- Telegram (prioridade, se configurado):
  - `TELEGRAM_BOT_TOKEN=...`
  - `TELEGRAM_CHAT_ID=...`
- Fallback e-mail (somente se Telegram NAO estiver configurado):
  - `OPS_ALERT_EMAIL=seu-email@dominio.com`

Regra implementada:
- Se Telegram estiver configurado, alerta vai somente para Telegram.
- Se Telegram nao estiver configurado, alerta vai para e-mail.

## 2) Permissoes iniciais

Execute via SSH uma vez:

```bash
cd /home/SEU_USUARIO/domains/SEU_DOMINIO/public_html
chmod +x scripts/ops/*.sh
mkdir -p storage/backups storage/logs
```

## 3) Comandos recomendados no Cron Jobs

### 3.1 Health check a cada 5 minutos (com alerta automatico)

```cron
*/5 * * * * cd /home/SEU_USUARIO/domains/SEU_DOMINIO/public_html && ./scripts/ops/check-health.sh https://SEU_DOMINIO/healthz >> storage/logs/health-cron.log 2>&1
```

### 3.2 Backup diario as 02:30 (com alerta em falha)

```cron
30 2 * * * cd /home/SEU_USUARIO/domains/SEU_DOMINIO/public_html && ./scripts/ops/backup-db.sh >> storage/logs/backup-cron.log 2>&1
```

### 3.3 Worker de fila fallback (shared hosting) a cada minuto (com alerta em falha)

```cron
* * * * * cd /home/SEU_USUARIO/domains/SEU_DOMINIO/public_html && ./scripts/ops/run-queue-once.sh >> storage/logs/queue-cron.log 2>&1
```

### 3.4 Scheduler do Laravel a cada minuto (com alerta em falha)

```cron
* * * * * cd /home/SEU_USUARIO/domains/SEU_DOMINIO/public_html && ./scripts/ops/run-scheduler.sh >> storage/logs/scheduler-cron.log 2>&1
```

## 4) Como cadastrar no painel Hostinger

1. Acesse `hPanel > Advanced > Cron Jobs`.
2. Clique em `Create cron job`.
3. Cole um dos comandos acima no campo de comando.
4. Configure o agendamento conforme cada exemplo.
5. Salve e valide os logs em `storage/logs`.

## 5) Testes manuais rapidos

### 5.1 Testar envio de alerta (sem erro real)

```bash
cd /home/SEU_USUARIO/domains/SEU_DOMINIO/public_html
php artisan ops:notify "Teste manual de alerta" --level=warning --context=manual-test
```

### 5.2 Validar saude

```bash
cd /home/SEU_USUARIO/domains/SEU_DOMINIO/public_html
php artisan ops:readiness
curl -sS https://SEU_DOMINIO/healthz
```

## 6) Variaveis obrigatorias recomendadas

No `.env` de producao, configure tambem:

```env
OPS_STATUS_ALLOWED_EMAILS=admin@seu-dominio.com
```

Isso libera acesso ao painel `https://SEU_DOMINIO/ops/status`.
