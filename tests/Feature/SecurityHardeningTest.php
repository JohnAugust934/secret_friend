<?php

use App\Models\Group;
use App\Models\User;

it('blocks non-member from viewing a group by id', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $outsider = User::factory()->create();

    $group = Group::create([
        'name' => 'Private Group',
        'event_date' => now()->addWeek(),
        'owner_id' => $owner->id,
        'invite_token' => 'SEC123',
    ]);

    $group->members()->attach([$owner->id, $member->id]);

    $this->actingAs($outsider)
        ->get(route('groups.show', $group))
        ->assertForbidden();
});

it('blocks non-member from updating wishlist', function () {
    $owner = User::factory()->create();
    $outsider = User::factory()->create();

    $group = Group::create([
        'name' => 'Wishlist Locked',
        'event_date' => now()->addWeek(),
        'owner_id' => $owner->id,
        'invite_token' => 'SEC124',
    ]);

    $group->members()->attach([$owner->id]);

    $this->actingAs($outsider)
        ->put(route('groups.wishlist.update', $group), ['wishlist' => 'Any'])
        ->assertForbidden();
});

it('exposes health endpoint', function () {
    $response = $this->get(route('healthz'));

    expect([200, 503])->toContain($response->status());
    $response->assertJsonStructure([
        'status',
        'checks' => ['database', 'cache', 'queue'],
        'timestamp',
    ]);
});
