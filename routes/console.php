<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

    if ($status === 'fail' || $status === 'unknown') {
        $notifyOps("Health check com falha: status={$status}", 'error', 'healthz');
        $this->error("Health check falhou com status={$status}");

        return 2;
    }

    if ($status === 'degraded') {
        $notifyOps('Health check degradado', 'warning', 'healthz');
        $this->warn('Health check degradado.');

        return 1;
    }

    $this->info('Health check saudavel.');

    return 0;
})->purpose('Run health check against OPS_HEALTHCHECK_URL (or APP_URL/healthz) and notify on degradation/failure');

Artisan::command('ops:backup-db {--output=} {--retention-days=}', function () use ($notifyOps) {
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
        $host = (string) ($connection['host'] ?? '127.0.0.1');
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
        $host = (string) ($connection['host'] ?? '127.0.0.1');
        $port = (string) ($connection['port'] ?? '3306');
        $username = (string) ($connection['username'] ?? '');
        $password = (string) ($connection['password'] ?? '');

        if ($database === '' || $username === '') {
            $this->error('Configuracao de banco incompleta para backup (database/username).');

            return 1;
        }

        $outFile = $outDir.DIRECTORY_SEPARATOR."backup_mysql_{$timestamp}.sql";
        $shellCommand = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
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

    $threshold = now()->subDays($retentionDays)->getTimestamp();
    $deleted = 0;

    foreach (glob($outDir.DIRECTORY_SEPARATOR.'backup_*.sql') ?: [] as $file) {
        $mtime = @filemtime($file);
        if ($mtime !== false && $mtime < $threshold && @unlink($file)) {
            $deleted++;
        }
    }

    $this->info("Backup criado em: {$outFile}");
    $this->info("Backups antigos removidos: {$deleted}");

    return 0;
})->purpose('Create DB backup based on Laravel DB config and prune old files');

Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=120')
    ->everyMinute();

Schedule::command('ops:health-check')
    ->everyFiveMinutes();

Schedule::command('ops:backup-db')
    ->dailyAt('02:30');
