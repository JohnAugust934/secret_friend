<?php

namespace App\Providers;

use App\Models\GroupMember;
use App\Observers\GroupMemberObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        GroupMember::observe(GroupMemberObserver::class);

        // Force HTTPS when explicitly enabled or in production.
        if (config('app.force_https') || app()->environment('production')) {
            URL::forceScheme('https');
        }

        $this->configureRateLimiters();
    }

    private function configureRateLimiters(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(8)->by($request->input('email').'|'.$request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(6)->by($request->ip());
        });

        RateLimiter::for('password-recovery', function (Request $request) {
            return Limit::perMinute(6)->by($request->input('email').'|'.$request->ip());
        });

        RateLimiter::for('invite-actions', function (Request $request) {
            return Limit::perMinute(30)->by(($request->user()?->id ?? 'guest').'|'.$request->ip());
        });

        RateLimiter::for('draw-actions', function (Request $request) {
            return Limit::perMinute(5)->by(($request->user()?->id ?? 'guest').'|'.$request->ip());
        });

        RateLimiter::for('group-admin-actions', function (Request $request) {
            return Limit::perMinute(20)->by(($request->user()?->id ?? 'guest').'|'.$request->ip());
        });

        RateLimiter::for('health', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // SEGURANÇA: Throttle no confirm-password para impedir brute-force
        // da senha atual em cenários de sessão comprometida.
        RateLimiter::for('confirm-password', function (Request $request) {
            return Limit::perMinute(5)->by(($request->user()?->id ?? 'guest').'|'.$request->ip());
        });
    }
}
