<?php

namespace Database\Factories;

use App\Models\Saving;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Saving>
 */
class SavingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Tabungan '.fake()->word(),
            'saving_provider_id' => null,
            'target_amount' => null,
            'deadline' => null,
        ];
    }
}
