<?php

namespace App\Repositories\Contracts;

use App\Models\BudgetPlan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BudgetPlanRepositoryInterface
{
    public function getAllPaginated(array $filters, int $perPage): LengthAwarePaginator;

    public function findById(int $id): ?BudgetPlan;

    public function getActivePlan(): ?BudgetPlan;

    public function getPlanByMonthAndYear(int $month, int $year): ?BudgetPlan;

    public function getYearlyPlans(int $year): Collection;

    public function getAvailableYears(): array;

    public function create(array $data): BudgetPlan;

    public function update(BudgetPlan $budgetPlan, array $data): bool;

    public function delete(BudgetPlan $budgetPlan): bool;

    public function deactivateOtherActivePlans(?int $exceptId = null): int;
}
