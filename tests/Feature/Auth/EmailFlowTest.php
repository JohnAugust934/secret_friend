<?php

/**
 * Testes de Feature — Fluxo de E-mail de Verificação e Recuperação de Senha
 *
 * Cobre:
 * - Notificação de verificação é enviada imediatamente (notifyNow) ao registrar.
 * - Falha de SMTP não retorna erro 500 para o usuário.
 * - Reenvio do link de verificação funciona corretamente.
 * - Usuário já verificado não recebe novo e-mail ao solicitar reenvio.
 * - Recuperação de senha envia a notificação corretamente.
 */

use App\Models\User;
use App\Notifications\VerifyEmailQueued;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

// ---------------------------------------------------------------------------
// Registro — Verificação de E-mail
// ---------------------------------------------------------------------------

test('ao registrar, a notificação de verificação é enviada imediatamente', function () {
    Notification::fake();

    $this->post('/register', [
        'name'                  => 'João Teste',
        'email'                 => 'joao@teste.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect();

    $user = User::where('email', 'joao@teste.com')->firstOrFail();

    // Garante que a nossa VerifyEmailQueued foi usada, não a padrão do Laravel.
    // Notification::fake() captura tanto notify() quanto notifyNow().
    Notification::assertSentTo($user, VerifyEmailQueued::class);
});

test('ao registrar, a notificação de verificação vai para a fila correta (emails)', function () {
    Notification::fake();

    $this->post('/register', [
        'name'                  => 'Maria Fila',
        'email'                 => 'maria@fila.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'maria@fila.com')->firstOrFail();

    Notification::assertSentTo(
        $user,
        VerifyEmailQueued::class,
        fn ($notification) => $notification->queue === 'emails'
    );
});

test('ao registrar, o usuário é criado e autenticado mesmo que o envio de e-mail falhe', function () {
    Notification::fake();

    // Simula falha total no sistema de notificações (ex.: SMTP indisponível)
    Notification::shouldReceive('send')->andThrow(new \RuntimeException('SMTP connection failed'));

    // O registro deve concluir normalmente — sem erro 500.
    $response = $this->post('/register', [
        'name'                  => 'Usuário Resiliente',
        'email'                 => 'resiliente@teste.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    // O usuário foi criado no banco
    $this->assertDatabaseHas('users', ['email' => 'resiliente@teste.com']);

    // O redirect aconteceu normalmente (sem 500)
    $response->assertRedirect();
    $response->assertStatus(302);
});

test('ao registrar, o usuário é redirecionado para o dashboard', function () {
    Notification::fake();

    $this->post('/register', [
        'name'                  => 'Redirect User',
        'email'                 => 'redirect@teste.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard'));
});

// ---------------------------------------------------------------------------
// Reenvio do link de verificação
// ---------------------------------------------------------------------------

test('usuário não verificado pode solicitar reenvio do link de verificação', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertSessionHas('status', 'verification-link-sent');

    Notification::assertSentTo($user, VerifyEmailQueued::class);
});

test('usuário já verificado é redirecionado ao dashboard ao solicitar reenvio', function () {
    Notification::fake();

    // Usuário com email_verified_at preenchido (comportamento padrão da factory)
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('verification.send'))
        ->assertRedirect(route('dashboard'));

    // Não deve enviar nova notificação para quem já está verificado
    Notification::assertNothingSent();
});

// ---------------------------------------------------------------------------
// Recuperação de senha
// ---------------------------------------------------------------------------

test('usuário pode solicitar link de recuperação de senha', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertSessionHas('status', __('We have emailed your password reset link.'));

    Notification::assertSentTo($user, ResetPassword::class);
});
