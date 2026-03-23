<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Seed a fixed verified test user for local/dev usage.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'teste@amigosecreto.local'],
            [
                'name' => 'Usuario Teste',
                'password' => Hash::make('Teste@123456'),
                'email_verified_at' => now(),
            ]
        );
    }
}
