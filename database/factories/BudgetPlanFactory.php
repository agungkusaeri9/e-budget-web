<?php

namespace Database\Factories;

use App\Models\BudgetPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetPlan>
 */
class BudgetPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalIncome = fake()->numberBetween(5000000, 15000000);
        $totalOvertime = fake()->numberBetween(0, 2000000);
        $totalExpense = fake()->numberBetween(2000000, 8000000);

        return [
            'user_id' => fn () => auth()->id() ?? User::query()->latest('id')->value('id') ?? User::factory(),
            'month' => fake()->numberBetween(1, 12),
            'year' => (int) date('Y'),
            'status' => fake()->randomElement(['draft', 'active', 'closed']),
            'notes' => fake()->optional()->sentence(),
            'total_income' => $totalIncome,
            'total_overtime' => $totalOvertime,
            'total_expense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
        ];
    }
}
