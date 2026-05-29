<?php

use App\Models\Exclusion;
use App\Models\Group;
use App\Models\Pairing;
use App\Models\User;
use App\Services\DrawService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

// ──────────────────────────────────────────
// draw_round: primeiro sorteio
// ──────────────────────────────────────────

test('o primeiro sorteio grava os pares com draw_round = 1', function () {
    $owner = User::factory()->create();
    $uA    = User::factory()->create();
    $uB    = User::factory()->create();

    $group = Group::forceCreate([
        'name'         => 'Round Test',
        'event_date'   => now()->addWeek(),
        'owner_id'     => $owner->id,
        'invite_token' => 'RND001',
    ]);
    $group->members()->attach([$owner->id, $uA->id, $uB->id]);
    $group->load(['members', 'exclusions']);

    $service = new DrawService;
    $service->draw($group, 0);

    expect(Pairing::where('group_id', $group->id)->where('draw_round', 1)->count())->toBe(3);
    expect(Pairing::where('group_id', $group->id)->max('draw_round'))->toBe(1);
});

// ──────────────────────────────────────────
// draw_round: re-sorteio
// ──────────────────────────────────────────

test('o re-sorteio grava pares com draw_round = 2 e preserva o round anterior', function () {
    $uA = User::factory()->create(['name' => 'Alice']);
    $uB = User::factory()->create(['name' => 'Bob']);
    $uC = User::factory()->create(['name' => 'Carlos']);

    $group = Group::forceCreate([
        'name'         => 'Re-sort Test',
        'event_date'   => now()->addWeek(),
        'owner_id'     => $uA->id,
        'invite_token' => 'RND002',
    ]);
    $group->members()->attach([$uA->id, $uB->id, $uC->id]);
    $group->load(['members', 'exclusions']);

    $service = new DrawService;

    // Primeiro sorteio (round 1)
    $service->draw($group, 0);
    expect(Pairing::where('group_id', $group->id)->where('draw_round', 1)->count())->toBe(3);

    // Re-sorteio (round 2)
    $group->refresh()->load(['members', 'exclusions']);
    $service->draw($group, 1);

    // Round 1 preservado e round 2 criado
    expect(Pairing::where('group_id', $group->id)->where('draw_round', 1)->count())->toBe(3);
    expect(Pairing::where('group_id', $group->id)->where('draw_round', 2)->count())->toBe(3);
});

// ──────────────────────────────────────────
// Prevenção de repetição
// ──────────────────────────────────────────

test('no re-sorteio nenhum santa tira a mesma pessoa do round anterior', function () {
    $uA = User::factory()->create(['name' => 'Alice']);
    $uB = User::factory()->create(['name' => 'Bob']);
    $uC = User::factory()->create(['name' => 'Carlos']);
    $uD = User::factory()->create(['name' => 'Diana']);

    $group = Group::forceCreate([
        'name'         => 'Repetition Test',
        'event_date'   => now()->addWeek(),
        'owner_id'     => $uA->id,
        'invite_token' => 'RND003',
    ]);
    $group->members()->attach([$uA->id, $uB->id, $uC->id, $uD->id]);
    $group->load(['members', 'exclusions']);

    $service = new DrawService;

    // Executa os dois sorteios
    $service->draw($group, 0);
    $group->refresh()->load(['members', 'exclusions']);
    $service->draw($group, 1);

    // Para cada par do round 2, verifica que não é o mesmo que o round 1
    $round1Pairs = Pairing::where('group_id', $group->id)->where('draw_round', 1)->get();
    $round2Pairs = Pairing::where('group_id', $group->id)->where('draw_round', 2)->get();

    foreach ($round2Pairs as $pair2) {
        $previousGiftee = $round1Pairs->firstWhere('santa_id', $pair2->santa_id)?->giftee_id;

        expect($pair2->giftee_id)->not->toBe($previousGiftee,
            "Santa {$pair2->santa_id} tirou a mesma pessoa ({$pair2->giftee_id}) nos rounds 1 e 2."
        );
    }
});

test('re-sorteio via HTTP pelo owner incrementa o round e retorna sucesso', function () {
    $owner = User::factory()->create();
    $uA    = User::factory()->create();
    $uB    = User::factory()->create();

    $group = Group::forceCreate([
        'name'         => 'HTTP Redraw Test',
        'event_date'   => now()->addWeek(),
        'owner_id'     => $owner->id,
        'invite_token' => 'RND004',
        'is_drawn'     => true,
    ]);
    $group->members()->attach([$owner->id, $uA->id, $uB->id]);

    // Simula round 1 já existente no banco
    Pairing::insert([
        ['group_id' => $group->id, 'santa_id' => $owner->id, 'giftee_id' => $uA->id, 'draw_round' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['group_id' => $group->id, 'santa_id' => $uA->id,   'giftee_id' => $uB->id, 'draw_round' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['group_id' => $group->id, 'santa_id' => $uB->id,   'giftee_id' => $owner->id, 'draw_round' => 1, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $response = $this->actingAs($owner)
        ->post(route('groups.draw', $group));

    $response->assertRedirect();

    // Deve existir round 2
    expect(Pairing::where('group_id', $group->id)->max('draw_round'))->toBe(2);
});

// ──────────────────────────────────────────
// Impossibilidade matemática no re-sorteio
// ──────────────────────────────────────────

test('re-sorteio lança exceção quando histórico + exclusões tornam sorteio impossível', function () {
    // Cenário: 3 pessoas onde A só pode tirar B (bloqueada de C por exclusão)
    // e B tirou A no round anterior → a única opção restante para B seria C.
    // A história de A→B + exclusão A→C significa A ficaria sem candidatos no re-sorteio
    // se C→A e B→C, mas com 3 pessoas e A bloqueando ambos, deve ser impossível.
    // Vamos usar um cenário mais simples: exclusões + histórico = todos bloqueados para A.
    $uA = User::factory()->create(['name' => 'A']);
    $uB = User::factory()->create(['name' => 'B']);
    $uC = User::factory()->create(['name' => 'C']);

    $group = Group::forceCreate([
        'name'         => 'Impossible Redraw',
        'event_date'   => now()->addWeek(),
        'owner_id'     => $uA->id,
        'invite_token' => 'RNDX01',
        'is_drawn'     => true,
    ]);
    $group->members()->attach([$uA->id, $uB->id, $uC->id]);

    // Round 1: A→B, B→C, C→A (histórico)
    Pairing::insert([
        ['group_id' => $group->id, 'santa_id' => $uA->id, 'giftee_id' => $uB->id, 'draw_round' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['group_id' => $group->id, 'santa_id' => $uB->id, 'giftee_id' => $uC->id, 'draw_round' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['group_id' => $group->id, 'santa_id' => $uC->id, 'giftee_id' => $uA->id, 'draw_round' => 1, 'created_at' => now(), 'updated_at' => now()],
    ]);

    // Exclusão permanente: A não pode tirar C
    Exclusion::create(['group_id' => $group->id, 'user_id' => $uA->id, 'excluded_id' => $uC->id]);

    // No re-sorteio: A não pode tirar B (histórico) nem C (exclusão) → impossível
    $group->load(['members', 'exclusions']);

    $service = new DrawService;

    expect(fn () => $service->draw($group, 1))
        ->toThrow(Exception::class, 'Matematicamente impossível');
});
