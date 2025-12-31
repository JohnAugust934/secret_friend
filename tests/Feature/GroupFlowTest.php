<?php

use App\Models\User;
use App\Models\Group;
use App\Models\Pairing;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

// Desabilita CSRF para os testes
beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('um usuário pode entrar num grupo usando o link de convite', function () {
    $user = User::factory()->create();
    $owner = User::factory()->create();
    $group = Group::create([
        'name' => 'Grupo Teste',
        'event_date' => '2025-12-25',
        'owner_id' => $owner->id,
        'invite_token' => 'CONVITE123',
    ]);

    // Acessa a página de convite
    $response = $this->actingAs($user)->get(route('groups.join', 'CONVITE123'));
    $response->assertStatus(200);
    $response->assertSee($group->name);

    // Confirma a entrada
    $response = $this->actingAs($user)->post(route('groups.join.store', 'CONVITE123'), [
        'wishlist' => 'Livros'
    ]);

    $response->assertRedirect(route('groups.show', $group));

    // Verifica se está no banco
    $this->assertDatabaseHas('group_members', [
        'group_id' => $group->id,
        'user_id' => $user->id,
        'wishlist' => 'Livros'
    ]);
});

test('apenas o dono pode realizar o sorteio e gera pares corretamente', function () {
    $owner = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $group = Group::create([
        'name' => 'Sorteio',
        'event_date' => '2025-12-25',
        'owner_id' => $owner->id,
        'invite_token' => 'ABC',
    ]);

    $group->members()->attach([$owner->id, $user2->id, $user3->id]);

    // Membro comum tenta sortear (deve falhar)
    $response = $this->actingAs($user2)->post(route('groups.draw', $group));
    $response->assertStatus(403);

    // Dono sorteia (deve passar)
    $response = $this->actingAs($owner)->post(route('groups.draw', $group));
    $response->assertSessionHas('success');

    // CORREÇÃO AQUI: Mudámos de 'pairings' para 'matches'
    $this->assertDatabaseCount('matches', 3);

    $group->refresh();
    expect($group->is_drawn)->toBeTrue();
});

test('o usuário consegue ver quem lhe calhou no sorteio', function () {
    $santa = User::factory()->create();
    $giftee = User::factory()->create();
    $group = Group::create([
        'name' => 'Revelação',
        'event_date' => '2025-12-25',
        'owner_id' => $santa->id,
        'invite_token' => 'ABC',
        'is_drawn' => true,
    ]);

    $group->members()->attach([$santa->id, $giftee->id]);

    // Cria o par manualmente para testar a visualização
    Pairing::create([
        'group_id' => $group->id,
        'santa_id' => $santa->id,
        'giftee_id' => $giftee->id,
    ]);

    $response = $this->actingAs($santa)->get(route('groups.show', $group));

    $response->assertOk();
    $response->assertSee($giftee->name); // Deve ver o nome do amigo

    // Verifica o texto atualizado da interface
    $response->assertSee('A sua missão secreta');
});

test('o dono pode excluir o grupo e limpar os dados', function () {
    $owner = User::factory()->create();
    $group = Group::create([
        'name' => 'Para Deletar',
        'event_date' => '2025-12-25',
        'owner_id' => $owner->id,
        'invite_token' => 'DEL',
    ]);

    $response = $this->actingAs($owner)->delete(route('groups.destroy', $group));

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseMissing('groups', ['id' => $group->id]);
});

test('um membro comum NÃO pode excluir o grupo', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::create([
        'name' => 'Protegido',
        'event_date' => '2025-12-25',
        'owner_id' => $owner->id,
        'invite_token' => 'PROT',
    ]);

    $response = $this->actingAs($member)->delete(route('groups.destroy', $group));

    $response->assertStatus(403);
    $this->assertDatabaseHas('groups', ['id' => $group->id]);
});
