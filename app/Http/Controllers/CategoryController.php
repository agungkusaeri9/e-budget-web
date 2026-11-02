<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\IndexCategoryRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    public function index(IndexCategoryRequest $request)
    {
        $categories = $this->categoryService->getCategories($request->filters(), $request->perPage());

        return Inertia::render('categories/Index', [
            'categories' => $categories->through(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
                'amount' => $category->amount,
                'description' => $category->description,
                'is_default' => (bool) $category->is_default,
                'order_no' => (int) $category->order_no,
                'created_at' => $category->created_at,
            ]),
            'filters' => $request->filters(),
        ]);
    }

    public function create()
    {
        return Inertia::render('categories/Create');
    }

    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        try {
            $this->categoryService->createCategory($validated);

            return redirect()->route('categories.index')->with('success', 'Kategori baru berhasil ditambahkan.');
        } catch (\Throwable $e) {
            Log::error('Gagal menambahkan kategori: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Gagal menambahkan kategori: '.$e->getMessage())->withInput();
        }
    }

    public function edit(Category $category)
    {
        return Inertia::render('categories/Edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
                'amount' => $category->amount,
                'description' => $category->description,
                'is_default' => (bool) $category->is_default,
                'order_no' => (int) $category->order_no,
            ],
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();

        try {
            $this->categoryService->updateCategory($category, $validated);

            return redirect()->route('categories.index')->with('success', 'Data kategori berhasil diperbarui.');
        } catch (\Throwable $e) {
            Log::error('Gagal memperbarui kategori: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Gagal memperbarui kategori: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(Category $category)
    {
        try {
            $this->categoryService->deleteCategory($category);

            return back()->with('success', 'Kategori berhasil dihapus.');
        } catch (\Throwable $e) {
            Log::error('Gagal menghapus kategori: '.$e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Gagal menghapus kategori: '.$e->getMessage());
        }
    }
}
