<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\GroupMember;
use App\Observers\GroupMemberObserver;

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
        // Registra o Observer para o Modelo Pivot
        GroupMember::observe(GroupMemberObserver::class);
    }
}
