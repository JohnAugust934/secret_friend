<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('jkd123sn'),
            'email_verified_at' => now(), // <--- ISTO GARANTE QUE JÁ VEM VERIFICADO
        ]);

        User::factory()->create([
            'name' => 'CONTA TESTE',
            'email' => 'joao@joao.com',
            'password' => bcrypt('jkd123sn'),
            'email_verified_at' => now(), // <--- ISTO GARANTE QUE JÁ VEM VERIFICADO
        ]);

        User::factory()->create([
            'name' => 'ADMIN TESTE',
            'email' => 'admin@admin.com',
            'password' => bcrypt('jkd123sn'),
            'email_verified_at' => now(), // <--- ISTO GARANTE QUE JÁ VEM VERIFICADO
        ]);

        User::factory()->create([
            'name' => 'TESTE admin',
            'email' => 'admin@teste.com',
            'password' => bcrypt('jkd123sn'),
            'email_verified_at' => now(), // <--- ISTO GARANTE QUE JÁ VEM VERIFICADO
        ]);
    }
}
