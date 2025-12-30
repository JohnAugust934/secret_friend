<?php

use App\Models\User;
use App\Models\Group;

test('o dono do grupo pode remover um participante', function () {
    // 1. Setup
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $group = Group::create([
        'name' => 'Festa',
        'event_date' => '2025-12-25',
        'owner_id' => $owner->id,
        'invite_token' => 'ABC'
    ]);

    // Adiciona dono e membro
    $group->members()->attach([$owner->id, $member->id]);

    // 2. Ação: Dono remove membro
    $response = $this->actingAs($owner)
        ->delete(route('groups.members.destroy', [$group, $member]));

    // 3. Verificação
    $response->assertSessionHas('success');

    // O membro não deve mais estar na tabela pivot
    $this->assertDatabaseMissing('group_members', [
        'group_id' => $group->id,
        'user_id' => $member->id
    ]);
});

test('um membro comum NÃO pode remover outros participantes', function () {
    $owner = User::factory()->create();
    $member1 = User::factory()->create();
    $member2 = User::factory()->create();

    $group = Group::create(['name' => 'Teste', 'event_date' => '2025-12-25', 'owner_id' => $owner->id, 'invite_token' => '123']);
    $group->members()->attach([$owner->id, $member1->id, $member2->id]);

    // Membro 1 tenta remover Membro 2
    $response = $this->actingAs($member1)
        ->delete(route('groups.members.destroy', [$group, $member2]));

    // Deve ser proibido (403)
    $response->assertStatus(403);

    // Membro 2 ainda deve estar no grupo
    $this->assertDatabaseHas('group_members', ['user_id' => $member2->id]);
});

test('não é possível remover membros após o sorteio ser realizado', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();

    $group = Group::create([
        'name' => 'Já Sorteado',
        'event_date' => '2025-12-25',
        'owner_id' => $owner->id,
        'invite_token' => 'ZZZ',
        'is_drawn' => true // Sorteio já feito
    ]);
    $group->members()->attach([$owner->id, $member->id]);

    // Dono tenta remover membro
    $response = $this->actingAs($owner)
        ->delete(route('groups.members.destroy', [$group, $member]));

    // Deve dar erro de sessão (mensagem de erro)
    $response->assertSessionHas('error');

    // O membro ainda deve estar lá (para não quebrar os pares formados)
    $this->assertDatabaseHas('group_members', ['user_id' => $member->id]);
});

test('o dono não pode remover a si mesmo pela função de remover membros', function () {
    $owner = User::factory()->create();
    $group = Group::create(['name' => 'Teste', 'event_date' => '2025-12-25', 'owner_id' => $owner->id, 'invite_token' => 'XXX']);
    $group->members()->attach($owner->id);

    $response = $this->actingAs($owner)
        ->delete(route('groups.members.destroy', [$group, $owner]));

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('group_members', ['user_id' => $owner->id]);
});
