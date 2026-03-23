<?php

namespace App\Http\Controllers\Ops;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StatusController extends Controller
{
    public function __invoke()
    {
        $this->authorizeAccess();

        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'backups' => $this->checkBackups(),
        ];

        return view('ops.status', [
            'checks' => $checks,
            'overall' => $this->overall($checks),
            'checkedAt' => now(),
        ]);
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('select 1');

            return ['status' => 'ok', 'details' => 'Conexao ativa'];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'details' => $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'ops:status:'.uniqid('', true);
            Cache::put($key, 'ok', 10);
            $ok = Cache::get($key) === 'ok';
            Cache::forget($key);

            return ['status' => $ok ? 'ok' : 'fail', 'details' => $ok ? 'Leitura/gravacao OK' : 'Falha no cache'];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'details' => $e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        try {
            $pending = Schema::hasTable('jobs') ? DB::table('jobs')->count() : null;
            $failed = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : null;
            $status = ($failed ?? 0) > 0 ? 'warning' : 'ok';

            return [
                'status' => $status,
                'details' => 'Pendentes: '.($pending ?? 'N/A').' | Falhos: '.($failed ?? 'N/A'),
                'pending' => $pending,
                'failed' => $failed,
            ];
        } catch (\Throwable $e) {
            return ['status' => 'fail', 'details' => $e->getMessage()];
        }
    }

    private function checkBackups(): array
    {
        $dir = storage_path('backups');

        if (! is_dir($dir)) {
            return ['status' => 'warning', 'details' => 'Pasta de backup nao encontrada'];
        }

        $files = collect(glob($dir.'/backup_*.sql') ?: [])
            ->map(fn ($file) => [
                'name' => basename($file),
                'mtime' => filemtime($file),
                'size' => filesize($file),
            ])
            ->sortByDesc('mtime')
            ->values();

        if ($files->isEmpty()) {
            return ['status' => 'warning', 'details' => 'Nenhum backup encontrado'];
        }

        $latest = $files->first();
        $ageHours = (int) floor((time() - (int) $latest['mtime']) / 3600);
        $status = $ageHours <= 26 ? 'ok' : 'warning';

        return [
            'status' => $status,
            'details' => 'Ultimo backup: '.$latest['name'].' ('.date('d/m/Y H:i', (int) $latest['mtime']).')',
            'latest' => $latest,
            'count' => $files->count(),
        ];
    }

    private function overall(array $checks): string
    {
        if (collect($checks)->contains(fn ($check) => $check['status'] === 'fail')) {
            return 'fail';
        }

        if (collect($checks)->contains(fn ($check) => $check['status'] === 'warning')) {
            return 'warning';
        }

        return 'ok';
    }

    private function authorizeAccess(): void
    {
        $allowed = collect(explode(',', (string) config('services.ops.status_allowed_emails', '')))
            ->map(fn ($email) => trim($email))
            ->filter()
            ->values();

        if ($allowed->isEmpty()) {
            abort_unless(config('app.env') === 'local', 403);

            return;
        }

        abort_unless($allowed->contains(auth()->user()?->email), 403);
    }
}
