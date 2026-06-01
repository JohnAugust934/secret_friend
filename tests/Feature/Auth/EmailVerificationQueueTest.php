<?php

/**
 * Testes de Feature — Notificação de Verificação de E-mail
 *
 * Testa os contratos da VerifyEmailQueued:
 * - Implementação correta das interfaces (ShouldQueue, etc.)
 * - Configuração da fila correta ('emails')
 * - Parâmetros de resiliência (tries, timeout, backoff)
 * - Envio imediato via notifyNow() (sem fila, para hospedagem compartilhada)
 */

use App\Models\User;
use App\Notifications\VerifyEmailQueued;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

// ---------------------------------------------------------------------------
// Contratos estruturais da VerifyEmailQueued
// ---------------------------------------------------------------------------

test('VerifyEmailQueued implementa ShouldQueue', function () {
    $notification = new VerifyEmailQueued;

    expect($notification)->toBeInstanceOf(ShouldQueue::class);
});

test('VerifyEmailQueued é destinada à fila emails', function () {
    $notification = new VerifyEmailQueued;

    expect($notification->queue)->toBe('emails');
});

test('VerifyEmailQueued tem configurações de resiliência corretas', function () {
    $notification = new VerifyEmailQueued;

    // 3 tentativas antes de mover para failed_jobs
    expect($notification->tries)->toBe(3);

    // Timeout menor que retry_after=90s do config/queue.php
    // para evitar processamento duplicado de jobs
    expect($notification->timeout)->toBeLessThan(90);

    // Backoff exponencial configurado
    expect($notification->backoff)->toBeArray()->not->toBeEmpty();
});

// ---------------------------------------------------------------------------
// Comportamento de envio imediato (notifyNow — sem fila)
//
// Usamos notifyNow() porque a Hostinger (hospedagem compartilhada) não
// suporta workers de fila persistentes. Notification::fake() intercepta
// tanto notify() quanto notifyNow(), então os testes funcionam nos dois modos.
// ---------------------------------------------------------------------------

test('o cadastro de novo usuário envia a VerifyEmailQueued imediatamente', function () {
    Notification::fake();

    $this->post('/register', [
        'name'                  => 'Queue Test User',
        'email'                 => 'queuetest@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'queuetest@example.com')->firstOrFail();

    // Notification::fake() captura notifyNow() e notify() igualmente.
    // Confirma que a notificação correta foi enviada ao usuário recém-cadastrado.
    Notification::assertSentTo($user, VerifyEmailQueued::class);
});

test('ao solicitar reenvio de verificação, a notificação é enviada ao usuário', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->post(route('verification.send'));

    Notification::assertSentTo($user, VerifyEmailQueued::class);
});

test('apenas um e-mail de verificação é enviado por registro', function () {
    Notification::fake();

    $this->post('/register', [
        'name'                  => 'Um Email Só',
        'email'                 => 'umso@teste.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'umso@teste.com')->firstOrFail();

    Notification::assertSentToTimes($user, VerifyEmailQueued::class, 1);
});
