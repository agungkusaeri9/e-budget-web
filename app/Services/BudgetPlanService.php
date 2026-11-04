<?php

namespace App\Services;

use App\Models\BudgetPlan;
use App\Models\Category;
use App\Repositories\Contracts\BudgetPlanRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BudgetPlanService
{
    public function __construct(
        private BudgetPlanRepositoryInterface $budgetPlanRepository,
        private ActivityLogService $activityLogService
    ) {}

    public function getBudgetPlans(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->budgetPlanRepository->getAllPaginated($filters, $perPage);
    }

    public function getBudgetPlanById(int $id): ?BudgetPlan
    {
        return $this->budgetPlanRepository->findById($id);
    }

    public function getDashboardData(?int $year = null, ?int $month = null): array
    {
        $activePlan = $this->budgetPlanRepository->getActivePlan();

        if ($year === null && $month === null) {
            if ($activePlan) {
                $year = (int) $activePlan->year;
                $month = (int) $activePlan->month;
            } else {
                $year = (int) date('Y');
                $month = (int) date('n');
            }
        } else {
            $year = $year ?: (int) date('Y');
            $month = $month ?: (int) date('n');
        }

        $selectedPlan = $this->budgetPlanRepository->getPlanByMonthAndYear($month, $year);

        $selectedPlanData = null;
        if ($selectedPlan) {
            $totalIncome = (float) $selectedPlan->total_income;
            $totalExpense = (float) $selectedPlan->total_expense;
            $balance = (float) $selectedPlan->balance;
            $totalOvertime = (float) $selectedPlan->total_overtime;

            $expenseRatio = $totalIncome > 0 ? round(($totalExpense / $totalIncome) * 100, 1) : 0;
            $balanceRatio = $totalIncome > 0 ? round(($balance / $totalIncome) * 100, 1) : 0;

            $expenseItems = $selectedPlan->items
                ->where('type', 'expense')
                ->sortByDesc('amount')
                ->values()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'amount' => (float) $item->amount,
                    'percentage' => $totalExpense > 0 ? round(((float) $item->amount / $totalExpense) * 100, 1) : 0,
                ])
                ->all();

            $incomeItems = $selectedPlan->items
                ->where('type', 'income')
                ->sortByDesc('amount')
                ->values()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'amount' => (float) $item->amount,
                    'percentage' => $totalIncome > 0 ? round(((float) $item->amount / $totalIncome) * 100, 1) : 0,
                ])
                ->all();

            $selectedPlanData = [
                'id' => $selectedPlan->id,
                'month' => (int) $selectedPlan->month,
                'year' => (int) $selectedPlan->year,
                'status' => $selectedPlan->status,
                'notes' => $selectedPlan->notes,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'balance' => $balance,
                'total_overtime' => $totalOvertime,
                'expense_ratio' => $expenseRatio,
                'balance_ratio' => $balanceRatio,
                'expense_items' => $expenseItems,
                'income_items' => $incomeItems,
            ];
        }

        $yearlyPlans = $this->budgetPlanRepository->getYearlyPlans($year)->keyBy('month');
        $availableYears = $this->budgetPlanRepository->getAvailableYears();

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $shortMonthNames = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        $monthlyChartData = [];
        $yearTotalIncome = 0.0;
        $yearTotalExpense = 0.0;
        $yearTotalOvertime = 0.0;

        for ($m = 1; $m <= 12; $m++) {
            $plan = $yearlyPlans->get($m);
            $inc = $plan ? (float) $plan->total_income : 0.0;
            $exp = $plan ? (float) $plan->total_expense : 0.0;
            $bal = $plan ? (float) $plan->balance : 0.0;
            $ot = $plan ? (float) $plan->total_overtime : 0.0;

            $yearTotalIncome += $inc;
            $yearTotalExpense += $exp;
            $yearTotalOvertime += $ot;

            $monthlyChartData[] = [
                'month' => $m,
                'month_name' => $monthNames[$m],
                'short_name' => $shortMonthNames[$m],
                'total_income' => $inc,
                'total_expense' => $exp,
                'balance' => $bal,
                'total_overtime' => $ot,
                'has_plan' => $plan !== null,
                'status' => $plan?->status,
                'plan_id' => $plan?->id,
                'is_active' => $plan?->status === 'active',
            ];
        }

        return [
            'activePlan' => $selectedPlanData,
            'monthlyChartData' => $monthlyChartData,
            'summary' => [
                'selected_month' => $month,
                'selected_year' => $year,
                'available_years' => $availableYears,
                'year_total_income' => $yearTotalIncome,
                'year_total_expense' => $yearTotalExpense,
                'year_total_balance' => $yearTotalIncome - $yearTotalExpense,
                'year_total_overtime' => $yearTotalOvertime,
                'plans_count' => $yearlyPlans->count(),
            ],
        ];
    }

    public function createBudgetPlan(array $data): BudgetPlan
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            $totals = $this->calculateTotals($items);
            $status = $data['status'] ?? 'draft';

            if ($status === 'active') {
                $this->budgetPlanRepository->deactivateOtherActivePlans();
            }

            $planData = [
                'user_id' => auth()->id(),
                'month' => (int) $data['month'],
                'year' => (int) $data['year'],
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'total_income' => $totals['total_income'],
                'total_overtime' => $totals['total_overtime'],
                'total_expense' => $totals['total_expense'],
                'balance' => $totals['balance'],
            ];

            $budgetPlan = $this->budgetPlanRepository->create($planData);

            if (! $budgetPlan) {
                throw new \Exception('Gagal menyimpan rencana anggaran baru ke database.');
            }

            foreach ($items as $item) {
                $categoryId = $this->resolveCategoryId($item);

                $budgetPlan->items()->create([
                    'category_id' => $categoryId,
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'amount' => (float) ($item['amount'] ?? 0),
                    'description' => $item['description'] ?? null,
                ]);
            }

            $this->syncSavingTransactions($budgetPlan, $status);

            $this->activityLogService->log(
                'Create Budget Plan',
                "Created a new budget plan for {$budgetPlan->month}/{$budgetPlan->year}",
                [
                    'budget_plan_id' => $budgetPlan->id,
                    'month' => $budgetPlan->month,
                    'year' => $budgetPlan->year,
                    'totals' => $totals,
                ]
            );

            return $budgetPlan->load('items');
        });
    }

    public function updateBudgetPlan(BudgetPlan $budgetPlan, array $data): bool
    {
        return DB::transaction(function () use ($budgetPlan, $data) {
            $existingPlan = $this->budgetPlanRepository->findById($budgetPlan->id);
            if (! $existingPlan) {
                throw new \Exception('Data rencana anggaran tidak ditemukan di sistem.');
            }

            $items = $data['items'] ?? [];
            $totals = $this->calculateTotals($items);
            $status = $data['status'] ?? $existingPlan->status;

            if ($status === 'active') {
                $this->budgetPlanRepository->deactivateOtherActivePlans($existingPlan->id);
            }

            $planData = [
                'month' => (int) $data['month'],
                'year' => (int) $data['year'],
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'total_income' => $totals['total_income'],
                'total_overtime' => $totals['total_overtime'],
                'total_expense' => $totals['total_expense'],
                'balance' => $totals['balance'],
            ];

            $updated = $this->budgetPlanRepository->update($existingPlan, $planData);

            if (! $updated) {
                throw new \Exception('Gagal memperbarui rencana anggaran ke database.');
            }

            $existingPlan->items()->delete();

            foreach ($items as $item) {
                $categoryId = $this->resolveCategoryId($item);

                $existingPlan->items()->create([
                    'category_id' => $categoryId,
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'amount' => (float) ($item['amount'] ?? 0),
                    'description' => $item['description'] ?? null,
                ]);
            }

            $this->syncSavingTransactions($existingPlan, $status);

            $this->activityLogService->log(
                'Update Budget Plan',
                "Updated budget plan for {$existingPlan->month}/{$existingPlan->year}",
                [
                    'budget_plan_id' => $existingPlan->id,
                    'totals' => $totals,
                ]
            );

            return true;
        });
    }

    public function deleteBudgetPlan(BudgetPlan $budgetPlan): bool
    {
        return DB::transaction(function () use ($budgetPlan) {
            $existingPlan = $this->budgetPlanRepository->findById($budgetPlan->id);
            if (! $existingPlan) {
                throw new \Exception('Data rencana anggaran tidak ditemukan di sistem.');
            }

            $planPeriod = "{$existingPlan->month}/{$existingPlan->year}";
            $planId = $existingPlan->id;

            $deleted = $this->budgetPlanRepository->delete($existingPlan);

            if (! $deleted) {
                throw new \Exception('Gagal menghapus rencana anggaran dari database.');
            }

            $this->activityLogService->log(
                'Delete Budget Plan',
                "Deleted budget plan for {$planPeriod}",
                ['deleted_budget_plan_id' => $planId]
            );

            return true;
        });
    }

    private function syncSavingTransactions(BudgetPlan $plan, string $status): void
    {
        $plan->savingTransactions()->delete();

        if ($status === 'draft') {
            return;
        }

        $date = Carbon::create((int) $plan->year, (int) $plan->month, 1)->toDateString();

        $plan->items()->with('category')->where('type', 'expense')->where('amount', '>', 0)->get()
            ->filter(fn ($item) => $item->category?->is_saving && $item->category->saving_id)
            ->each(fn ($item) => $plan->savingTransactions()->create([
                'saving_id' => $item->category->saving_id,
                'amount' => $item->amount,
                'transaction_date' => $date,
                'note' => "Budget plan {$plan->month}/{$plan->year}: {$item->name}",
            ]));
    }

    /**
     * Resolve existing category ID or create a new category when input is manual.
     *
     * @param  array<string, mixed>  $item
     */
    private function resolveCategoryId(array $item): ?int
    {
        if (! empty($item['category_id'])) {
            return (int) $item['category_id'];
        }

        $name = trim((string) ($item['name'] ?? ''));
        $type = (string) ($item['type'] ?? 'expense');

        if ($name === '') {
            return null;
        }

        $existingCategory = Category::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->where('type', $type)
            ->first();

        if ($existingCategory) {
            return $existingCategory->id;
        }

        $nextOrderNo = ((int) Category::query()->where('type', $type)->max('order_no')) + 1;

        $newCategory = Category::query()->create([
            'name' => $name,
            'type' => $type,
            'amount' => ! empty($item['amount']) ? (float) $item['amount'] : null,
            'description' => $item['description'] ?? null,
            'is_default' => false,
            'order_no' => $nextOrderNo,
        ]);

        return $newCategory->id;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{total_income: float, total_overtime: float, total_expense: float, balance: float}
     */
    private function calculateTotals(array $items): array
    {
        $totalIncome = 0.0;
        $totalOvertime = 0.0;
        $totalExpense = 0.0;

        foreach ($items as $item) {
            $amount = (float) ($item['amount'] ?? 0);
            $type = $item['type'] ?? '';
            $name = (string) ($item['name'] ?? '');

            if ($type === 'income') {
                $totalIncome += $amount;
                if (stripos($name, 'lembur') !== false) {
                    $totalOvertime += $amount;
                }
            } elseif ($type === 'expense') {
                $totalExpense += $amount;
            }
        }

        return [
            'total_income' => $totalIncome,
            'total_overtime' => $totalOvertime,
            'total_expense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
        ];
    }
}
