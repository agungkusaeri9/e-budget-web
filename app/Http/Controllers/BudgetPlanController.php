<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetPlan\IndexBudgetPlanRequest;
use App\Http\Requests\BudgetPlan\StoreBudgetPlanRequest;
use App\Http\Requests\BudgetPlan\UpdateBudgetPlanRequest;
use App\Models\BudgetPlan;
use App\Services\BudgetPlanService;
use App\Services\CategoryService;
use App\Services\RecurringExpenseService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BudgetPlanController extends Controller
{
    public function __construct(
        private BudgetPlanService $budgetPlanService,
        private CategoryService $categoryService,
        private RecurringExpenseService $recurringExpenseService
    ) {}

    public function index(IndexBudgetPlanRequest $request)
    {
        $budgetPlans = $this->budgetPlanService->getBudgetPlans($request->filters(), $request->perPage());

        return Inertia::render('budget-plans/Index', [
            'budgetPlans' => $budgetPlans->through(fn (BudgetPlan $plan) => [
                'id' => $plan->id,
                'month' => $plan->month,
                'year' => $plan->year,
                'status' => $plan->status,
                'notes' => $plan->notes,
                'total_income' => $plan->total_income,
                'total_overtime' => $plan->total_overtime,
                'total_expense' => $plan->total_expense,
                'balance' => $plan->balance,
                'items_count' => $plan->items_count ?? 0,
                'created_at' => $plan->created_at,
            ]),
            'filters' => $request->filters(),
        ]);
    }

    public function create()
    {
        $categories = $this->categoryService->getAllCategories();
        $recurringExpenses = $this->recurringExpenseService->getActiveRecurringExpenses();
        $categoryIds = $recurringExpenses->pluck('category_id')->unique()->filter()->values()->all();
        $closedPlans = $this->recurringExpenseService->getClosedBudgetPlansForCategories($categoryIds);

        return Inertia::render('budget-plans/Create', [
            'incomeCategories' => $categories->where('type', 'income')->sortBy('order_no')->values()->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'type' => $cat->type,
                'amount' => $cat->amount,
                'description' => $cat->description,
                'is_default' => (bool) $cat->is_default,
                'order_no' => (int) $cat->order_no,
            ]),
            'expenseCategories' => $categories->where('type', 'expense')->sortBy('order_no')->values()->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'type' => $cat->type,
                'amount' => $cat->amount,
                'description' => $cat->description,
                'is_default' => (bool) $cat->is_default,
                'order_no' => (int) $cat->order_no,
            ]),
            'recurringExpenses' => $recurringExpenses->map(function ($re) use ($closedPlans) {
                $stats = $re->getPaymentStats($closedPlans);

                return [
                    'id' => $re->id,
                    'category_id' => $re->category_id,
                    'name' => $re->name ?: $re->category?->name,
                    'amount' => (float) $re->amount,
                    'start_month' => (int) $re->start_month,
                    'start_year' => (int) $re->start_year,
                    'end_month' => $re->end_month ? (int) $re->end_month : null,
                    'end_year' => $re->end_year ? (int) $re->end_year : null,
                    'is_active' => (bool) $re->is_active,
                    'description' => $re->description,
                    'paid_count' => $stats['paid_count'],
                    'total_duration' => $stats['total_duration'],
                    'remaining_count' => $stats['remaining_count'],
                    'progress_percentage' => $stats['progress_percentage'],
                    'is_completed' => $stats['is_completed'],
                ];
            }),
        ]);
    }

    public function store(StoreBudgetPlanRequest $request)
    {
        $validated = $request->validated();

        try {
            $this->budgetPlanService->createBudgetPlan($validated);

            return redirect()->route('budget-plans.index')->with('success', 'Budget Plan baru berhasil dibuat.');
        } catch (\Throwable $e) {
            Log::error('Gagal membuat budget plan: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Gagal membuat budget plan: '.$e->getMessage())->withInput();
        }
    }

    public function show(BudgetPlan $budgetPlan)
    {
        $budgetPlan->load('items');

        return Inertia::render('budget-plans/Show', [
            'budgetPlan' => [
                'id' => $budgetPlan->id,
                'month' => $budgetPlan->month,
                'year' => $budgetPlan->year,
                'status' => $budgetPlan->status,
                'notes' => $budgetPlan->notes,
                'total_income' => $budgetPlan->total_income,
                'total_overtime' => $budgetPlan->total_overtime,
                'total_expense' => $budgetPlan->total_expense,
                'balance' => $budgetPlan->balance,
                'created_at' => $budgetPlan->created_at,
                'items' => $budgetPlan->items->map(fn ($item) => [
                    'id' => $item->id,
                    'category_id' => $item->category_id,
                    'name' => $item->name,
                    'type' => $item->type,
                    'amount' => $item->amount,
                    'description' => $item->description,
                ]),
            ],
        ]);
    }

    public function edit(BudgetPlan $budgetPlan)
    {
        $budgetPlan->load('items');
        $categories = $this->categoryService->getAllCategories();

        return Inertia::render('budget-plans/Edit', [
            'budgetPlan' => [
                'id' => $budgetPlan->id,
                'month' => $budgetPlan->month,
                'year' => $budgetPlan->year,
                'status' => $budgetPlan->status,
                'notes' => $budgetPlan->notes,
                'items' => $budgetPlan->items->map(fn ($item) => [
                    'id' => $item->id,
                    'category_id' => $item->category_id,
                    'name' => $item->name,
                    'type' => $item->type,
                    'amount' => $item->amount,
                    'description' => $item->description,
                ]),
            ],
            'incomeCategories' => $categories->where('type', 'income')->sortBy('order_no')->values()->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'type' => $cat->type,
                'amount' => $cat->amount,
                'description' => $cat->description,
                'is_default' => (bool) $cat->is_default,
                'order_no' => (int) $cat->order_no,
                'is_default' => (bool) $cat->is_default,
                'order_no' => (int) $cat->order_no,
            ]),
            'expenseCategories' => $categories->where('type', 'expense')->sortBy('order_no')->values()->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'type' => $cat->type,
                'amount' => $cat->amount,
                'description' => $cat->description,
                'is_default' => (bool) $cat->is_default,
                'order_no' => (int) $cat->order_no,
                'is_default' => (bool) $cat->is_default,
                'order_no' => (int) $cat->order_no,
            ]),
        ]);
    }

    public function update(UpdateBudgetPlanRequest $request, BudgetPlan $budgetPlan)
    {
        $validated = $request->validated();

        try {
            $this->budgetPlanService->updateBudgetPlan($budgetPlan, $validated);

            return redirect()->route('budget-plans.index')->with('success', 'Budget Plan berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui budget plan: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Gagal memperbarui budget plan: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(BudgetPlan $budgetPlan)
    {
        try {
            $this->budgetPlanService->deleteBudgetPlan($budgetPlan);

            return back()->with('success', 'Budget Plan berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus budget plan: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Gagal menghapus budget plan: '.$e->getMessage());
        }
    }
}
