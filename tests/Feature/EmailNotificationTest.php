<?php

use App\Models\User;
use App\Models\Group;
use App\Mail\DrawResult;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

// Desabilita CSRF para facilitar o POST
beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('o sistema envia e-mails para os participantes após o sorteio', function () {
    // 1. Fake no sistema de e-mail (Intercepta os envios)
    Mail::fake();

    // 2. Setup: Criar grupo e participantes
    $owner = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $group = Group::create([
        'name' => 'Grupo de Teste Email',
        'event_date' => '2025-12-25',
        'budget' => 100,
        'owner_id' => $owner->id,
        'invite_token' => 'EMAIL123',
    ]);

    // Anexar 3 membros (Mínimo exigido para sortear)
    $group->members()->attach([$owner->id, $user2->id, $user3->id]);

    // 3. Ação: Realizar o sorteio
    $response = $this->actingAs($owner)->post(route('groups.draw', $group));

    // 4. Verificação
    $response->assertSessionHas('success');

    // CORREÇÃO: Como agora usamos Queue, verificamos se foi ENFILEIRADO
    // Devem ser enfileirados 3 e-mails (um para cada participante)
    Mail::assertQueued(DrawResult::class, 3);

    // Verifica se o e-mail foi enfileirado especificamente para o dono
    Mail::assertQueued(DrawResult::class, function ($mail) use ($owner) {
        return $mail->hasTo($owner->email);
    });
});

test('o e-mail contém o nome do amigo secreto sorteado', function () {
    // Para testar o conteúdo, vamos renderizar o Mailable manualmente
    $santa = User::factory()->create(['name' => 'Papai Noel']);
    $giftee = User::factory()->create(['name' => 'Rodolfo Rena']);
    $group = Group::create([
        'name' => 'Natal Mágico',
        'event_date' => '2025-12-25',
        'owner_id' => $santa->id,
        'invite_token' => 'XYZ'
    ]);

    // Criar a instância do e-mail
    $mailable = new DrawResult($group, $santa, $giftee);

    // Renderizar o HTML
    $html = $mailable->render();

    // Verificar se o HTML contém os nomes certos
    expect($html)->toContain('Natal Mágico'); // Nome do grupo
    expect($html)->toContain('Olá, Papai Noel'); // Nome do destinatário
    expect($html)->toContain('Rodolfo Rena'); // Nome do amigo secreto (o mais importante!)
});
