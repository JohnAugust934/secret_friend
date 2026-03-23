<?php

use App\Models\User;

it('accepts frontend telemetry payload', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('telemetry.frontend'), [
            'type' => 'js_error',
            'message' => 'sample error',
            'url' => 'http://localhost/test',
        ])
        ->assertStatus(202)
        ->assertJson(['ok' => true]);
});
