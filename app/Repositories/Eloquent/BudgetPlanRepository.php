<?php

namespace App\Repositories\Eloquent;

use App\Models\BudgetPlan;
use App\Repositories\Contracts\BudgetPlanRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BudgetPlanRepository implements BudgetPlanRepositoryInterface
{
    public function getAllPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return BudgetPlan::query()
            ->withCount('items')
            ->when($filters['month'] ?? null, function ($query, $month) {
                if ($month !== 'all' && is_numeric($month)) {
                    $query->where('month', (int) $month);
                }
            })
            ->when($filters['year'] ?? null, function ($query, $year) {
                if ($year !== 'all' && is_numeric($year)) {
                    $query->where('year', (int) $year);
                }
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                if (in_array($status, ['draft', 'active', 'closed'], true)) {
                    $query->where('status', $status);
                }
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(int $id): ?BudgetPlan
    {
        return BudgetPlan::with('items')->find($id);
    }

    public function getActivePlan(): ?BudgetPlan
    {
        return BudgetPlan::with('items')
            ->where('status', 'active')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();
    }

    public function getPlanByMonthAndYear(int $month, int $year): ?BudgetPlan
    {
        return BudgetPlan::with('items')
            ->where('month', $month)
            ->where('year', $year)
            ->first();
    }

    public function getYearlyPlans(int $year): Collection
    {
        return BudgetPlan::where('year', $year)
            ->orderBy('month')
            ->get();
    }

    public function getAvailableYears(): array
    {
        $years = BudgetPlan::distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($y) => (int) $y)
            ->toArray();

        $currentYear = (int) date('Y');
        if (! in_array($currentYear, $years, true)) {
            $years[] = $currentYear;
            rsort($years);
        }

        return array_values($years);
    }

    public function create(array $data): BudgetPlan
    {
        return BudgetPlan::query()->create($data);
    }

    public function update(BudgetPlan $budgetPlan, array $data): bool
    {
        return $budgetPlan->update($data);
    }

    public function delete(BudgetPlan $budgetPlan): bool
    {
        return $budgetPlan->delete();
    }

    public function deactivateOtherActivePlans(?int $exceptId = null): int
    {
        return BudgetPlan::query()
            ->where('status', 'active')
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->update(['status' => 'draft']);
    }
}
