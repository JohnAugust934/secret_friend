<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('novos usuários recebem notificação de verificação de e-mail ao se registrar', function () {
    Notification::fake();

    $response = $this->post('/register', [
        'name' => 'Teste Email',
        'email' => 'teste@email.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/dashboard'); // Redireciona, mas o middleware vai barrar depois se não verificar

    // Verifica se a notificação de e-mail foi enviada
    Notification::assertSentTo(
        User::where('email', 'teste@email.com')->first(),
        VerifyEmail::class
    );
});

test('usuário pode solicitar link de recuperação de senha', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertSessionHas('status', __("We have emailed your password reset link."));

    // Verifica se a notificação de reset foi enviada
    Notification::assertSentTo(
        $user,
        ResetPassword::class
    );
});
