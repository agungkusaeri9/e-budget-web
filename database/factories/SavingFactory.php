<?php

namespace Database\Factories;

use App\Models\Saving;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Saving>
 */
class SavingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => fn () => auth()->id() ?? User::query()->latest('id')->value('id') ?? User::factory(),
            'name' => 'Tabungan '.fake()->word(),
            'saving_provider_id' => null,
            'target_amount' => null,
            'deadline' => null,
        ];
    }
}
