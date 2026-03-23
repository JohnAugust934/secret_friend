<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

it('shows public invite landing page for guests', function () {
    $owner = User::factory()->create();

    $group = Group::create([
        'name' => 'Convite Publico',
        'event_date' => now()->addWeek(),
        'budget' => 50,
        'owner_id' => $owner->id,
        'invite_token' => 'PUBLIC1',
    ]);

    $response = $this->get(route('groups.join', $group->invite_token));

    $response->assertOk();
    $response->assertSee('Voce recebeu um convite');
    $response->assertSee($group->name);
});

it('redirects to invite flow after login when invite token is provided', function () {
    $owner = User::factory()->create();
    $user = User::factory()->create([
        'email' => 'invite-login@example.com',
        'password' => bcrypt('password123'),
    ]);

    $group = Group::create([
        'name' => 'Grupo Login Convite',
        'event_date' => now()->addWeek(),
        'budget' => 80,
        'owner_id' => $owner->id,
        'invite_token' => 'LOGIN1',
    ]);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password123',
        'invite_token' => $group->invite_token,
    ]);

    $response->assertRedirect(route('groups.join', $group->invite_token));
});

it('redirects to invite flow after registration when invite token is provided', function () {
    $owner = User::factory()->create();

    $group = Group::create([
        'name' => 'Grupo Cadastro Convite',
        'event_date' => now()->addWeek(),
        'budget' => 100,
        'owner_id' => $owner->id,
        'invite_token' => 'REG1',
    ]);

    $response = $this->post(route('register'), [
        'name' => 'Novo Usuario',
        'email' => 'novo-usuario@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'invite_token' => $group->invite_token,
    ]);

    $response->assertRedirect(route('groups.join', $group->invite_token));
});
