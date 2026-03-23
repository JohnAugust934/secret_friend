<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

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
