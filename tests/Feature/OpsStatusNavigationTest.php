<?php

use App\Models\User;

it('shows status navigation link only for allowed emails in production', function () {
    config()->set('app.env', 'production');
    config()->set('services.ops.status_allowed_emails', 'admin@example.com');

    $allowedUser = User::factory()->create(['email' => 'admin@example.com']);
    $forbiddenUser = User::factory()->create(['email' => 'membro@example.com']);

    $this->actingAs($allowedUser)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(route('ops.status'), false);

    auth()->logout();

    $this->actingAs($forbiddenUser)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee(route('ops.status'), false);
});
