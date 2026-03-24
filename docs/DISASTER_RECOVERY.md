# Disaster Recovery (Restore de Banco)

Este runbook descreve o processo de restauracao do banco em caso de desastre.

Projeto:
`/home/u810081012/domains/on3digital.com.br/public_html/secretFriend`

## 1) Pre-condicoes

- Aplicacao em manutencao (recomendado):

```bash
php artisan down --render="errors::503"
```

- Confirmar que o arquivo de backup existe e esta acessivel no servidor.
- Confirmar variaveis de conexao no `.env`.

## 2) Backup preventivo antes do restore

O comando de restore faz backup preventivo automaticamente (a menos que use `--skip-pre-backup`).

## 3) Restore efetivo

### 3.1 MySQL/MariaDB

```bash
cd /home/u810081012/domains/on3digital.com.br/public_html/secretFriend
php artisan ops:restore-db /caminho/do/backup.sql --force
```

### 3.2 PostgreSQL

```bash
cd /home/u810081012/domains/on3digital.com.br/public_html/secretFriend
php artisan ops:restore-db /caminho/do/backup.sql --force
```

### 3.3 SQLite

```bash
cd /home/u810081012/domains/on3digital.com.br/public_html/secretFriend
php artisan ops:restore-db /caminho/do/backup.sqlite --force
```

## 4) Pos-restore

```bash
php artisan optimize:clear
php artisan optimize
php artisan ops:readiness
php artisan queue:failed
```

- Validar endpoint de saude:

```bash
curl -sS https://sfriend.on3digital.com.br/healthz
```

- Validar fluxo funcional minimo (login e acesso ao dashboard).

## 5) Logs e alertas

- Log principal: `storage/logs/laravel.log`
- Falhas de fila: `php artisan queue:failed`
- O restore envia notificacao operacional via `ops:notify` em sucesso/falha.

## 6) Comandos de seguranca

- Restaurar sem backup preventivo (somente se ja existir snapshot confiavel):

```bash
php artisan ops:restore-db /caminho/do/backup.sql --force --skip-pre-backup
```

- Voltar aplicacao ao ar:

```bash
php artisan up
```
