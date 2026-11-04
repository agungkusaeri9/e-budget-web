<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function getAllPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        return Category::query()
            ->with('savingAccount:id,name')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['type'] ?? null, function ($query, $type) {
                if (in_array($type, ['income', 'expense'], true)) {
                    $query->where('type', $type);
                }
            })
            ->orderBy('order_no')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getAll(): Collection
    {
        return Category::query()->orderBy('order_no')->orderBy('name')->get();
    }

    public function getDefaultCategories(): Collection
    {
        return Category::query()
            ->where('is_default', true)
            ->orderBy('order_no')
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
