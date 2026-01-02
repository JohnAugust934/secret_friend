<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
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
        // Observador para limpar cache de grupos
        GroupMember::observe(GroupMemberObserver::class);

        // Personalização do E-mail de Verificação
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verifique seu E-mail - Amigo Secreto')
                ->view('emails.verify-email', [
                    'url' => $url,
                    'name' => $notifiable->name
                ]);
        });
    }
}
