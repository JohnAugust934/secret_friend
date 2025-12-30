<?php

use App\Models\User;
use App\Models\Group;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

// Desabilita CSRF para os testes
beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('o dono do grupo pode ver a tela de edição', function () {
    $owner = User::factory()->create();
    // Cria grupo com data futura válida (10 dias a partir de hoje)
    $group = Group::create([
        'name' => 'Original',
        'event_date' => now()->addDays(10),
        'owner_id' => $owner->id,
        'invite_token' => 'ABC'
    ]);

    $response = $this->actingAs($owner)->get(route('groups.edit', $group));

    $response->assertStatus(200);
    $response->assertSee('Editar Grupo');
});

test('o dono do grupo pode atualizar os dados', function () {
    $owner = User::factory()->create();
    $group = Group::create([
        'name' => 'Original',
        'event_date' => now()->addDays(5),
        'owner_id' => $owner->id,
        'invite_token' => 'ABC'
    ]);

    // Define uma nova data válida (20 dias a partir de hoje)
    $newDate = now()->addDays(20)->format('Y-m-d');

    $response = $this->actingAs($owner)->put(route('groups.update', $group), [
        'name' => 'Nome Alterado',
        'event_date' => $newDate,
        'budget' => 500,
        'description' => 'Nova descrição'
    ]);

    $response->assertRedirect(route('groups.show', $group));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('groups', [
        'id' => $group->id,
        'name' => 'Nome Alterado',
        'budget' => 500
    ]);
});

test('membros comuns NÃO podem acessar a tela de edição', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::create([
        'name' => 'Original',
        'event_date' => now()->addDays(10),
        'owner_id' => $owner->id,
        'invite_token' => 'ABC'
    ]);

    $response = $this->actingAs($member)->get(route('groups.edit', $group));

    $response->assertStatus(403); // Proibido
});

test('membros comuns NÃO podem atualizar o grupo', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::create([
        'name' => 'Original',
        'event_date' => now()->addDays(10),
        'owner_id' => $owner->id,
        'invite_token' => 'ABC'
    ]);

    // Usamos uma data válida para garantir que passa na validação de formato
    // e bate de frente com a validação de permissão (403) no Controller
    $response = $this->actingAs($member)->put(route('groups.update', $group), [
        'name' => 'Hacked',
        'event_date' => now()->addDays(20)->format('Y-m-d')
    ]);

    $response->assertStatus(403);

    // O nome não deve ter mudado
    $this->assertDatabaseHas('groups', ['name' => 'Original']);
});
