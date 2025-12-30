<?php

use App\Models\User;
use App\Models\Group;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

// O beforeEach executa antes de cada teste deste ficheiro
beforeEach(function () {
    // Desativa a proteção CSRF apenas para estes testes
    // Isto evita o erro 419 sem comprometer a segurança da app real
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('um usuário autenticado pode criar um grupo com dados válidos', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('groups.store'), [
        'name' => 'Amigo Secreto da Firma',
        'event_date' => now()->addDays(10)->format('Y-m-d'),
        'budget' => 50.00,
        'description' => 'Regras do grupo...',
        'wishlist' => 'Eu quero um teclado',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('groups', [
        'name' => 'Amigo Secreto da Firma',
        'budget' => 50.00,
        'owner_id' => $user->id,
    ]);

    $group = Group::where('name', 'Amigo Secreto da Firma')->first();
    $this->assertDatabaseHas('group_members', [
        'group_id' => $group->id,
        'user_id' => $user->id,
        'wishlist' => 'Eu quero um teclado'
    ]);
});

test('visitantes (não logados) não podem criar grupos', function () {
    // Nota: Aqui NÃO usamos o actingAs, pois queremos simular um visitante
    $response = $this->post(route('groups.store'), [
        'name' => 'Grupo Hacker',
        'event_date' => '2025-12-25',
    ]);

    // Deve ser redirecionado para o login
    $response->assertRedirect(route('login'));
});

test('não é possível criar grupo sem nome', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('groups.store'), [
        'name' => '', // Vazio
        'event_date' => '2025-12-25',
    ]);

    $response->assertSessionHasErrors('name');
});

test('não é possível criar grupo com data no passado', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('groups.store'), [
        'name' => 'Grupo do Passado',
        'event_date' => now()->subDay()->format('Y-m-d'), // Ontem
    ]);

    $response->assertSessionHasErrors('event_date');
});

test('não é possível criar grupo com orçamento negativo', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('groups.store'), [
        'name' => 'Grupo Negativo',
        'event_date' => now()->addDay()->format('Y-m-d'),
        'budget' => -10, // Inválido
    ]);

    $response->assertSessionHasErrors('budget');
});
