<?php

namespace Tests\Feature;

use App\Models\BudgetPlan;
use App\Models\BudgetPlanItem;
use App\Models\Category;
use App\Models\RecurringExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_recurring_expenses_index_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        RecurringExpense::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('recurring-expenses.index'));

        $response->assertOk();
    }

    public function test_recurring_expense_create_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        Category::factory()->state(['type' => 'expense'])->create();

        $response = $this->actingAs($user)->get(route('recurring-expenses.create'));

        $response->assertOk();
    }

    public function test_recurring_expense_can_be_created_successfully(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->state(['type' => 'expense', 'name' => 'Motor'])->create();

        $response = $this->actingAs($user)->post(route('recurring-expenses.store'), [
            'category_id' => $category->id,
            'name' => 'Cicilan Honda Vario',
            'amount' => 500000,
            'start_month' => 1,
            'start_year' => 2027,
            'end_month' => 12,
            'end_year' => 2027,
            'is_active' => true,
            'description' => 'Cicilan 12 bulan',
        ]);

        $response->assertRedirect(route('recurring-expenses.index'));
        $this->assertDatabaseHas('recurring_expenses', [
            'category_id' => $category->id,
            'name' => 'Cicilan Honda Vario',
            'amount' => 500000,
            'start_month' => 1,
            'start_year' => 2027,
            'end_month' => 12,
            'end_year' => 2027,
            'is_active' => true,
        ]);
    }

    public function test_recurring_expense_can_be_created_with_new_category_input(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('recurring-expenses.store'), [
            'category_name' => 'Kosan Adik',
            'name' => 'Kosan Aziz Semester 1',
            'amount' => 1200000,
            'start_month' => 1,
            'start_year' => 2027,
            'end_month' => 6,
            'end_year' => 2027,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('recurring-expenses.index'));

        // Category was automatically created in categories table
        $this->assertDatabaseHas('categories', [
            'name' => 'Kosan Adik',
            'type' => 'expense',
        ]);

        $category = Category::where('name', 'Kosan Adik')->first();
        $this->assertNotNull($category);

        $this->assertDatabaseHas('recurring_expenses', [
            'category_id' => $category->id,
            'name' => 'Kosan Aziz Semester 1',
            'amount' => 1200000,
        ]);
    }

    public function test_recurring_expense_requires_either_category_id_or_category_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('recurring-expenses.store'), [
            'amount' => 500000,
            'start_month' => 1,
            'start_year' => 2027,
        ]);

        $response->assertSessionHasErrors(['category_id', 'category_name']);
    }

    public function test_recurring_expense_validation_fails_if_end_period_is_before_start_period(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->state(['type' => 'expense'])->create();

        $response = $this->actingAs($user)->post(route('recurring-expenses.store'), [
            'category_id' => $category->id,
            'amount' => 500000,
            'start_month' => 6,
            'start_year' => 2027,
            'end_month' => 3,
            'end_year' => 2027,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors(['end_month']);
    }

    public function test_recurring_expense_can_be_updated(): void
    {
        $user = User::factory()->create();
        $item = RecurringExpense::factory()->create([
            'amount' => 500000,
        ]);

        $response = $this->actingAs($user)->put(route('recurring-expenses.update', $item), [
            'category_id' => $item->category_id,
            'name' => 'Cicilan Diperbarui',
            'amount' => 550000,
            'start_month' => 1,
            'start_year' => 2027,
            'end_month' => 12,
            'end_year' => 2027,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('recurring-expenses.index'));
        $this->assertDatabaseHas('recurring_expenses', [
            'id' => $item->id,
            'name' => 'Cicilan Diperbarui',
            'amount' => 550000,
        ]);
    }

    public function test_recurring_expense_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $item = RecurringExpense::factory()->create();

        $response = $this->actingAs($user)->delete(route('recurring-expenses.destroy', $item));

        $response->assertRedirect();
        $this->assertDatabaseMissing('recurring_expenses', [
            'id' => $item->id,
        ]);
    }

    public function test_recurring_expense_status_can_be_toggled(): void
    {
        $user = User::factory()->create();
        $item = RecurringExpense::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->patch(route('recurring-expenses.toggle', $item));

        $response->assertRedirect();
        $this->assertDatabaseHas('recurring_expenses', [
            'id' => $item->id,
            'is_active' => false,
        ]);
    }

    public function test_recurring_expense_is_active_for_period_helper(): void
    {
        $item = RecurringExpense::factory()->create([
            'start_month' => 1,
            'start_year' => 2027,
            'end_month' => 12,
            'end_year' => 2027,
            'is_active' => true,
        ]);

        // Before start period: false
        $this->assertFalse($item->isActiveForPeriod(12, 2026));

        // In range: true
        $this->assertTrue($item->isActiveForPeriod(1, 2027));
        $this->assertTrue($item->isActiveForPeriod(6, 2027));
        $this->assertTrue($item->isActiveForPeriod(12, 2027));

        // After end period: false
        $this->assertFalse($item->isActiveForPeriod(1, 2028));

        // Inactive flag: false
        $item->is_active = false;
        $this->assertFalse($item->isActiveForPeriod(6, 2027));
    }

    public function test_create_budget_plan_page_includes_recurring_expenses(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->state(['type' => 'expense', 'name' => 'Motor'])->create();
        $recurring = RecurringExpense::factory()->create([
            'category_id' => $category->id,
            'amount' => 500000,
            'start_month' => 1,
            'start_year' => 2027,
            'end_month' => 12,
            'end_year' => 2027,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('budget-plans.create'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('budget-plans/Create')
            ->has('recurringExpenses', 1)
            ->where('recurringExpenses.0.category_id', $category->id)
            ->where('recurringExpenses.0.amount', 500000)
        );
    }

    public function test_recurring_expense_calculates_paid_count_only_from_closed_budget_plans_in_period(): void
    {
        $categoryA = Category::factory()->state(['type' => 'expense', 'name' => 'Motor'])->create();
        $categoryB = Category::factory()->state(['type' => 'expense', 'name' => 'Kosan'])->create();

        $recurring = RecurringExpense::factory()->create([
            'category_id' => $categoryA->id,
            'name' => 'Cicilan Motor',
            'amount' => 500000,
            'start_month' => 1,
            'start_year' => 2027,
            'end_month' => 12,
            'end_year' => 2027,
            'is_active' => true,
        ]);

        // 1. Closed plan inside period with Category A -> SHOULD COUNT
        $planJan27 = BudgetPlan::factory()->create([
            'month' => 1,
            'year' => 2027,
            'status' => 'closed',
        ]);
        BudgetPlanItem::create([
            'budget_plan_id' => $planJan27->id,
            'category_id' => $categoryA->id,
            'name' => 'Motor',
            'type' => 'expense',
            'amount' => 500000,
        ]);

        // 2. Another closed plan inside period with Category A -> SHOULD COUNT
        $planFeb27 = BudgetPlan::factory()->create([
            'month' => 2,
            'year' => 2027,
            'status' => 'closed',
        ]);
        BudgetPlanItem::create([
            'budget_plan_id' => $planFeb27->id,
            'category_id' => $categoryA->id,
            'name' => 'Motor',
            'type' => 'expense',
            'amount' => 500000,
        ]);

        // 3. Active plan inside period with Category A -> SHOULD NOT COUNT (not closed)
        $planMar27 = BudgetPlan::factory()->create([
            'month' => 3,
            'year' => 2027,
            'status' => 'active',
        ]);
        BudgetPlanItem::create([
            'budget_plan_id' => $planMar27->id,
            'category_id' => $categoryA->id,
            'name' => 'Motor',
            'type' => 'expense',
            'amount' => 500000,
        ]);

        // 4. Draft plan inside period with Category A -> SHOULD NOT COUNT (not closed)
        $planApr27 = BudgetPlan::factory()->create([
            'month' => 4,
            'year' => 2027,
            'status' => 'draft',
        ]);
        BudgetPlanItem::create([
            'budget_plan_id' => $planApr27->id,
            'category_id' => $categoryA->id,
            'name' => 'Motor',
            'type' => 'expense',
            'amount' => 500000,
        ]);

        // 5. Closed plan before start period (Dec 2026) -> SHOULD NOT COUNT
        $planDec26 = BudgetPlan::factory()->create([
            'month' => 12,
            'year' => 2026,
            'status' => 'closed',
        ]);
        BudgetPlanItem::create([
            'budget_plan_id' => $planDec26->id,
            'category_id' => $categoryA->id,
            'name' => 'Motor',
            'type' => 'expense',
            'amount' => 500000,
        ]);

        // 6. Closed plan after end period (Jan 2028) -> SHOULD NOT COUNT
        $planJan28 = BudgetPlan::factory()->create([
            'month' => 1,
            'year' => 2028,
            'status' => 'closed',
        ]);
        BudgetPlanItem::create([
            'budget_plan_id' => $planJan28->id,
            'category_id' => $categoryA->id,
            'name' => 'Motor',
            'type' => 'expense',
            'amount' => 500000,
        ]);

        // 7. Closed plan inside period but with Category B only -> SHOULD NOT COUNT
        $planMay27 = BudgetPlan::factory()->create([
            'month' => 5,
            'year' => 2027,
            'status' => 'closed',
        ]);
        BudgetPlanItem::create([
            'budget_plan_id' => $planMay27->id,
            'category_id' => $categoryB->id,
            'name' => 'Kosan',
            'type' => 'expense',
            'amount' => 1400000,
        ]);

        $this->assertEquals(12, $recurring->total_duration);
        $this->assertEquals(2, $recurring->paid_count);
        $this->assertEquals(10, $recurring->remaining_count);
        $this->assertEquals(16.7, $recurring->progress_percentage);
        $this->assertFalse($recurring->is_completed);
        $this->assertEquals(6000000, $recurring->total_amount);
        $this->assertEquals(1000000, $recurring->paid_amount);
        $this->assertEquals(5000000, $recurring->remaining_amount);

        $stats = $recurring->getPaymentStats();
        $this->assertEquals(2, $stats['paid_count']);
        $this->assertEquals(12, $stats['total_duration']);
        $this->assertEquals(10, $stats['remaining_count']);
        $this->assertEquals(16.7, $stats['progress_percentage']);
        $this->assertFalse($stats['is_completed']);
        $this->assertEquals(6000000, $stats['total_amount']);
        $this->assertEquals(1000000, $stats['paid_amount']);
        $this->assertEquals(5000000, $stats['remaining_amount']);
    }

    public function test_recurring_expense_marks_as_completed_when_all_duration_periods_paid(): void
    {
        $category = Category::factory()->state(['type' => 'expense', 'name' => 'Motor'])->create();

        $recurring = RecurringExpense::factory()->create([
            'category_id' => $category->id,
            'start_month' => 1,
            'start_year' => 2027,
            'end_month' => 2,
            'end_year' => 2027,
            'is_active' => true,
        ]);

        for ($month = 1; $month <= 2; $month++) {
            $plan = BudgetPlan::factory()->create([
                'month' => $month,
                'year' => 2027,
                'status' => 'closed',
            ]);
            BudgetPlanItem::create([
                'budget_plan_id' => $plan->id,
                'category_id' => $category->id,
                'name' => 'Motor',
                'type' => 'expense',
                'amount' => 500000,
            ]);
        }

        $this->assertEquals(2, $recurring->total_duration);
        $this->assertEquals(2, $recurring->paid_count);
        $this->assertEquals(0, $recurring->remaining_count);
        $this->assertEquals(100.0, $recurring->progress_percentage);
        $this->assertTrue($recurring->is_completed);
    }

    public function test_recurring_expenses_index_page_returns_payment_stats(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->state(['type' => 'expense', 'name' => 'Motor'])->create();

        $recurring = RecurringExpense::factory()->create([
            'category_id' => $category->id,
            'amount' => 500000,
            'start_month' => 1,
            'start_year' => 2027,
            'end_month' => 12,
            'end_year' => 2027,
            'is_active' => true,
        ]);

        $plan = BudgetPlan::factory()->create([
            'month' => 1,
            'year' => 2027,
            'status' => 'closed',
        ]);
        BudgetPlanItem::create([
            'budget_plan_id' => $plan->id,
            'category_id' => $category->id,
            'name' => 'Motor',
            'type' => 'expense',
            'amount' => 500000,
        ]);

        $response = $this->actingAs($user)->get(route('recurring-expenses.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('recurring-expenses/Index')
            ->has('recurringExpenses.data', 1)
            ->where('recurringExpenses.data.0.paid_count', 1)
            ->where('recurringExpenses.data.0.total_duration', 12)
            ->where('recurringExpenses.data.0.remaining_count', 11)
            ->where('recurringExpenses.data.0.progress_percentage', 8.3)
            ->where('recurringExpenses.data.0.is_completed', false)
            ->where('recurringExpenses.data.0.total_amount', 6000000)
            ->where('recurringExpenses.data.0.paid_amount', 500000)
            ->where('recurringExpenses.data.0.remaining_amount', 5500000)
        );
    }
}
