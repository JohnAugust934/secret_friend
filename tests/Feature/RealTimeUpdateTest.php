<?php

use App\Models\User;
use App\Models\Group;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('a rota parcial de membros retorna a lista correta de usuários', function () {
    $owner = User::factory()->create();
    $user2 = User::factory()->create(['name' => 'Novo Membro']);

    $group = Group::create([
        'name' => 'Grupo Auto Update',
        'event_date' => '2025-12-25',
        'owner_id' => $owner->id,
        'invite_token' => 'UPDATE',
    ]);

    $group->members()->attach([$owner->id, $user2->id]);

    // O dono acessa a rota parcial
    $response = $this->actingAs($owner)->get(route('groups.members.list', $group));

    $response->assertStatus(200);

    // Verifica se NÃO retornou o layout completo (não deve ter <html> ou <body>)
    $response->assertDontSee('<html');

    // Verifica se retornou os nomes dos membros
    // CORREÇÃO: Como é o próprio usuário logado, ele vê "Você" em vez do nome dele
    $response->assertSee('Você');

    // O outro membro continua aparecendo com o nome normal
    $response->assertSee('Novo Membro');
});

test('usuários fora do grupo não podem acessar a lista parcial', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $group = Group::create([
        'name' => 'Grupo Privado',
        'event_date' => '2025-12-25',
        'owner_id' => $owner->id,
        'invite_token' => 'PRIV',
    ]);
    $group->members()->attach($owner->id);

    $response = $this->actingAs($outsider)->get(route('groups.members.list', $group));

    $response->assertStatus(403);
});
