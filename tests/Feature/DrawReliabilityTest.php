<?php

use App\Models\User;
use App\Models\Group;
use App\Models\Exclusion;
use App\Models\Pairing;
use App\Services\DrawService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('algoritmo resolve um cenário de exclusão circular complexa', function () {
    // Cenário "Cadeia Fechada":
    // A não pode tirar B
    // B não pode tirar C
    // C não pode tirar A
    // Solução única esperada: A->C, B->A, C->B

    $uA = User::factory()->create(['name' => 'A']);
    $uB = User::factory()->create(['name' => 'B']);
    $uC = User::factory()->create(['name' => 'C']);

    $group = Group::create(['name' => 'Hard Logic', 'event_date' => now(), 'owner_id' => $uA->id, 'invite_token' => 'HARD']);
    $group->members()->attach([$uA->id, $uB->id, $uC->id]);

    // Criar as restrições circulares
    Exclusion::create(['group_id' => $group->id, 'user_id' => $uA->id, 'excluded_id' => $uB->id]);
    Exclusion::create(['group_id' => $group->id, 'user_id' => $uB->id, 'excluded_id' => $uC->id]);
    Exclusion::create(['group_id' => $group->id, 'user_id' => $uC->id, 'excluded_id' => $uA->id]);

    $service = new DrawService();
    $result = $service->draw($group);

    expect($result)->toBeTrue();

    // Validar se a lógica seguiu o único caminho possível
    $pairA = Pairing::where('group_id', $group->id)->where('santa_id', $uA->id)->first();
    $pairB = Pairing::where('group_id', $group->id)->where('santa_id', $uB->id)->first();
    $pairC = Pairing::where('group_id', $group->id)->where('santa_id', $uC->id)->first();

    expect($pairA->giftee_id)->toBe($uC->id);
    expect($pairB->giftee_id)->toBe($uA->id);
    expect($pairC->giftee_id)->toBe($uB->id);
});

test('algoritmo identifica corretamente cenário impossível', function () {
    // Cenário Impossível: 3 Pessoas (A, B, C)
    // A bloqueia B e C. 
    // A não tem quem tirar.

    $uA = User::factory()->create();
    $uB = User::factory()->create();
    $uC = User::factory()->create();

    $group = Group::create(['name' => 'Impossible', 'event_date' => now(), 'owner_id' => $uA->id, 'invite_token' => 'IMP']);
    $group->members()->attach([$uA->id, $uB->id, $uC->id]);

    Exclusion::create(['group_id' => $group->id, 'user_id' => $uA->id, 'excluded_id' => $uB->id]);
    Exclusion::create(['group_id' => $group->id, 'user_id' => $uA->id, 'excluded_id' => $uC->id]);

    $service = new DrawService();

    // Deve lançar exceção
    expect(fn() => $service->draw($group))->toThrow(Exception::class, 'Matematicamente impossível');
});

test('stress test: algoritmo funciona para grupo médio (20 pessoas)', function () {
    $owner = User::factory()->create();
    $group = Group::create(['name' => 'Stress Test', 'event_date' => now(), 'owner_id' => $owner->id, 'invite_token' => 'STR']);

    // Criar 20 membros
    $members = User::factory(20)->create();
    $group->members()->attach($members->pluck('id'));

    // Adicionar algumas exclusões aleatórias (ex: cada um bloqueia o próximo)
    foreach ($members as $index => $member) {
        if (isset($members[$index + 1])) {
            Exclusion::create([
                'group_id' => $group->id,
                'user_id' => $member->id,
                'excluded_id' => $members[$index + 1]->id
            ]);
        }
    }

    $service = new DrawService();
    $startTime = microtime(true);

    $service->draw($group);

    $endTime = microtime(true);
    $duration = $endTime - $startTime;

    // Deve ser rápido (menos de 1 segundo para 20 pessoas)
    expect($duration)->toBeLessThan(1.0);
    expect(Pairing::where('group_id', $group->id)->count())->toBe(20);
});
