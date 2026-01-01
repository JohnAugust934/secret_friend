<?php

use App\Models\User;
use App\Models\Group;
use App\Models\Exclusion;
use App\Models\Pairing;
use App\Services\DrawService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

// Desabilita CSRF para facilitar os testes de POST
beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('admin pode criar e excluir restrições', function () {
    // Cenário: Grupo com Dono e Membro
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::create(['name' => 'G', 'event_date' => now(), 'owner_id' => $owner->id, 'invite_token' => 'X']);

    // Anexar ambos ao grupo
    $group->members()->attach([$owner->id, $member->id]);

    // Ação: Criar Restrição (Dono não tira Membro)
    $response = $this->actingAs($owner)->post(route('groups.exclusions.store', $group), [
        'user_id' => $owner->id,
        'excluded_id' => $member->id
    ]);

    // Verificação
    $response->assertSessionHas('success');
    $this->assertDatabaseHas('exclusions', [
        'group_id' => $group->id,
        'user_id' => $owner->id,
        'excluded_id' => $member->id
    ]);

    // Ação: Remover Restrição
    $exclusion = Exclusion::first();
    $response = $this->actingAs($owner)->delete(route('groups.exclusions.destroy', [$group, $exclusion]));

    // Verificação
    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('exclusions', ['id' => $exclusion->id]);
});

test('o sorteio respeita as restrições (Lógica Matemática)', function () {
    // Cenário: A, B, C.
    // A bloqueia B.
    // Logo, A TEM que tirar C.
    // Se A tirar C, C tem que tirar B (para sobrar B tirar A).

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

    // A afirmação deve ser verdadeira: A tirou C
    expect($pairA->giftee_id)->toBe($uC->id);
});

test('falha se o sorteio for impossível', function () {
    $uA = User::factory()->create();
    $uB = User::factory()->create();
    $group = Group::create(['name' => 'Fail', 'event_date' => now(), 'owner_id' => $uA->id, 'invite_token' => 'Z']);
    $group->members()->attach([$uA->id, $uB->id]);

    // Bloqueio total
    Exclusion::create(['group_id' => $group->id, 'user_id' => $uA->id, 'excluded_id' => $uB->id]);

    $service = new DrawService();

    // Deve lançar exceção
    expect(fn() => $service->draw($group))->toThrow(Exception::class);
});

// --- NOVOS TESTES DE SEGURANÇA ---

test('SEGURANÇA: não permite criar restrição com usuários de fora do grupo', function () {
    $owner = User::factory()->create();
    $memberInGroup = User::factory()->create();
    $outsider = User::factory()->create(); // Usuário que NÃO está no grupo

    $group = Group::create(['name' => 'Sec Test', 'event_date' => now(), 'owner_id' => $owner->id, 'invite_token' => 'S']);
    $group->members()->attach([$owner->id, $memberInGroup->id]);

    // Tentativa 1: Tentar excluir alguém de fora
    $response = $this->actingAs($owner)->post(route('groups.exclusions.store', $group), [
        'user_id' => $owner->id,
        'excluded_id' => $outsider->id // <--- INTRUSO
    ]);

    // Deve falhar com erro de validação nos campos
    $response->assertSessionHasErrors(['excluded_id']);
    $this->assertDatabaseCount('exclusions', 0); // Nada deve ser salvo

    // Tentativa 2: Alguém de fora tentar excluir alguém de dentro
    $response = $this->actingAs($owner)->post(route('groups.exclusions.store', $group), [
        'user_id' => $outsider->id, // <--- INTRUSO TENTANDO DITAR REGRA
        'excluded_id' => $memberInGroup->id
    ]);

    $response->assertSessionHasErrors(['user_id']);
    $this->assertDatabaseCount('exclusions', 0);
});

test('SEGURANÇA: não permite criar restrição contra si mesmo', function () {
    $owner = User::factory()->create();
    $group = Group::create(['name' => 'Self Test', 'event_date' => now(), 'owner_id' => $owner->id, 'invite_token' => 'M']);
    $group->members()->attach($owner->id);

    // Tentar excluir a si mesmo
    $response = $this->actingAs($owner)->post(route('groups.exclusions.store', $group), [
        'user_id' => $owner->id,
        'excluded_id' => $owner->id
    ]);

    $response->assertSessionHasErrors(['excluded_id']);
});
