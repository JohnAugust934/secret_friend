<?php

use App\Mail\DrawResult;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Mail;

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

    $group = Group::forceCreate([
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
    $group = Group::forceCreate([
        'name' => 'Natal Mágico',
        'event_date' => '2025-12-25',
        'owner_id' => $santa->id,
        'invite_token' => 'XYZ',
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

test('o e-mail contém a wishlist do presenteado quando ela está preenchida', function () {
    $santa = User::factory()->create(['name' => 'Papai Noel']);
    $giftee = User::factory()->create(['name' => 'Rodolfo Rena']);
    $group = Group::forceCreate([
        'name' => 'Natal Mágico',
        'event_date' => '2025-12-25',
        'owner_id' => $santa->id,
        'invite_token' => 'WISH1',
    ]);

    $wishlist = 'Quero um livro de PHP e um par de meias';
    $mailable = new DrawResult($group, $santa, $giftee, $wishlist);

    $html = $mailable->render();

    expect($html)->toContain('Quero um livro de PHP e um par de meias');
});

test('o e-mail não exibe seção de wishlist quando ela está vazia', function () {
    $santa = User::factory()->create(['name' => 'Papai Noel']);
    $giftee = User::factory()->create(['name' => 'Rodolfo Rena']);
    $group = Group::forceCreate([
        'name' => 'Natal Mágico',
        'event_date' => '2025-12-25',
        'owner_id' => $santa->id,
        'invite_token' => 'WISH2',
    ]);

    $mailable = new DrawResult($group, $santa, $giftee, null);

    $html = $mailable->render();

    expect($html)->not->toContain('Lista de Desejos');
});

test('o sorteio enfileira e-mail com a wishlist correta do presenteado', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $group = Group::forceCreate([
        'name' => 'Grupo Wishlist',
        'event_date' => '2025-12-25',
        'budget' => 50,
        'owner_id' => $owner->id,
        'invite_token' => 'WLTEST',
    ]);

    $wishlistDoUser2 = 'Livro de Laravel e café especial';
    $group->members()->attach([
        $owner->id => ['wishlist' => null],
        $user2->id => ['wishlist' => $wishlistDoUser2],
        $user3->id => ['wishlist' => null],
    ]);

    $this->actingAs($owner)->post(route('groups.draw', $group));

    // Verifica que ao menos um mail foi enfileirado com a wishlist correta do user2
    Mail::assertQueued(DrawResult::class, function (DrawResult $mail) use ($user2, $wishlistDoUser2) {
        // O santa que tirou $user2 deve receber o e-mail com a wishlist dele
        return $mail->gifteeName === $user2->name && $mail->wishlist === $wishlistDoUser2;
    });
});
