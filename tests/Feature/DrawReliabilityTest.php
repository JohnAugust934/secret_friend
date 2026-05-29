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

test('algoritmo resolve um cenário de exclusão circular complexa', function () {
    // Cenário "Cadeia Fechada":
    // A não pode tirar B
    // B não pode tirar C
    // C não pode tirar A
    // Solução única esperada: A->C, B->A, C->B

    $uA = User::factory()->create(['name' => 'A']);
    $uB = User::factory()->create(['name' => 'B']);
    $uC = User::factory()->create(['name' => 'C']);

    $group = Group::forceCreate(['name' => 'Hard Logic', 'event_date' => now(), 'owner_id' => $uA->id, 'invite_token' => 'HARD']);
    $group->members()->attach([$uA->id, $uB->id, $uC->id]);

    // Criar as restrições circulares
    Exclusion::create(['group_id' => $group->id, 'user_id' => $uA->id, 'excluded_id' => $uB->id]);
    Exclusion::create(['group_id' => $group->id, 'user_id' => $uB->id, 'excluded_id' => $uC->id]);
    Exclusion::create(['group_id' => $group->id, 'user_id' => $uC->id, 'excluded_id' => $uA->id]);

    $service = new DrawService;
    $result  = $service->draw($group, 0); // lastRound=0 = primeiro sorteio

    expect($result)->toBeTrue();

    // Validar se a lógica seguiu o único caminho possível
    $pairA = Pairing::where('group_id', $group->id)->where('santa_id', $uA->id)->where('draw_round', 1)->first();
    $pairB = Pairing::where('group_id', $group->id)->where('santa_id', $uB->id)->where('draw_round', 1)->first();
    $pairC = Pairing::where('group_id', $group->id)->where('santa_id', $uC->id)->where('draw_round', 1)->first();

    expect((int) $pairA->giftee_id)->toBe($uC->id);
    expect((int) $pairB->giftee_id)->toBe($uA->id);
    expect((int) $pairC->giftee_id)->toBe($uB->id);
});


test('algoritmo identifica corretamente cenário impossível', function () {
    // Cenário Impossível: 3 Pessoas (A, B, C)
    // A bloqueia B e C.
    // A não tem quem tirar.

    $uA = User::factory()->create();
    $uB = User::factory()->create();
    $uC = User::factory()->create();

    $group = Group::forceCreate(['name' => 'Impossible', 'event_date' => now(), 'owner_id' => $uA->id, 'invite_token' => 'IMP']);
    $group->members()->attach([$uA->id, $uB->id, $uC->id]);

    Exclusion::create(['group_id' => $group->id, 'user_id' => $uA->id, 'excluded_id' => $uB->id]);
    Exclusion::create(['group_id' => $group->id, 'user_id' => $uA->id, 'excluded_id' => $uC->id]);

    $service = new DrawService;

    // Deve lançar exceção
    expect(fn () => $service->draw($group, 0))->toThrow(Exception::class, 'Matematicamente impossível');
});

test('stress test: algoritmo funciona para grupo médio (20 pessoas)', function () {
    $owner = User::factory()->create();
    $group = Group::forceCreate(['name' => 'Stress Test', 'event_date' => now(), 'owner_id' => $owner->id, 'invite_token' => 'STR']);

    // Criar 20 membros
    $members = User::factory(20)->create();
    $group->members()->attach($members->pluck('id'));

    // Adicionar algumas exclusões aleatórias (ex: cada um bloqueia o próximo)
    foreach ($members as $index => $member) {
        if (isset($members[$index + 1])) {
            Exclusion::create([
                'group_id' => $group->id,
                'user_id' => $member->id,
                'excluded_id' => $members[$index + 1]->id,
            ]);
        }
    }

    $service   = new DrawService;
    $startTime = microtime(true);

    $service->draw($group, 0);

    $endTime  = microtime(true);
    $duration = $endTime - $startTime;

    // Deve ser rápido (menos de 1 segundo para 20 pessoas)
    expect($duration)->toBeLessThan(1.0);
    expect(Pairing::where('group_id', $group->id)->where('draw_round', 1)->count())->toBe(20);
});

test('o resultado do sorteio é guardado de forma cifrada na base de dados', function () {
    $owner = User::factory()->create();
    $uA    = User::factory()->create();
    $uB    = User::factory()->create();

    $group = Group::forceCreate(['name' => 'Crypt Test', 'event_date' => now(), 'owner_id' => $owner->id, 'invite_token' => 'CRYP']);
    $group->members()->attach([$owner->id, $uA->id, $uB->id]);

    $service = new DrawService;
    $service->draw($group, 0);

    // Ler diretamente da base de dados sem usar o Eloquent Casting
    $rawMatches = Illuminate\Support\Facades\DB::table('matches')
        ->where('group_id', $group->id)
        ->get();

    expect($rawMatches->count())->toBe(3);

    foreach ($rawMatches as $rawMatch) {
        // giftee_id deve ser uma string longa encriptada, e não um número identificável
        expect((string) $rawMatch->giftee_id)->not->toBe((string) $uA->id);
        expect((string) $rawMatch->giftee_id)->not->toBe((string) $uB->id);
        expect((string) $rawMatch->giftee_id)->not->toBe((string) $owner->id);
        
        // Desencriptar para validar
        $decrypted = Illuminate\Support\Facades\Crypt::decryptString($rawMatch->giftee_id);
        expect(in_array($decrypted, [$owner->id, $uA->id, $uB->id]))->toBeTrue();
    }
});

test('o sorteio gera um registo de auditoria com metadados seguros', function () {
    $owner = User::factory()->create();
    $uA    = User::factory()->create();
    $uB    = User::factory()->create();

    $group = Group::forceCreate(['name' => 'Audit Test', 'event_date' => now(), 'owner_id' => $owner->id, 'invite_token' => 'AUDIT']);
    $group->members()->attach([$owner->id, $uA->id, $uB->id]);

    // O sorteio em controller registra IP e User
    $response = $this->actingAs($owner)
        ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->post(route('groups.draw', $group));

    $response->assertRedirect();

    // Validar se tabela draw_audits tem 1 registro
    $audit = Illuminate\Support\Facades\DB::table('draw_audits')->first();
    
    expect($audit)->not->toBeNull();
    expect($audit->group_id)->toBe($group->id);
    expect($audit->user_id)->toBe($owner->id);
    expect($audit->ip_address)->toBe('127.0.0.1');
    expect($audit->draw_round)->toBe(1);
});
