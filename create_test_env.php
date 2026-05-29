<?php

use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

echo "A criar utilizador de teste...\n";

$user = User::updateOrCreate(
    ['email' => 'teste@teste.com'],
    [
        'name' => 'Utilizador de Teste',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
    ]
);

echo "A criar membros falsos...\n";
$fake1 = User::factory()->create(['name' => 'Ana Falsa']);
$fake2 = User::factory()->create(['name' => 'Bruno Falso']);
$fake3 = User::factory()->create(['name' => 'Carlos Falso']);

echo "A criar grupo de teste...\n";
$group = Group::forceCreate([
    'name' => 'Teste das Novas Features',
    'event_date' => now()->addDays(15),
    'location' => 'Restaurante O Testador',
    'budget_limit' => '10€ - 20€',
    'budget' => 20,
    'description' => 'Grupo configurado automaticamente para testar o re-sorteio, location e budget_limit.',
    'owner_id' => $user->id,
    'invite_token' => Str::upper(Str::random(6)),
]);

$group->members()->attach([
    $user->id => ['wishlist' => 'Gostava de receber um livro de programação.'],
    $fake1->id => ['wishlist' => 'Adoro chocolates.'],
    $fake2->id => ['wishlist' => 'Uma caneca engraçada.'],
    $fake3->id => ['wishlist' => 'Meias coloridas.'],
]);

echo "Ambiente de teste criado com sucesso!\n";
echo "Email: teste@teste.com\n";
echo "Password: password123\n";
