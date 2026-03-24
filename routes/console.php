<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

$notifyOps = static function (string $message, string $level = 'error', string $context = ''): void {
    try {
        Artisan::call('ops:notify', [
            'message' => $message,
            '--level' => $level,
            '--context' => $context,
        ]);
    } catch (\Throwable) {
        // Nunca deixa uma notificacao quebrar o fluxo principal.
    }
};

$normalizeDbHost = static function (string $host): string {
    return in_array(strtolower($host), ['localhost', '::1'], true) ? '127.0.0.1' : $host;
};

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ops:readiness', function () {
    $items = [];

    $items[] = ['APP_DEBUG', config('app.debug') ? 'fail' : 'ok', config('app.debug') ? 'must be false in production' : ''];
    $items[] = ['APP_FORCE_HTTPS', config('app.force_https') ? 'ok' : 'warn', config('app.force_https') ? '' : 'recommended true in production'];
    $items[] = ['QUEUE_CONNECTION', config('queue.default') === 'database' ? 'ok' : 'warn', config('queue.default') === 'database' ? '' : 'database recommended for reliability'];

    try {
        DB::select('select 1');
        $items[] = ['DB', 'ok', ''];
    } catch (\Throwable $e) {
        $items[] = ['DB', 'fail', $e->getMessage()];
    }

    try {
        $k = 'ops:readiness:'.uniqid('', true);
        Cache::put($k, 'ok', 10);
        $ok = Cache::get($k) === 'ok';
        Cache::forget($k);
        $items[] = ['Cache', $ok ? 'ok' : 'fail', $ok ? '' : 'cache read/write failed'];
    } catch (\Throwable $e) {
        $items[] = ['Cache', 'fail', $e->getMessage()];
    }

    if (Schema::hasTable('failed_jobs')) {
        $failed = DB::table('failed_jobs')->count();
        $items[] = ['failed_jobs', $failed > 0 ? 'warn' : 'ok', "count={$failed}"];
    }

    $this->table(['Check', 'Status', 'Details'], $items);

    $hasFail = collect($items)->contains(fn ($i) => $i[1] === 'fail');

    return $hasFail ? 1 : 0;
})->purpose('Run production readiness checks (db/cache/queue/security)');

Artisan::command('ops:notify {message} {--level=error} {--context=}', function () {
    $message = (string) $this->argument('message');
    $level = strtoupper((string) $this->option('level'));
    $context = (string) $this->option('context');

    $fullMessage = "[{$level}] {$message}";
    if ($context !== '') {
        $fullMessage .= "\nContexto: {$context}";
    }

    $appName = (string) config('app.name', 'Amigo Secreto');
    $appUrl = (string) config('app.url', '');
    $timestamp = now()->toIso8601String();

    $telegramToken = (string) (config('services.telegram.bot_token') ?? '');
    $telegramChatId = (string) (config('services.telegram.chat_id') ?? '');

    // Regra solicitada: se Telegram estiver configurado, usa APENAS Telegram.
    if ($telegramToken !== '' && $telegramChatId !== '') {
        $text = "{$appName}\n{$fullMessage}\nHora: {$timestamp}";
        if ($appUrl !== '') {
            $text .= "\nURL: {$appUrl}";
        }

        $response = Http::asForm()
            ->timeout(10)
            ->post("https://api.telegram.org/bot{$telegramToken}/sendMessage", [
                'chat_id' => $telegramChatId,
                'text' => $text,
                'disable_web_page_preview' => true,
            ]);

        if (! $response->successful()) {
            $this->error('Falha ao enviar alerta para o Telegram.');

            return 1;
        }

        $this->info('Alerta enviado via Telegram.');

        return 0;
    }

    $alertEmail = (string) (config('services.ops.alert_email') ?? '');

    if ($alertEmail === '') {
        $this->error('Nenhum canal de alerta configurado (Telegram ou OPS_ALERT_EMAIL).');

        return 1;
    }

    $subject = "[{$appName}] [{$level}] Alerta operacional";
    $body = "Aplicacao: {$appName}\nMensagem: {$message}\nNivel: {$level}\nHora: {$timestamp}";

    if ($context !== '') {
        $body .= "\nContexto: {$context}";
    }

    if ($appUrl !== '') {
        $body .= "\nURL: {$appUrl}";
    }

    Mail::raw($body, function ($mail) use ($alertEmail, $subject): void {
        $mail->to($alertEmail)->subject($subject);
    });

    $this->info('Alerta enviado via e-mail.');

    return 0;
})->purpose('Send ops alert: Telegram first, fallback to email when Telegram is not configured');

Artisan::command('ops:health-check {--url=}', function () use ($notifyOps) {
    $healthUrl = trim((string) $this->option('url'));

    if ($healthUrl === '') {
        $healthUrl = trim((string) config('services.ops.healthcheck_url', ''));
    }

    if ($healthUrl === '') {
        $appUrl = rtrim((string) config('app.url', ''), '/');
        if ($appUrl !== '') {
            $healthUrl = $appUrl.'/healthz';
        }
    }

    if ($healthUrl === '') {
        $this->error('Nao foi possivel determinar a URL de health check. Configure OPS_HEALTHCHECK_URL ou APP_URL.');

        return 1;
    }

    try {
        $response = Http::timeout(10)->acceptJson()->get($healthUrl);
    } catch (\Throwable $e) {
        $notifyOps("Health check inacessivel em {$healthUrl}: {$e->getMessage()}", 'error', 'healthz');
        $this->error("Falha ao consultar health endpoint: {$healthUrl}");

        return 2;
    }

    if (! $response->successful()) {
        $notifyOps("Health check retornou HTTP {$response->status()} em {$healthUrl}", 'error', 'healthz');
        $this->error("Health endpoint retornou HTTP {$response->status()}");

        return 2;
    }

    $payload = $response->json();
    $status = is_array($payload) ? (string) ($payload['status'] ?? 'unknown') : 'unknown';
    $details = [];

    if (is_array($payload) && isset($payload['checks']) && is_array($payload['checks'])) {
        foreach ($payload['checks'] as $checkName => $checkValue) {
            if (is_string($checkValue) && $checkValue !== 'ok') {
                $details[] = "{$checkName}={$checkValue}";
                continue;
            }

            if (is_array($checkValue)) {
                $state = (string) ($checkValue['status'] ?? $checkValue['state'] ?? 'unknown');
                if (! in_array($state, ['ok', 'pass'], true)) {
                    $detail = (string) ($checkValue['details'] ?? '');
                    $details[] = $detail !== ''
                        ? "{$checkName}={$state} ({$detail})"
                        : "{$checkName}={$state}";
                }
            }
        }
    }

    $detailText = $details !== [] ? implode(' | ', $details) : 'Sem detalhes adicionais no payload';

    if ($status === 'fail' || $status === 'unknown') {
        $notifyOps(
            "Health check com falha: status={$status}. Divergencias: {$detailText}. Verifique storage/logs/laravel.log",
            'error',
            'healthz'
        );
        Log::error('ops.health_check.failed', [
            'url' => $healthUrl,
            'status' => $status,
            'details' => $details,
            'payload' => $payload,
        ]);
        $this->error("Health check falhou com status={$status}");

        return 2;
    }

    if ($status === 'degraded') {
        $notifyOps(
            "Health check degradado. Divergencias: {$detailText}. Verifique storage/logs/laravel.log",
            'warning',
            'healthz'
        );
        Log::warning('ops.health_check.degraded', [
            'url' => $healthUrl,
            'details' => $details,
            'payload' => $payload,
        ]);
        $this->warn('Health check degradado.');

        return 1;
    }

    $this->info('Health check saudavel.');

    return 0;
})->purpose('Run health check against OPS_HEALTHCHECK_URL (or APP_URL/healthz) and notify on degradation/failure');

Artisan::command('ops:backup-db {--output=} {--retention-days=}', function () use ($notifyOps, $normalizeDbHost) {
    $connectionName = (string) config('database.default');
    $connection = config("database.connections.{$connectionName}");

    if (! is_array($connection)) {
        $this->error("Conexao de banco invalida: {$connectionName}");

        return 1;
    }

    $driver = (string) ($connection['driver'] ?? '');
    if (! in_array($driver, ['mysql', 'mariadb', 'pgsql', 'sqlite'], true)) {
        $this->error("Driver nao suportado para backup: {$driver}. Use mysql/mariadb/pgsql/sqlite.");

        return 1;
    }

    $outDir = trim((string) $this->option('output'));
    if ($outDir === '') {
        $outDir = storage_path('backups');
    }

    if (! is_dir($outDir) && ! @mkdir($outDir, 0755, true) && ! is_dir($outDir)) {
        $notifyOps("Falha ao criar diretorio de backup: {$outDir}", 'error', 'backup-db');
        $this->error("Nao foi possivel criar o diretorio de backup: {$outDir}");

        return 1;
    }

    $retentionDays = (int) $this->option('retention-days');
    if ($retentionDays <= 0) {
        $retentionDays = (int) config('services.ops.backup_retention_days', 14);
    }
    if ($retentionDays <= 0) {
        $retentionDays = 14;
    }

    $timestamp = now()->format('Ymd_His');
    $database = (string) ($connection['database'] ?? '');

    $env = [];
    $outFile = '';
    $shellCommand = '';

    if ($driver === 'sqlite') {
        if ($database === '' || $database === ':memory:') {
            $this->error('Backup sqlite requer arquivo fisico em database.connections.*.database.');

            return 1;
        }

        $sqlitePath = str_starts_with($database, DIRECTORY_SEPARATOR) ? $database : base_path($database);
        if (! is_file($sqlitePath)) {
            $this->error("Arquivo sqlite nao encontrado: {$sqlitePath}");

            return 1;
        }

        $outFile = $outDir.DIRECTORY_SEPARATOR."backup_sqlite_{$timestamp}.sqlite";

        if (! @copy($sqlitePath, $outFile)) {
            $notifyOps("Falha ao copiar arquivo sqlite para backup: {$sqlitePath}", 'error', 'backup-db');
            $this->error('Falha ao criar backup sqlite.');

            return 1;
        }
    } elseif ($driver === 'pgsql') {
        $host = $normalizeDbHost((string) ($connection['host'] ?? '127.0.0.1'));
        $port = (string) ($connection['port'] ?? '5432');
        $username = (string) ($connection['username'] ?? '');
        $password = (string) ($connection['password'] ?? '');

        if ($database === '' || $username === '') {
            $this->error('Configuracao de banco incompleta para backup (database/username).');

            return 1;
        }

        $outFile = $outDir.DIRECTORY_SEPARATOR."backup_pgsql_{$timestamp}.sql";
        $shellCommand = sprintf(
            'pg_dump --host=%s --port=%s --username=%s --dbname=%s --format=plain --no-owner --no-privileges > %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($outFile)
        );
        $env['PGPASSWORD'] = $password;
    } else {
        $host = $normalizeDbHost((string) ($connection['host'] ?? '127.0.0.1'));
        $port = (string) ($connection['port'] ?? '3306');
        $username = (string) ($connection['username'] ?? '');
        $password = (string) ($connection['password'] ?? '');

        if ($database === '' || $username === '') {
            $this->error('Configuracao de banco incompleta para backup (database/username).');

            return 1;
        }

        $dumpBinary = trim((string) config('services.ops.mysql_dump_binary', ''));
        if ($dumpBinary === '') {
            $dumpBinary = is_file('/usr/bin/mariadb-dump') ? '/usr/bin/mariadb-dump' : 'mysqldump';
        }

        $outFile = $outDir.DIRECTORY_SEPARATOR."backup_mysql_{$timestamp}.sql";
        $shellCommand = sprintf(
            '%s --protocol=TCP --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($dumpBinary),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($outFile)
        );
    }

    if ($shellCommand !== '') {
        $process = Process::fromShellCommandline($shellCommand, base_path(), $env);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput());
            if ($errorOutput === '') {
                $errorOutput = trim($process->getOutput());
            }

            $notifyOps('Falha no backup de banco'.($errorOutput !== '' ? ": {$errorOutput}" : ''), 'error', 'backup-db');
            $this->error('Backup falhou.'.($errorOutput !== '' ? " {$errorOutput}" : ''));

            return 1;
        }
    }

    if (! is_file($outFile)) {
        $notifyOps("Backup concluido sem arquivo valido: {$outFile}", 'error', 'backup-db');
        $this->error("Arquivo de backup nao encontrado: {$outFile}");

        return 1;
    }

    $size = (int) (@filesize($outFile) ?: 0);
    if ($size <= 0) {
        $notifyOps("Backup gerado com tamanho zero: {$outFile}", 'error', 'backup-db');
        $this->error('Backup gerado com tamanho zero.');

        return 1;
    }

    $integrityOk = true;
    $integrityNotes = [];
    $sample = @file_get_contents($outFile, false, null, 0, 262144);

    if ($driver === 'sqlite') {
        try {
            $pdo = new PDO('sqlite:'.$outFile);
            $check = (string) $pdo->query('PRAGMA integrity_check')->fetchColumn();
            if (strtolower($check) !== 'ok') {
                $integrityOk = false;
                $integrityNotes[] = "sqlite_integrity={$check}";
            } else {
                $integrityNotes[] = 'sqlite_integrity=ok';
            }
        } catch (\Throwable $e) {
            $integrityOk = false;
            $integrityNotes[] = 'sqlite_integrity=erro';
            $integrityNotes[] = $e->getMessage();
        }
    } else {
        $sampleText = is_string($sample) ? strtoupper($sample) : '';
        $hasStructure = str_contains($sampleText, 'CREATE TABLE')
            || str_contains($sampleText, 'INSERT INTO')
            || str_contains($sampleText, 'MYSQL DUMP')
            || str_contains($sampleText, 'MARIADB DUMP')
            || str_contains($sampleText, 'POSTGRESQL DATABASE DUMP');

        if (! $hasStructure) {
            $integrityOk = false;
            $integrityNotes[] = 'assinatura_sql_ausente';
        } else {
            $integrityNotes[] = 'assinatura_sql=ok';
        }
    }

    $sha256 = @hash_file('sha256', $outFile) ?: 'n/a';
    $integritySummary = $integrityNotes !== [] ? implode(', ', $integrityNotes) : 'sem_notas';

    if (! $integrityOk) {
        $notifyOps(
            "Backup criado, mas integridade basica falhou. Arquivo: ".basename($outFile).". Verifique storage/logs/laravel.log",
            'error',
            'backup-db'
        );
        Log::error('ops.backup.integrity_failed', [
            'file' => $outFile,
            'size' => $size,
            'sha256' => $sha256,
            'notes' => $integrityNotes,
            'driver' => $driver,
        ]);
        $this->error("Integridade basica do backup falhou: {$integritySummary}");

        return 1;
    }

    $threshold = now()->subDays($retentionDays)->getTimestamp();
    $deleted = 0;

    foreach (glob($outDir.DIRECTORY_SEPARATOR.'backup_*.*') ?: [] as $file) {
        $mtime = @filemtime($file);
        if ($mtime !== false && $mtime < $threshold && @unlink($file)) {
            $deleted++;
        }
    }

    $this->info("Backup criado em: {$outFile}");
    $this->info("Backups antigos removidos: {$deleted}");
    $this->info("SHA256: {$sha256}");
    $notifyOps(
        'Backup realizado com integridade basica OK. Arquivo: '.basename($outFile).". Tamanho: {$size} bytes. SHA256: {$sha256}",
        'info',
        'backup-db'
    );
    Log::info('ops.backup.completed', [
        'file' => $outFile,
        'size' => $size,
        'sha256' => $sha256,
        'deleted_old_backups' => $deleted,
        'driver' => $driver,
    ]);

    return 0;
})->purpose('Create DB backup based on Laravel DB config and prune old files');

Artisan::command('ops:check-failed-mails', function () use ($notifyOps) {
    if (! Schema::hasTable('failed_jobs')) {
        $this->info('Tabela failed_jobs nao encontrada. Nada para monitorar.');

        return 0;
    }

    $cursorKey = 'ops:last_failed_jobs_cursor';
    $lastSeenId = (int) Cache::get($cursorKey, 0);

    $query = DB::table('failed_jobs')->orderBy('id');
    if ($lastSeenId > 0) {
        $query->where('id', '>', $lastSeenId);
    }

    $failedRows = $query->get(['id', 'queue', 'payload', 'exception', 'failed_at']);

    if ($failedRows->isEmpty()) {
        $this->info('Nenhuma nova falha de fila.');

        return 0;
    }

    $maxId = (int) $failedRows->max('id');

    // Primeira execucao: inicializa cursor sem alertar historico antigo.
    if ($lastSeenId === 0) {
        Cache::forever($cursorKey, $maxId);
        $this->info('Cursor inicializado para monitoramento de failed_jobs.');

        return 0;
    }

    $mailFailures = $failedRows->filter(function ($row) {
        $payload = (string) ($row->payload ?? '');
        $exception = (string) ($row->exception ?? '');

        return str_contains($payload, 'Illuminate\\Mail\\')
            || str_contains($payload, 'App\\Mail\\')
            || str_contains($payload, 'SendQueuedMailable')
            || str_contains($exception, 'Swift_')
            || str_contains($exception, 'Symfony\\Component\\Mailer')
            || str_contains($exception, 'SMTP');
    })->values();

    Cache::forever($cursorKey, $maxId);

    if ($mailFailures->isEmpty()) {
        $this->info('Novas falhas detectadas, mas sem indicio de e-mail.');

        return 0;
    }

    $first = $mailFailures->first();
    $firstError = preg_replace('/\s+/', ' ', trim((string) ($first->exception ?? 'erro desconhecido')));
    $firstError = substr($firstError, 0, 220);
    $count = $mailFailures->count();

    $message = "Falha de envio de e-mail detectada em fila ({$count} novo(s)). ".
        "Erro: {$firstError}. Verifique storage/logs/laravel.log e rode 'php artisan queue:failed'.";

    $notifyOps($message, 'error', 'queue-mail');
    Log::error('ops.queue.mail_failed', [
        'count' => $count,
        'first_failed_job_id' => $first->id ?? null,
        'first_queue' => $first->queue ?? null,
        'first_error' => $firstError,
    ]);

    $this->error($message);

    return 1;
})->purpose('Notify when new failed_jobs indicate email delivery failures');

Artisan::command('ops:restore-db {file} {--force} {--skip-pre-backup}', function () use ($notifyOps, $normalizeDbHost) {
    if (! (bool) $this->option('force')) {
        $this->error('Por seguranca, confirme com --force para executar restore.');

        return 1;
    }

    $fileInput = trim((string) $this->argument('file'));
    $restoreFile = str_starts_with($fileInput, DIRECTORY_SEPARATOR) ? $fileInput : base_path($fileInput);

    if (! is_file($restoreFile)) {
        $this->error("Arquivo de restore nao encontrado: {$restoreFile}");

        return 1;
    }

    if (! (bool) $this->option('skip-pre-backup')) {
        $this->info('Criando backup preventivo antes do restore...');
        $code = Artisan::call('ops:backup-db');
        if ($code !== 0) {
            $notifyOps('Restore abortado: falha no backup preventivo.', 'error', 'restore-db');
            $this->error('Restore abortado: backup preventivo falhou.');

            return 1;
        }
    }

    $connectionName = (string) config('database.default');
    $connection = config("database.connections.{$connectionName}");

    if (! is_array($connection)) {
        $this->error("Conexao de banco invalida: {$connectionName}");

        return 1;
    }

    $driver = (string) ($connection['driver'] ?? '');
    $database = (string) ($connection['database'] ?? '');
    $env = [];
    $shellCommand = '';

    if ($driver === 'sqlite') {
        if ($database === '' || $database === ':memory:') {
            $this->error('Restore sqlite requer arquivo fisico em database.connections.*.database.');

            return 1;
        }

        $targetPath = str_starts_with($database, DIRECTORY_SEPARATOR) ? $database : base_path($database);
        if (! @copy($restoreFile, $targetPath)) {
            $notifyOps("Restore sqlite falhou ao copiar arquivo para {$targetPath}", 'error', 'restore-db');
            $this->error('Falha ao copiar arquivo sqlite para destino.');

            return 1;
        }

        try {
            $pdo = new PDO('sqlite:'.$targetPath);
            $check = (string) $pdo->query('PRAGMA integrity_check')->fetchColumn();
            if (strtolower($check) !== 'ok') {
                throw new RuntimeException("PRAGMA integrity_check retornou: {$check}");
            }
        } catch (\Throwable $e) {
            $notifyOps("Restore sqlite concluiu copia, mas falhou integridade: {$e->getMessage()}", 'error', 'restore-db');
            $this->error("Restore sqlite falhou na validacao: {$e->getMessage()}");

            return 1;
        }
    } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
        $host = $normalizeDbHost((string) ($connection['host'] ?? '127.0.0.1'));
        $port = (string) ($connection['port'] ?? '3306');
        $username = (string) ($connection['username'] ?? '');
        $password = (string) ($connection['password'] ?? '');

        if ($database === '' || $username === '') {
            $this->error('Configuracao de banco incompleta para restore (database/username).');

            return 1;
        }

        $restoreBinary = trim((string) config('services.ops.mysql_restore_binary', ''));
        if ($restoreBinary === '') {
            $restoreBinary = is_file('/usr/bin/mariadb') ? '/usr/bin/mariadb' : 'mysql';
        }

        $shellCommand = sprintf(
            '%s --protocol=TCP --host=%s --port=%s --user=%s --password=%s %s < %s',
            escapeshellarg($restoreBinary),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($restoreFile)
        );
    } elseif ($driver === 'pgsql') {
        $host = $normalizeDbHost((string) ($connection['host'] ?? '127.0.0.1'));
        $port = (string) ($connection['port'] ?? '5432');
        $username = (string) ($connection['username'] ?? '');
        $password = (string) ($connection['password'] ?? '');

        if ($database === '' || $username === '') {
            $this->error('Configuracao de banco incompleta para restore (database/username).');

            return 1;
        }

        $shellCommand = sprintf(
            'psql --host=%s --port=%s --username=%s --dbname=%s -f %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($restoreFile)
        );
        $env['PGPASSWORD'] = $password;
    } else {
        $this->error("Driver nao suportado para restore: {$driver}");

        return 1;
    }

    if ($shellCommand !== '') {
        $process = Process::fromShellCommandline($shellCommand, base_path(), $env);
        $process->setTimeout(1800);
        $process->run();

        if (! $process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput());
            if ($errorOutput === '') {
                $errorOutput = trim($process->getOutput());
            }

            $notifyOps('Restore de banco falhou: '.($errorOutput !== '' ? $errorOutput : 'erro desconhecido'), 'error', 'restore-db');
            $this->error('Restore falhou.'.($errorOutput !== '' ? " {$errorOutput}" : ''));

            return 1;
        }
    }

    $notifyOps(
        'Restore de banco concluido com sucesso. Arquivo: '.basename($restoreFile).". Verifique storage/logs/laravel.log",
        'warning',
        'restore-db'
    );
    Log::warning('ops.restore.completed', [
        'file' => $restoreFile,
        'driver' => $driver,
    ]);
    $this->info('Restore concluido com sucesso.');

    return 0;
})->purpose('Restore database from backup file (requires --force, with optional pre-backup)');

Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=120')
    ->everyMinute();

Schedule::command('ops:health-check')
    ->everyFiveMinutes();

Schedule::command('ops:check-failed-mails')
    ->everyFiveMinutes();

Schedule::command('ops:backup-db')
    ->dailyAt('02:30');
