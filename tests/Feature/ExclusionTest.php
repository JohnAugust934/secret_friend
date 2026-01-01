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

test('admin pode criar e excluir restrições', function () {
    $owner = User::factory()->create();
    $user2 = User::factory()->create();
    $group = Group::create(['name' => 'G', 'event_date' => now(), 'owner_id' => $owner->id, 'invite_token' => 'X']);
    $group->members()->attach([$owner->id, $user2->id]);

    // Criar
    $response = $this->actingAs($owner)->post(route('groups.exclusions.store', $group), [
        'user_id' => $owner->id,
        'excluded_id' => $user2->id
    ]);
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('exclusions', ['user_id' => $owner->id, 'excluded_id' => $user2->id]);

    // Deletar
    $exclusion = Exclusion::first();
    $response = $this->actingAs($owner)->delete(route('groups.exclusions.destroy', [$group, $exclusion]));
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('exclusions', ['id' => $exclusion->id]);
});

test('o sorteio respeita as restrições (Lógica Matemática)', function () {
    // Cenário: A, B, C.
    // A bloqueia B.
    // Logo, A TEM que tirar C.
    // Se A tirar C, C tem que tirar B (para sobrar B tirar A, ou vice versa).

    $uA = User::factory()->create(['name' => 'A']);
    $uB = User::factory()->create(['name' => 'B']);
    $uC = User::factory()->create(['name' => 'C']);

    $group = Group::create(['name' => 'Logic Test', 'event_date' => now(), 'owner_id' => $uA->id, 'invite_token' => 'Y']);
    $group->members()->attach([$uA->id, $uB->id, $uC->id]);

    // Restrição: A não pode tirar B
    Exclusion::create(['group_id' => $group->id, 'user_id' => $uA->id, 'excluded_id' => $uB->id]);

    // Executa o sorteio usando o Service
    $service = new DrawService();
    $service->draw($group);

    // Verifica no banco quem o A tirou
    $pairA = Pairing::where('group_id', $group->id)->where('santa_id', $uA->id)->first();

    // A afirmação deve ser verdadeira: A tirou C (pois B estava bloqueado e não pode tirar a si mesmo)
    expect($pairA->giftee_id)->toBe($uC->id);
});

test('falha se o sorteio for impossível', function () {
    // Cenário: A e B. A bloqueia B. Impossível sortear (precisa min 3 ou sem bloqueio total).
    $uA = User::factory()->create();
    $uB = User::factory()->create();
    $group = Group::create(['name' => 'Fail', 'event_date' => now(), 'owner_id' => $uA->id, 'invite_token' => 'Z']);
    $group->members()->attach([$uA->id, $uB->id]);

    Exclusion::create(['group_id' => $group->id, 'user_id' => $uA->id, 'excluded_id' => $uB->id]);

    $service = new DrawService();

    // Deve lançar exceção
    expect(fn() => $service->draw($group))->toThrow(Exception::class);
});
