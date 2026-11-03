<?php

namespace App\Repositories\Contracts;

use App\Models\ActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;

interface ActivityLogRepositoryInterface
{
    public function getAllPaginated(array $filters, int $perPage): LengthAwarePaginator;

    public function create(array $data): ActivityLog;
}
