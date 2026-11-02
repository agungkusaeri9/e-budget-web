<?php

namespace App\Repositories\Eloquent;

use App\Models\RecurringExpense;
use App\Repositories\Contracts\RecurringExpenseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RecurringExpenseRepository implements RecurringExpenseRepositoryInterface
{
    public function getAllPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return RecurringExpense::query()
            ->with('category')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($catQuery) use ($search) {
                            $catQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', function ($query) use ($filters) {
                $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            })
            ->when($filters['category_id'] ?? null, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($filters['year'] ?? null, function ($query, $year) {
                $query->where(function ($q) use ($year) {
                    $q->where('start_year', '<=', $year)
                        ->where(function ($sub) use ($year) {
                            $sub->whereNull('end_year')
                                ->orWhere('end_year', '>=', $year);
                        });
                });
            })
            ->orderBy('start_year', 'desc')
            ->orderBy('start_month', 'desc')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getAll(): Collection
    {
        return RecurringExpense::query()
            ->with('category')
            ->orderBy('start_year')
            ->orderBy('start_month')
            ->get();
    }

    public function getActive(): Collection
    {
        return RecurringExpense::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('start_year')
            ->orderBy('start_month')
            ->get();
    }

    public function findById(int $id): ?RecurringExpense
    {
        return RecurringExpense::with('category')->find($id);
    }

    public function create(array $data): RecurringExpense
    {
        return RecurringExpense::query()->create($data);
    }

    public function update(RecurringExpense $recurringExpense, array $data): bool
    {
        return $recurringExpense->update($data);
    }

    public function delete(RecurringExpense $recurringExpense): bool
    {
        return $recurringExpense->delete();
    }

    public function toggleActive(RecurringExpense $recurringExpense): bool
    {
        return $recurringExpense->update([
            'is_active' => ! $recurringExpense->is_active,
        ]);
    }
}
