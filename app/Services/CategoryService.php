<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private ActivityLogService $activityLogService
    ) {}

    public function getCategories(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->categoryRepository->getAllPaginated($filters, $perPage);
    }

    public function getAllCategories(): Collection
    {
        return $this->categoryRepository->getAll();
    }

    public function getDefaultCategories(): Collection
    {
        return $this->categoryRepository->getDefaultCategories();
    }

    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->findById($id);
    }

    public function createCategory(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $category = $this->categoryRepository->create($data);

            if (! $category) {
                throw new \Exception('Gagal menyimpan data kategori baru ke database.');
            }

            $this->activityLogService->log(
                'Create Category',
                "Created a new category: {$category->name} ({$category->type})",
                ['category_id' => $category->id, 'data' => $category->only(['name', 'type', 'amount', 'description', 'is_default', 'order_no'])]
            );

            return $category;
        });
    }

    public function updateCategory(Category $category, array $data): bool
    {
        return DB::transaction(function () use ($category, $data) {
            $existingCategory = $this->categoryRepository->findById($category->id);
            if (! $existingCategory) {
                throw new \Exception('Data kategori tidak ditemukan di sistem.');
            }

            $oldData = $existingCategory->only(['name', 'type', 'amount', 'description', 'is_default', 'order_no']);

            $updated = $this->categoryRepository->update($existingCategory, $data);

            if (! $updated) {
                throw new \Exception('Gagal memperbarui data kategori ke database.');
            }

            $this->activityLogService->log(
                'Update Category',
                "Updated category: {$existingCategory->name}",
                [
                    'category_id' => $existingCategory->id,
                    'old' => $oldData,
                    'new' => $existingCategory->only(['name', 'type', 'amount', 'description', 'is_default', 'order_no']),
                ]
            );

            return true;
        });
    }

    public function deleteCategory(Category $category): bool
    {
        return DB::transaction(function () use ($category) {
            $existingCategory = $this->categoryRepository->findById($category->id);
            if (! $existingCategory) {
                throw new \Exception('Data kategori tidak ditemukan di sistem.');
            }

            $categoryName = $existingCategory->name;
            $categoryId = $existingCategory->id;

            $deleted = $this->categoryRepository->delete($existingCategory);

            if (! $deleted) {
                throw new \Exception('Gagal menghapus data kategori dari database.');
            }

            $this->activityLogService->log(
                'Delete Category',
                "Deleted category: {$categoryName}",
                ['deleted_category_id' => $categoryId]
            );

            return true;
        });
    }
}
