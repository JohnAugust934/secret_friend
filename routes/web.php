<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\Ops\StatusController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TelemetryController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/healthz', function () {
    $checks = [];
    $status = 'ok';

    try {
        DB::select('select 1 as ok');
        $checks['database'] = 'ok';
    } catch (\Throwable $e) {
        $checks['database'] = 'fail';
        $status = 'fail';
    }

    try {
        $key = 'healthz:'.uniqid('', true);
        Cache::put($key, 'ok', 10);
        $checks['cache'] = Cache::get($key) === 'ok' ? 'ok' : 'fail';
        Cache::forget($key);

        if ($checks['cache'] !== 'ok') {
            $status = 'fail';
        }
    } catch (\Throwable $e) {
        $checks['cache'] = 'fail';
        $status = 'fail';
    }

    $queueConnection = config('queue.default');
    $queue = ['connection' => $queueConnection, 'pending' => null, 'failed' => null, 'state' => 'ok'];

    try {
        if ($queueConnection === 'database') {
            if (Schema::hasTable('jobs')) {
                $queue['pending'] = DB::table('jobs')->count();
            }

            if (Schema::hasTable('failed_jobs')) {
                $queue['failed'] = DB::table('failed_jobs')->count();
                if ($queue['failed'] > 0 && $status !== 'fail') {
                    $status = 'degraded';
                    $queue['state'] = 'degraded';
                }
            }
        }
    } catch (\Throwable $e) {
        $queue['state'] = 'fail';
        $status = 'fail';
    }

    $checks['queue'] = $queue;

    return response()->json([
        'status' => $status,
        'checks' => $checks,
        'timestamp' => now()->toIso8601String(),
    ], $status === 'fail' ? 503 : 200);
})->middleware('throttle:health')->name('healthz');

Route::post('/telemetry/frontend', [TelemetryController::class, 'store'])
    ->middleware('throttle:health')
    ->name('telemetry.frontend');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('groups', GroupController::class)->except(['index']);

    Route::post('/groups/{group}/draw', [GroupController::class, 'draw'])
        ->middleware('throttle:draw-actions')
        ->name('groups.draw');

    Route::put('/groups/{group}/wishlist', [GroupController::class, 'updateWishlist'])
        ->middleware('throttle:invite-actions')
        ->name('groups.wishlist.update');

    Route::delete('/groups/{group}/members/{user}', [GroupController::class, 'removeMember'])
        ->middleware('throttle:group-admin-actions')
        ->name('groups.members.destroy');

    Route::get('/groups/{group}/members-list', [GroupController::class, 'membersList'])
        ->middleware('throttle:invite-actions')
        ->name('groups.members.list');

    Route::get('/groups/{group}/members-stream', [GroupController::class, 'membersStream'])
        ->middleware('throttle:invite-actions')
        ->name('groups.members.stream');

    Route::post('/groups/{group}/exclusions', [GroupController::class, 'storeExclusion'])
        ->middleware('throttle:group-admin-actions')
        ->name('groups.exclusions.store');

    Route::delete('/groups/{group}/exclusions/{exclusion}', [GroupController::class, 'destroyExclusion'])
        ->middleware('throttle:group-admin-actions')
        ->name('groups.exclusions.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/ops/status', StatusController::class)->name('ops.status');
});

Route::get('/invite/{token}', [GroupController::class, 'join'])
    ->middleware('throttle:invite-actions')
    ->name('groups.join');

Route::middleware('auth')->group(function () {
    Route::post('/invite/{token}', [GroupController::class, 'joinStore'])
        ->middleware('throttle:invite-actions')
        ->name('groups.join.store');
});

require __DIR__.'/auth.php';
