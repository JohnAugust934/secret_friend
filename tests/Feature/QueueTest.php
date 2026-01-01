<?php

use App\Models\User;
use App\Models\Group;
use App\Mail\DrawResult;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
});

test('e-mails são enfileirados e não enviados imediatamente', function () {
    Mail::fake(); // Intercepta os e-mails

    $owner = User::factory()->create();
    $member1 = User::factory()->create();
    $member2 = User::factory()->create(); // <--- ADICIONADO: 3º Membro necessário

    $group = Group::create(['name' => 'Queue Test', 'event_date' => now(), 'owner_id' => $owner->id, 'invite_token' => 'Q']);

    // Anexar 3 pessoas para passar na validação do Controller (Min 3)
    $group->members()->attach([$owner->id, $member1->id, $member2->id]);

    // Executa o sorteio
    $response = $this->actingAs($owner)->post(route('groups.draw', $group));

    // Se ainda falhar, isso vai imprimir o erro no terminal para sabermos o motivo
    if (session('error')) {
        dump(session('error'));
    }

    $response->assertSessionHas('success');

    // Verifica se o e-mail foi COLOCADO NA FILA (Queued)
    Mail::assertQueued(DrawResult::class, function ($mail) use ($owner) {
        return $mail->hasTo($owner->email);
    });

    Mail::assertQueued(DrawResult::class, function ($mail) use ($member1) {
        return $mail->hasTo($member1->email);
    });

    // Garante que a classe implementa a interface correta
    $mailReflection = new ReflectionClass(DrawResult::class);
    expect($mailReflection->implementsInterface(\Illuminate\Contracts\Queue\ShouldQueue::class))->toBeTrue();
});
