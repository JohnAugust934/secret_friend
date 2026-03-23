<?php

use App\Models\User;

it('forbids ops status in production when allowed emails is empty', function () {
    config()->set('app.env', 'production');
    config()->set('services.ops.status_allowed_emails', '');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('ops.status'))
        ->assertForbidden();
});

it('allows ops status for allowed email', function () {
    config()->set('app.env', 'production');
    config()->set('services.ops.status_allowed_emails', 'admin@example.com');

    $user = User::factory()->create(['email' => 'admin@example.com']);

    $this->actingAs($user)
        ->get(route('ops.status'))
        ->assertOk()
        ->assertSee('Status Operacional');
});
