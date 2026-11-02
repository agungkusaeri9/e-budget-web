<?php

namespace Tests\Feature;

use App\Models\BudgetPlan;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_plans_index_can_be_rendered(): void
    {
        $user = User::factory()->create();
        BudgetPlan::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('budget-plans.index'));

        $response->assertOk();
    }

    public function test_budget_plan_can_be_created_with_calculated_totals_and_overtime(): void
    {
        $user = User::factory()->create();
        $incomeCat = Category::factory()->income()->create(['name' => 'Salary Juni 2026', 'amount' => 6000000]);
        $overtimeCat = Category::factory()->income()->create(['name' => 'Lembur 3.5 (22/10/2026)', 'amount' => 500000]);
        $expenseCat = Category::factory()->expense()->create(['name' => 'Kosan', 'amount' => 1500000]);

        $response = $this->actingAs($user)->post(route('budget-plans.store'), [
            'month' => 1,
            'year' => 2026,
            'status' => 'active',
            'notes' => 'Pemasukan Januari untuk pengeluaran Februari',
            'items' => [
                [
                    'category_id' => $incomeCat->id,
                    'name' => $incomeCat->name,
                    'type' => 'income',
                    'amount' => 6000000,
                ],
                [
                    'category_id' => $overtimeCat->id,
                    'name' => $overtimeCat->name,
                    'type' => 'income',
                    'amount' => 500000,
                ],
                [
                    'category_id' => $expenseCat->id,
                    'name' => $expenseCat->name,
                    'type' => 'expense',
                    'amount' => 1500000,
                ],
            ],
        ]);

        $response->assertRedirect(route('budget-plans.index'));
        $this->assertDatabaseHas('budget_plans', [
            'month' => 1,
            'year' => 2026,
            'status' => 'active',
            'total_income' => 6500000,
            'total_overtime' => 500000,
            'total_expense' => 1500000,
            'balance' => 5000000,
        ]);
    }

    public function test_budget_plan_status_defaults_to_draft_when_omitted(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('budget-plans.store'), [
            'month' => 5,
            'year' => 2026,
            'notes' => 'Rencana tanpa status eksplisit',
            'items' => [
                [
                    'name' => 'Gaji Pokok',
                    'type' => 'income',
                    'amount' => 5000000,
                ],
            ],
        ]);

        $response->assertRedirect(route('budget-plans.index'));
        $this->assertDatabaseHas('budget_plans', [
            'month' => 5,
            'year' => 2026,
            'status' => 'draft',
        ]);
    }

    public function test_creating_active_budget_plan_deactivates_other_active_plans(): void
    {
        $user = User::factory()->create();

        $planA = BudgetPlan::factory()->create([
            'month' => 1,
            'year' => 2026,
            'status' => 'active',
        ]);

        $planClosed = BudgetPlan::factory()->create([
            'month' => 12,
            'year' => 2025,
            'status' => 'closed',
        ]);

        $response = $this->actingAs($user)->post(route('budget-plans.store'), [
            'month' => 2,
            'year' => 2026,
            'status' => 'active',
            'items' => [
                [
                    'name' => 'Gaji Pokok',
                    'type' => 'income',
                    'amount' => 5000000,
                ],
            ],
        ]);

        $response->assertRedirect(route('budget-plans.index'));

        // Plan A must be deactivated to 'draft'
        $this->assertEquals('draft', $planA->fresh()->status);

        // Closed plan must remain 'closed'
        $this->assertEquals('closed', $planClosed->fresh()->status);

        // New Plan must be 'active'
        $this->assertDatabaseHas('budget_plans', [
            'month' => 2,
            'year' => 2026,
            'status' => 'active',
        ]);

        // There should be exactly 1 active plan
        $this->assertEquals(1, BudgetPlan::where('status', 'active')->count());
    }

    public function test_updating_budget_plan_to_active_deactivates_other_active_plans(): void
    {
        $user = User::factory()->create();

        $planA = BudgetPlan::factory()->create([
            'month' => 1,
            'year' => 2026,
            'status' => 'active',
        ]);

        $planB = BudgetPlan::factory()->create([
            'month' => 2,
            'year' => 2026,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->put(route('budget-plans.update', $planB), [
            'month' => 2,
            'year' => 2026,
            'status' => 'active',
            'items' => [
                [
                    'name' => 'Gaji Pokok',
                    'type' => 'income',
                    'amount' => 5000000,
                ],
            ],
        ]);

        $response->assertRedirect(route('budget-plans.index'));

        // Plan A must now be draft
        $this->assertEquals('draft', $planA->fresh()->status);

        // Plan B must now be active
        $this->assertEquals('active', $planB->fresh()->status);

        // Exactly 1 active plan
        $this->assertEquals(1, BudgetPlan::where('status', 'active')->count());
    }

    public function test_updating_active_budget_plan_to_draft_does_not_affect_other_plans(): void
    {
        $user = User::factory()->create();

        $planA = BudgetPlan::factory()->create([
            'month' => 1,
            'year' => 2026,
            'status' => 'active',
        ]);

        $planB = BudgetPlan::factory()->create([
            'month' => 2,
            'year' => 2026,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->put(route('budget-plans.update', $planA), [
            'month' => 1,
            'year' => 2026,
            'status' => 'draft',
            'items' => [],
        ]);

        $response->assertRedirect(route('budget-plans.index'));

        $this->assertEquals('draft', $planA->fresh()->status);
        $this->assertEquals('draft', $planB->fresh()->status);
        $this->assertEquals(0, BudgetPlan::where('status', 'active')->count());
    }
}
