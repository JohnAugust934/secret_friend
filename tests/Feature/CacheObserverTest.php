<?php

use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('o observer limpa o cache automaticamente quando um membro entra', function () {
    $owner = User::factory()->create();
    $group = Group::create([
        'name' => 'Teste Cache',
        'event_date' => now(),
        'owner_id' => $owner->id,
        'invite_token' => 'CACHE'
    ]);

    // 1. Adiciona o dono (dispara observer, limpa cache que nem existia ainda)
    $group->members()->attach($owner->id);

    // 2. Acessa a lista para GERAR O CACHE
    $this->actingAs($owner)->get(route('groups.members.list', $group));

    $cacheKey = "group_members_html_{$group->id}";

    // Garante que o cache foi criado
    expect(Cache::has($cacheKey))->toBeTrue();

    // 3. Adiciona um novo membro (Aqui o OBSERVER deve disparar e apagar o cache)
    $newMember = User::factory()->create(['name' => 'Membro Novo']);
    $group->members()->attach($newMember->id);

    // 4. Verifica se o cache SUMIU (Sinal que o Observer funcionou)
    expect(Cache::has($cacheKey))->toBeFalse();

    // 5. Acessa de novo e garante que o novo nome está lá
    $response = $this->actingAs($owner)->get(route('groups.members.list', $group));
    $response->assertSee('Membro Novo');
});

test('o observer limpa o cache quando um membro é removido', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create(['name' => 'Tchau Membro']);
    $group = Group::create(['name' => 'Teste Remove', 'event_date' => now(), 'owner_id' => $owner->id, 'invite_token' => 'BYE']);

    $group->members()->attach([$owner->id, $member->id]);

    // Gera o cache
    $this->actingAs($owner)->get(route('groups.members.list', $group));
    expect(Cache::has("group_members_html_{$group->id}"))->toBeTrue();

    // Remove o membro (usando a rota do controller que agora usa o Model Pivot)
    $this->actingAs($owner)->delete(route('groups.members.destroy', [$group, $member]));

    // Verifica se o cache foi limpo
    expect(Cache::has("group_members_html_{$group->id}"))->toBeFalse();
});
