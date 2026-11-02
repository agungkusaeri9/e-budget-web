<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecurringExpense\IndexRecurringExpenseRequest;
use App\Http\Requests\RecurringExpense\StoreRecurringExpenseRequest;
use App\Http\Requests\RecurringExpense\UpdateRecurringExpenseRequest;
use App\Models\RecurringExpense;
use App\Services\CategoryService;
use App\Services\RecurringExpenseService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class RecurringExpenseController extends Controller
{
    public function __construct(
        private RecurringExpenseService $recurringExpenseService,
        private CategoryService $categoryService
    ) {}

    public function index(IndexRecurringExpenseRequest $request)
    {
        $recurringExpenses = $this->recurringExpenseService->getRecurringExpenses(
            $request->filters(),
            $request->perPage()
        );

        $categoryIds = $recurringExpenses->getCollection()->pluck('category_id')->unique()->filter()->values()->all();
        $closedPlans = $this->recurringExpenseService->getClosedBudgetPlansForCategories($categoryIds);

        $categories = $this->categoryService->getAllCategories()
            ->where('type', 'expense')
            ->sortBy('order_no')
            ->values();

        return Inertia::render('recurring-expenses/Index', [
            'recurringExpenses' => $recurringExpenses->through(function (RecurringExpense $item) use ($closedPlans) {
                $stats = $item->getPaymentStats($closedPlans);

                return [
                    'id' => $item->id,
                    'category_id' => $item->category_id,
                    'category_name' => $item->category?->name,
                    'name' => $item->name,
                    'amount' => $item->amount,
                    'start_month' => $item->start_month,
                    'start_year' => $item->start_year,
                    'end_month' => $item->end_month,
                    'end_year' => $item->end_year,
                    'is_active' => (bool) $item->is_active,
                    'description' => $item->description,
                    'created_at' => $item->created_at,
                    'paid_count' => $stats['paid_count'],
                    'total_duration' => $stats['total_duration'],
                    'remaining_count' => $stats['remaining_count'],
                    'progress_percentage' => $stats['progress_percentage'],
                    'is_completed' => $stats['is_completed'],
                    'total_amount' => $stats['total_amount'],
                    'paid_amount' => $stats['paid_amount'],
                    'remaining_amount' => $stats['remaining_amount'],
                ];
            }),
            'categories' => $categories->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
            ]),
            'filters' => $request->filters(),
        ]);
    }

    public function create()
    {
        $categories = $this->categoryService->getAllCategories()
            ->where('type', 'expense')
            ->sortBy('order_no')
            ->values();

        return Inertia::render('recurring-expenses/Create', [
            'expenseCategories' => $categories->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'amount' => $cat->amount,
                'description' => $cat->description,
            ]),
        ]);
    }

    public function store(StoreRecurringExpenseRequest $request)
    {
        $validated = $request->validated();

        try {
            $this->recurringExpenseService->createRecurringExpense($validated);

            return redirect()->route('recurring-expenses.index')
                ->with('success', 'Pengeluaran rutin baru berhasil ditambahkan.');
        } catch (\Throwable $e) {
            Log::error('Gagal menambahkan pengeluaran rutin: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Gagal menambahkan pengeluaran rutin: '.$e->getMessage())->withInput();
        }
    }

    public function edit(RecurringExpense $recurringExpense)
    {
        $categories = $this->categoryService->getAllCategories()
            ->where('type', 'expense')
            ->sortBy('order_no')
            ->values();

        $stats = $recurringExpense->getPaymentStats();

        return Inertia::render('recurring-expenses/Edit', [
            'recurringExpense' => [
                'id' => $recurringExpense->id,
                'category_id' => $recurringExpense->category_id,
                'name' => $recurringExpense->name,
                'amount' => $recurringExpense->amount,
                'start_month' => $recurringExpense->start_month,
                'start_year' => $recurringExpense->start_year,
                'end_month' => $recurringExpense->end_month,
                'end_year' => $recurringExpense->end_year,
                'is_active' => (bool) $recurringExpense->is_active,
                'description' => $recurringExpense->description,
                'paid_count' => $stats['paid_count'],
                'total_duration' => $stats['total_duration'],
                'remaining_count' => $stats['remaining_count'],
                'progress_percentage' => $stats['progress_percentage'],
                'is_completed' => $stats['is_completed'],
                'total_amount' => $stats['total_amount'],
                'paid_amount' => $stats['paid_amount'],
                'remaining_amount' => $stats['remaining_amount'],
            ],
            'expenseCategories' => $categories->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'amount' => $cat->amount,
                'description' => $cat->description,
            ]),
        ]);
    }

    public function update(UpdateRecurringExpenseRequest $request, RecurringExpense $recurringExpense)
    {
        $validated = $request->validated();

        try {
            $this->recurringExpenseService->updateRecurringExpense($recurringExpense, $validated);

            return redirect()->route('recurring-expenses.index')
                ->with('success', 'Data pengeluaran rutin berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui pengeluaran rutin: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Gagal memperbarui pengeluaran rutin: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(RecurringExpense $recurringExpense)
    {
        try {
            $this->recurringExpenseService->deleteRecurringExpense($recurringExpense);

            return back()->with('success', 'Pengeluaran rutin berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus pengeluaran rutin: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Gagal menghapus pengeluaran rutin: '.$e->getMessage());
        }
    }

    public function toggle(RecurringExpense $recurringExpense)
    {
        try {
            $this->recurringExpenseService->toggleActive($recurringExpense);

            $status = $recurringExpense->fresh()->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return back()->with('success', "Pengeluaran rutin berhasil {$status}.");
        } catch (\Throwable $e) {
            Log::error('Gagal mengubah status pengeluaran rutin: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Gagal mengubah status: '.$e->getMessage());
        }
    }
}
