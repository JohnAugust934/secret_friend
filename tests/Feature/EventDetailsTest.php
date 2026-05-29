<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ──────────────────────────────────────────
// Criação com novos campos
// ──────────────────────────────────────────

test('um utilizador pode criar um grupo com location e budget_limit', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('groups.store'), [
        'name'         => 'Natal da Família',
        'event_date'   => now()->addDays(30)->format('Y-m-d'),
        'location'     => 'Casa da Avó',
        'budget_limit' => '10€ - 20€',
        'description'  => 'Regras gerais',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('groups', [
        'name'         => 'Natal da Família',
        'location'     => 'Casa da Avó',
        'budget_limit' => '10€ - 20€',
        'owner_id'     => $user->id,
    ]);
});

test('location e budget_limit são opcionais na criação', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('groups.store'), [
        'name'       => 'Grupo Simples',
        'event_date' => now()->addDays(10)->format('Y-m-d'),
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('groups', [
        'name'         => 'Grupo Simples',
        'location'     => null,
        'budget_limit' => null,
    ]);
});

test('location não pode exceder 255 caracteres', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('groups.store'), [
        'name'       => 'Grupo X',
        'event_date' => now()->addDays(10)->format('Y-m-d'),
        'location'   => str_repeat('A', 256),
    ]);

    $response->assertSessionHasErrors('location');
});

test('budget_limit não pode exceder 100 caracteres', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('groups.store'), [
        'name'         => 'Grupo X',
        'event_date'   => now()->addDays(10)->format('Y-m-d'),
        'budget_limit' => str_repeat('A', 101),
    ]);

    $response->assertSessionHasErrors('budget_limit');
});

// ──────────────────────────────────────────
// Edição com novos campos
// ──────────────────────────────────────────

test('o owner pode editar location e budget_limit', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);

    $response = $this->actingAs($owner)->put(route('groups.update', $group), [
        'name'         => $group->name,
        'event_date'   => now()->addDays(30)->format('Y-m-d'),
        'location'     => 'Restaurante Novo',
        'budget_limit' => '30€ - 50€',
    ]);

    $response->assertRedirect(route('groups.show', $group));
    $this->assertDatabaseHas('groups', [
        'id'           => $group->id,
        'location'     => 'Restaurante Novo',
        'budget_limit' => '30€ - 50€',
    ]);
});

// ──────────────────────────────────────────
// Visualização na show page
// ──────────────────────────────────────────

test('a página do grupo exibe location e budget_limit quando preenchidos', function () {
    $owner = User::factory()->create();
    $group = Group::forceCreate([
        'name'         => 'Grupo Completo',
        'event_date'   => now()->addDays(10),
        'location'     => 'Casa do João',
        'budget_limit' => '15€ - 25€',
        'owner_id'     => $owner->id,
        'invite_token' => 'SHOW01',
    ]);
    $group->members()->attach($owner->id);

    $response = $this->actingAs($owner)->get(route('groups.show', $group));

    $response->assertOk();
    $response->assertSee('Casa do João');
    $response->assertSee('15€ - 25€');
});

test('a página do grupo exibe budget numérico quando budget_limit está vazio', function () {
    $owner = User::factory()->create();
    $group = Group::forceCreate([
        'name'         => 'Grupo Budget',
        'event_date'   => now()->addDays(10),
        'budget'       => 50.00,
        'budget_limit' => null,
        'owner_id'     => $owner->id,
        'invite_token' => 'SHOW02',
    ]);
    $group->members()->attach($owner->id);

    $response = $this->actingAs($owner)->get(route('groups.show', $group));

    $response->assertOk();
    $response->assertSee('50,00');
});
