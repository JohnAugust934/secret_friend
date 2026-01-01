<?php

use App\Models\User;
use App\Models\Group;
use App\Models\Pairing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('um usuário pode entrar num grupo usando o link de convite', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);

    $newUser = User::factory()->create();

    $this->actingAs($newUser)
        ->get(route('groups.join', $group->invite_token))
        ->assertOk()
        ->assertSee($group->name); // Vê o nome do grupo na tela de convite

    // Confirma entrada
    $this->actingAs($newUser)
        ->post(route('groups.join.store', $group->invite_token), ['wishlist' => 'Livros'])
        ->assertRedirect(route('groups.show', $group));

    expect($group->members->contains($newUser))->toBeTrue();
});

test('apenas o dono pode realizar o sorteio e gera pares corretamente', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);

    // Adiciona 3 membros (total 4 com o dono) para ter sorteio válido
    $members = User::factory(3)->create();
    $group->members()->attach($owner->id);
    $group->members()->attach($members);

    // Tenta sortear com usuário comum (Deve falhar)
    $this->actingAs($members[0])
        ->post(route('groups.draw', $group))
        ->assertForbidden();

    // Sorteia com o dono
    $this->actingAs($owner)
        ->post(route('groups.draw', $group))
        ->assertRedirect(); // Volta pra página com sucesso

    // Verifica banco
    expect(Pairing::where('group_id', $group->id)->count())->toBe(4);
    expect($group->fresh()->is_drawn)->toBeTrue();
});

test('o usuário consegue ver quem lhe calhou no sorteio', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id, 'is_drawn' => true]);

    $user = User::factory()->create();
    $target = User::factory()->create(['name' => 'Alvo do Sorteio']);

    $group->members()->attach([$user->id, $target->id, $owner->id]);

    // Cria o par manualmente no banco para testar a visualização
    Pairing::create([
        'group_id' => $group->id,
        'santa_id' => $user->id,
        'giftee_id' => $target->id
    ]);

    $response = $this->actingAs($user)->get(route('groups.show', $group));

    $response->assertOk();
    $response->assertSee($target->name); // Deve ver o nome do amigo

    // CORREÇÃO: Atualizado para o texto do novo layout (Cartão 3D)
    $response->assertSee('SEU PAR É...');
    $response->assertSee('Você tirou');
});

test('o dono pode excluir o grupo e limpar os dados', function () {
    $owner = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($owner->id);

    Pairing::create([
        'group_id' => $group->id,
        'santa_id' => $owner->id,
        'giftee_id' => $owner->id
    ]);

    $this->actingAs($owner)
        ->delete(route('groups.destroy', $group))
        ->assertRedirect(route('dashboard'));

    expect(Group::find($group->id))->toBeNull();
    expect(Pairing::where('group_id', $group->id)->count())->toBe(0);
});

test('um membro comum NÃO pode excluir o grupo', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $group = Group::factory()->create(['owner_id' => $owner->id]);
    $group->members()->attach($member->id);

    $this->actingAs($member)
        ->delete(route('groups.destroy', $group))
        ->assertForbidden();

    expect(Group::find($group->id))->not->toBeNull();
});
