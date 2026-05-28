<?php

/**
 * Testes de Feature — Fila de E-mail de Verificação
 *
 * Testa especificamente os contratos de enfileiramento da VerifyEmailQueued:
 * - Implementação correta das interfaces (ShouldQueue, etc.)
 * - Configuração da fila correta ('emails')
 * - Parâmetros de resiliência (tries, timeout, backoff)
 * - Comportamento com Queue::fake() para garantir que os jobs chegam à fila
 */

use App\Models\User;
use App\Notifications\VerifyEmailQueued;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

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
// Comportamento de enfileiramento via Queue::fake()
// ---------------------------------------------------------------------------

test('o cadastro de novo usuário coloca a VerifyEmailQueued na fila database', function () {
    Queue::fake();

    $this->post('/register', [
        'name'                  => 'Queue Test User',
        'email'                 => 'queuetest@example.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ]);

    // Com Queue::fake(), qualquer job/notificação enfileirada é interceptado.
    // A asserção abaixo confirma que o job chegou à fila sem ser executado.
    Queue::assertPushed(
        \Illuminate\Notifications\SendQueuedNotifications::class,
        function ($job) {
            return $job->notification instanceof VerifyEmailQueued;
        }
    );
});

test('ao solicitar reenvio de verificação, a notificação vai para a fila emails', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->post(route('verification.send'));

    Notification::assertSentTo(
        $user,
        VerifyEmailQueued::class,
        fn ($notification) => $notification->queue === 'emails'
    );
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
