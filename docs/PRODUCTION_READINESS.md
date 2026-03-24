# Guia de Prontidao para Producao

Este documento define o minimo necessario para liberar o sistema com seguranca e estabilidade em ambiente publico.

## 1) Segredos e ambiente

- Gere um novo `APP_KEY` para producao.
- Rotacione credenciais de banco e SMTP.
- Mantenha apenas placeholders em arquivos versionados.
- Nunca commite `.env` com segredos reais.

## 2) Hardening de seguranca

- Mantenha `APP_DEBUG=false` em producao.
- Defina `APP_FORCE_HTTPS=true`.
- Cookies de sessao seguros:
  - `SESSION_SECURE_COOKIE=true`
  - `SESSION_HTTP_ONLY=true`
  - `SESSION_SAME_SITE=lax`
- Mantenha rate limiting em autenticacao e acoes criticas de grupo.

## 3) Confiabilidade de fila

- Use `QUEUE_CONNECTION=database` em producao.
- Em shared hosting, processe fila via Scheduler (cron unico + `schedule:run`).
- Monitore `failed_jobs` e trate falhas rapidamente.

Comando base do worker (executado pelo scheduler):
`php artisan queue:work --stop-when-empty --tries=3 --timeout=120`

## 4) Saude e monitoramento

- Endpoint de saude da aplicacao: `GET /healthz`.
- Endpoint nativo do framework: `GET /up`.
- Trate `/healthz` com status `fail` como incidente.
- Trate `/healthz` com status `degraded` como alerta.

## 5) Gate de CI

Todo PR deve passar em:
- `vendor/bin/pint --test`
- `npm run build`
- `php artisan test`
- teste de cache (`config:cache`, `route:cache`, `view:cache`)

## 6) Backup, monitoramento e alertas

Comandos Artisan operacionais:
- `php artisan ops:backup-db`
- `php artisan ops:health-check`
- `php artisan ops:notify "Teste" --level=warning --context=manual`

Canal de alerta (regra de prioridade):
- Se `TELEGRAM_BOT_TOKEN` e `TELEGRAM_CHAT_ID` estiverem configurados, alerta vai somente para Telegram.
- Se Telegram nao estiver configurado, alerta vai para `OPS_ALERT_EMAIL`.

Variaveis recomendadas:
- `OPS_HEALTHCHECK_URL=${APP_URL}/healthz`
- `OPS_BACKUP_RETENTION_DAYS=14`

Faca simulacao de restore regularmente em staging.

## 7) Checklist pre-go-live

1. `php artisan migrate --force`
2. `php artisan optimize:clear && php artisan optimize`
3. Confirmar cron unico no Hostinger para `schedule:run`.
4. Validar `/healthz` e `/up`.
5. Testar fluxo real: login, convite, sorteio e e-mail.
6. Confirmar ausencia de alertas criticos e `failed_jobs` pendentes.

## 8) Baseline de carga

Use `scripts/ops/k6-smoke.js` antes de cada release e ajuste URL/VUs conforme o ambiente.

## 9) Cron na Hostinger

Veja `docs/CRON_HOSTINGER.md` para configuracao com 1 comando.

## 10) Telemetria e status

- Endpoint de telemetria frontend: `POST /telemetry/frontend`.
- Painel de status operacional: `GET /ops/status`.
- Controle de acesso ao status via `OPS_STATUS_ALLOWED_EMAILS`.
- Canal de telemetria registra erros JS e metricas de navegacao no log da aplicacao.

## 11) Testes E2E e concorrencia

- E2E (Playwright):
  - `npm run e2e`
  - `npm run e2e:headed`
- Carga concorrente (k6):
  - `k6 run scripts/ops/k6-concurrency.js -e BASE_URL=https://SEU_DOMINIO -e GROUP_ID=ID -e INVITE_TOKEN=TOKEN -e COOKIE="laravel_session=..."`
