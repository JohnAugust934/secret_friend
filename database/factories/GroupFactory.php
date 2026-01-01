<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Grupo ' . $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'event_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'budget' => $this->faker->randomFloat(2, 50, 500),
            'invite_token' => Str::upper(Str::random(6)),
            'owner_id' => User::factory(), // Cria um user automaticamente se não for passado
            'is_drawn' => false,
        ];
    }
}
