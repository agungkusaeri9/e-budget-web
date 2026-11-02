<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\RecurringExpense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringExpense>
 */
class RecurringExpenseFactory extends Factory
{
    protected $model = RecurringExpense::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory()->state(['type' => 'expense']),
            'name' => $this->faker->words(2, true),
            'amount' => $this->faker->numberBetween(100, 2000) * 1000,
            'start_month' => 1,
            'start_year' => 2027,
            'end_month' => 12,
            'end_year' => 2027,
            'is_active' => true,
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the recurring expense is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
