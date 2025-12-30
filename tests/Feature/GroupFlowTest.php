<?php

use App\Models\User;
use App\Models\Group;
use App\Models\Pairing;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

// Desabilita a proteção CSRF para estes testes de formulário
beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('um usuário pode entrar num grupo usando o link de convite', function () {
    // 1. Setup: Criar um dono e um grupo existente
    $owner = User::factory()->create();
    $group = Group::create([
        'name' => 'Festa da Firma',
        'event_date' => '2025-12-25',
        'budget' => 100,
        'description' => 'Teste de convite',
        'owner_id' => $owner->id,
        'invite_token' => 'CONVITE123',
    ]);
    // O dono já faz parte do grupo
    $group->members()->attach($owner->id);

    // 2. Cenário: Um novo utilizador recebe o link
    $newUser = User::factory()->create();

    // 3. Ação: Tenta entrar no grupo via POST (simulando o formulário de convite)
    $response = $this->actingAs($newUser)
        ->post(route('groups.join.store', 'CONVITE123'), [
            'wishlist' => 'Quero meias coloridas'
        ]);

    // 4. Verificação
    $response->assertRedirect(route('groups.show', $group));
    $response->assertSessionHas('success');

    // Verifica se foi gravado na base de dados
    $this->assertDatabaseHas('group_members', [
        'group_id' => $group->id,
        'user_id' => $newUser->id,
        'wishlist' => 'Quero meias coloridas'
    ]);
});

test('apenas o dono pode realizar o sorteio e gera pares corretamente', function () {
    // 1. Setup: Grupo com 3 pessoas (Mínimo para ser interessante)
    $owner = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $group = Group::create([
        'name' => 'Grupo para Sortear',
        'event_date' => '2025-12-25',
        'owner_id' => $owner->id,
        'invite_token' => 'ABC',
    ]);

    $group->members()->attach([$owner->id, $user2->id, $user3->id]);

    // 2. Teste de Segurança: Membro comum tenta sortear
    $responseFail = $this->actingAs($user2)
        ->post(route('groups.draw', $group));

    // Deve ser proibido (403 Forbidden)
    $responseFail->assertStatus(403);

    // 3. Ação: O Dono realiza o sorteio
    $responseSuccess = $this->actingAs($owner)
        ->post(route('groups.draw', $group));

    // 4. Verificação
    $responseSuccess->assertSessionHas('success');

    // O status do grupo deve ter mudado para sorteado
    $this->assertDatabaseHas('groups', ['id' => $group->id, 'is_drawn' => true]);

    // Devem ter sido criados exatamente 3 pares na tabela pairings
    $this->assertEquals(3, Pairing::where('group_id', $group->id)->count());
});

test('o usuário consegue ver quem lhe calhou no sorteio', function () {
    // 1. Setup: Criar cenário onde o sorteio JÁ aconteceu
    $santa = User::factory()->create();
    $giftee = User::factory()->create();

    $group = Group::create([
        'name' => 'Revelação',
        'event_date' => '2025-12-25',
        'owner_id' => $santa->id,
        'invite_token' => 'XYZ',
        'is_drawn' => true
    ]);

    $group->members()->attach([$santa->id, $giftee->id]);

    // Criamos o par manualmente no banco para garantir o teste
    Pairing::create([
        'group_id' => $group->id,
        'santa_id' => $santa->id,
        'giftee_id' => $giftee->id
    ]);

    // 2. Ação: O "Santa" visita a página do grupo
    $response = $this->actingAs($santa)->get(route('groups.show', $group));

    // 3. Verificação: O HTML deve conter o nome da pessoa que ele tirou
    $response->assertOk();
    $response->assertSee($giftee->name); // Deve ver o nome do amigo
    $response->assertSee('Sua missão secreta'); // Texto da nossa UI
});

test('o dono pode excluir o grupo e limpar os dados', function () {
    // 1. Setup
    $owner = User::factory()->create();
    $group = Group::create([
        'name' => 'Grupo Errado',
        'event_date' => '2025-12-25',
        'owner_id' => $owner->id,
        'invite_token' => 'DEL',
    ]);

    // 2. Ação: Dono clica em excluir
    $response = $this->actingAs($owner)->delete(route('groups.destroy', $group));

    // 3. Verificação
    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('success');

    // O grupo não deve mais existir no banco
    $this->assertDatabaseMissing('groups', ['id' => $group->id]);
});

test('um membro comum NÃO pode excluir o grupo', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $group = Group::create([
        'name' => 'Não Toque Aqui',
        'event_date' => '2025-12-25',
        'owner_id' => $owner->id, // O dono é outro
        'invite_token' => 'SECURE',
    ]);
    $group->members()->attach($member->id);

    // Membro tenta apagar
    $response = $this->actingAs($member)->delete(route('groups.destroy', $group));

    // Deve ser bloqueado
    $response->assertStatus(403);

    // O grupo ainda deve existir
    $this->assertDatabaseHas('groups', ['id' => $group->id]);
});
