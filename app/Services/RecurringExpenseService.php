<?php

namespace App\Services;

use App\Models\BudgetPlan;
use App\Models\Category;
use App\Models\RecurringExpense;
use App\Repositories\Contracts\RecurringExpenseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RecurringExpenseService
{
    public function __construct(
        private RecurringExpenseRepositoryInterface $recurringExpenseRepository,
        private ActivityLogService $activityLogService
    ) {}

    public function getRecurringExpenses(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->recurringExpenseRepository->getAllPaginated($filters, $perPage);
    }

    public function getAllRecurringExpenses(): Collection
    {
        return $this->recurringExpenseRepository->getAll();
    }

    public function getActiveRecurringExpenses(): Collection
    {
        return $this->recurringExpenseRepository->getActive();
    }

    /**
     * Get closed budget plans for the specified category IDs to optimize payment progress calculations.
     *
     * @param  array<int>  $categoryIds
     * @return Collection<int, BudgetPlan>
     */
    public function getClosedBudgetPlansForCategories(array $categoryIds): Collection
    {
        if (empty($categoryIds)) {
            return new Collection;
        }

        return BudgetPlan::query()
            ->where('status', 'closed')
            ->whereHas('items', fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->with(['items' => fn ($q) => $q->whereIn('category_id', $categoryIds)->select('id', 'budget_plan_id', 'category_id')])
            ->get(['id', 'month', 'year', 'status']);
    }

    public function getRecurringExpenseById(int $id): ?RecurringExpense
    {
        return $this->recurringExpenseRepository->findById($id);
    }

    public function createRecurringExpense(array $data): RecurringExpense
    {
        return DB::transaction(function () use ($data) {
            $categoryId = $this->resolveCategoryId($data);

            $saveData = $data;
            $saveData['category_id'] = $categoryId;
            unset($saveData['category_name']);

            if (empty($saveData['name'])) {
                $category = Category::find($categoryId);
                $saveData['name'] = $category?->name;
            }

            $recurringExpense = $this->recurringExpenseRepository->create($saveData);

            if (! $recurringExpense) {
                throw new \Exception('Gagal menyimpan data pengeluaran rutin baru.');
            }

            $this->activityLogService->log(
                'Create Recurring Expense',
                "Created recurring expense for category ID {$recurringExpense->category_id}: {$recurringExpense->amount}",
                ['recurring_expense_id' => $recurringExpense->id, 'data' => $recurringExpense->toArray()]
            );

            return $recurringExpense;
        });
    }

    public function updateRecurringExpense(RecurringExpense $recurringExpense, array $data): bool
    {
        return DB::transaction(function () use ($recurringExpense, $data) {
            $existing = $this->recurringExpenseRepository->findById($recurringExpense->id);
            if (! $existing) {
                throw new \Exception('Data pengeluaran rutin tidak ditemukan.');
            }

            $categoryId = $this->resolveCategoryId($data);

            $saveData = $data;
            $saveData['category_id'] = $categoryId;
            unset($saveData['category_name']);

            if (empty($saveData['name'])) {
                $category = Category::find($categoryId);
                $saveData['name'] = $category?->name;
            }

            $oldData = $existing->toArray();
            $updated = $this->recurringExpenseRepository->update($existing, $saveData);

            if (! $updated) {
                throw new \Exception('Gagal memperbarui data pengeluaran rutin.');
            }

            $this->activityLogService->log(
                'Update Recurring Expense',
                "Updated recurring expense ID: {$existing->id}",
                [
                    'recurring_expense_id' => $existing->id,
                    'old' => $oldData,
                    'new' => $existing->fresh()->toArray(),
                ]
            );

            return true;
        });
    }

    public function deleteRecurringExpense(RecurringExpense $recurringExpense): bool
    {
        return DB::transaction(function () use ($recurringExpense) {
            $existing = $this->recurringExpenseRepository->findById($recurringExpense->id);
            if (! $existing) {
                throw new \Exception('Data pengeluaran rutin tidak ditemukan.');
            }

            $id = $existing->id;
            $deleted = $this->recurringExpenseRepository->delete($existing);

            if (! $deleted) {
                throw new \Exception('Gagal menghapus data pengeluaran rutin.');
            }

            $this->activityLogService->log(
                'Delete Recurring Expense',
                "Deleted recurring expense ID: {$id}",
                ['deleted_recurring_expense_id' => $id]
            );

            return true;
        });
    }

    public function toggleActive(RecurringExpense $recurringExpense): bool
    {
        return DB::transaction(function () use ($recurringExpense) {
            $existing = $this->recurringExpenseRepository->findById($recurringExpense->id);
            if (! $existing) {
                throw new \Exception('Data pengeluaran rutin tidak ditemukan.');
            }

            $toggled = $this->recurringExpenseRepository->toggleActive($existing);

            if (! $toggled) {
                throw new \Exception('Gagal mengubah status aktif pengeluaran rutin.');
            }

            $statusText = $existing->is_active ? 'aktif' : 'nonaktif';
            $this->activityLogService->log(
                'Toggle Recurring Expense Status',
                "Status pengeluaran rutin ID {$existing->id} diubah menjadi {$statusText}",
                ['recurring_expense_id' => $existing->id, 'is_active' => $existing->is_active]
            );

            return true;
        });
    }

    /**
     * Resolve existing category ID or create a new expense category if manual name provided.
     *
     * @param  array<string, mixed>  $data
     */
    private function resolveCategoryId(array $data): int
    {
        if (! empty($data['category_id'])) {
            return (int) $data['category_id'];
        }

        $name = trim((string) ($data['category_name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('Kategori pengeluaran wajib dipilih atau diisi.');
        }

        $existing = Category::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->where('type', 'expense')
            ->first();

        if ($existing) {
            return $existing->id;
        }

        $nextOrderNo = ((int) Category::query()->where('type', 'expense')->max('order_no')) + 1;

        $newCategory = Category::query()->create([
            'name' => $name,
            'type' => 'expense',
            'amount' => ! empty($data['amount']) ? (float) $data['amount'] : null,
            'description' => $data['description'] ?? null,
            'is_default' => false,
            'order_no' => $nextOrderNo,
        ]);

        return $newCategory->id;
    }
}
