<?php

namespace App\Repositories\Contracts;

use App\Models\RecurringExpense;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RecurringExpenseRepositoryInterface
{
    public function getAllPaginated(array $filters, int $perPage): LengthAwarePaginator;

    public function getAll(): Collection;

    public function getActive(): Collection;

    public function findById(int $id): ?RecurringExpense;

    public function create(array $data): RecurringExpense;

    public function update(RecurringExpense $recurringExpense, array $data): bool;

    public function delete(RecurringExpense $recurringExpense): bool;

    public function toggleActive(RecurringExpense $recurringExpense): bool;
}
