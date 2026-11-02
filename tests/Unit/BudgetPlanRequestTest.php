<?php

namespace Tests\Unit;

use App\Http\Requests\BudgetPlan\StoreBudgetPlanRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class BudgetPlanRequestTest extends TestCase
{
    public function test_store_budget_plan_request_validates_month_year_and_status(): void
    {
        $request = new StoreBudgetPlanRequest;

        $validator = Validator::make([
            'month' => 1,
            'year' => 2026,
            'status' => 'active',
            'notes' => 'Rencana anggaran Januari',
            'items' => [
                [
                    'name' => 'Salary Juni 2026',
                    'type' => 'income',
                    'amount' => 7000000,
                ],
                [
                    'name' => 'Kosan',
                    'type' => 'expense',
                    'amount' => 1500000,
                ],
            ],
        ], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_store_budget_plan_request_rejects_invalid_month(): void
    {
        $request = new StoreBudgetPlanRequest;

        $validator = Validator::make([
            'month' => 13,
            'year' => 2026,
            'status' => 'active',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('month', $validator->errors()->toArray());
    }
}
