<?php

use App\Models\Group;
use App\Models\Pairing;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ──────────────────────────────────────────
// join() — GET do link de convite
// ──────────────────────────────────────────

test('o link de convite mostra a landing page quando o grupo não foi sorteado', function () {
    $owner = User::factory()->create();
    $group = Group::forceCreate([
        'name'         => 'Grupo Aberto',
        'event_date'   => now()->addWeek(),
        'owner_id'     => $owner->id,
        'invite_token' => 'OPEN01',
        'is_drawn'     => false,
    ]);

    $response = $this->get(route('groups.join', $group->invite_token));

    $response->assertOk();
    $response->assertSee('Voce recebeu um convite');
});

test('o link de convite redireciona para a view draw-closed quando o grupo já foi sorteado (visitante)', function () {
    $owner = User::factory()->create();
    $group = Group::forceCreate([
        'name'         => 'Grupo Fechado',
        'event_date'   => now()->addWeek(),
        'owner_id'     => $owner->id,
        'invite_token' => 'CLOSE1',
        'is_drawn'     => true,
    ]);

    $response = $this->get(route('groups.join', $group->invite_token));

    $response->assertOk();
    // A view draw-closed deve ser renderizada
    $response->assertSee('Sorteio já Realizado');
    $response->assertSee($group->name);
});

test('o link de convite mostra draw-closed para utilizador autenticado se o grupo já foi sorteado', function () {
    $owner = User::factory()->create();
    $user  = User::factory()->create();
    $group = Group::forceCreate([
        'name'         => 'Grupo Sorteado',
        'event_date'   => now()->addWeek(),
        'owner_id'     => $owner->id,
        'invite_token' => 'CLOSE2',
        'is_drawn'     => true,
    ]);

    $response = $this->actingAs($user)
        ->get(route('groups.join', $group->invite_token));

    $response->assertOk();
    $response->assertSee('Sorteio já Realizado');
});

// ──────────────────────────────────────────
// joinStore() — POST de confirmação de entrada
// ──────────────────────────────────────────

test('joinStore bloqueia entrada num grupo já sorteado', function () {
    $owner = User::factory()->create();
    $user  = User::factory()->create();
    $group = Group::forceCreate([
        'name'         => 'Grupo Bloqueado',
        'event_date'   => now()->addWeek(),
        'owner_id'     => $owner->id,
        'invite_token' => 'CLOSE3',
        'is_drawn'     => true,
    ]);

    $response = $this->actingAs($user)
        ->post(route('groups.join.store', $group->invite_token), ['wishlist' => 'teste']);

    $response->assertRedirect();

    // Não deve ter entrado no grupo
    $this->assertDatabaseMissing('group_members', [
        'group_id' => $group->id,
        'user_id'  => $user->id,
    ]);

    // Deve ter mensagem de erro na sessão
    $response->assertSessionHas('error');
});

test('joinStore aceita entrada num grupo não sorteado', function () {
    $owner = User::factory()->create();
    $user  = User::factory()->create();
    $group = Group::forceCreate([
        'name'         => 'Grupo Aberto Store',
        'event_date'   => now()->addWeek(),
        'owner_id'     => $owner->id,
        'invite_token' => 'OPEN02',
        'is_drawn'     => false,
    ]);

    $response = $this->actingAs($user)
        ->post(route('groups.join.store', $group->invite_token), ['wishlist' => 'Livros']);

    $response->assertRedirect(route('groups.show', $group));

    $this->assertDatabaseHas('group_members', [
        'group_id' => $group->id,
        'user_id'  => $user->id,
    ]);
});
