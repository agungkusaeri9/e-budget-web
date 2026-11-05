<?php

namespace App\Repositories\Eloquent;

use App\Models\ActivityLog;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function getAllPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return ActivityLog::query()
            ->with('user')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('activity', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(($filters['user_id'] ?? null) && $filters['user_id'] !== 'all', function ($query) use ($filters) {
                $query->where('user_id', $filters['user_id']);
            })
            ->when($filters['start_date'] ?? null, function ($query, $startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($filters['end_date'] ?? null, function ($query, $endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): ActivityLog
    {
        return ActivityLog::create($data);
    }
}
