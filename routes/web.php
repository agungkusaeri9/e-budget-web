<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BudgetPlanController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\SavingProviderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('users', [UserController::class, 'index'])->middleware('can:view users')->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->middleware('can:create users')->name('users.create');
    Route::post('users', [UserController::class, 'store'])->middleware('can:create users')->name('users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->middleware('can:edit users')->name('users.edit');
    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])->middleware('can:edit users')->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('can:delete users')->name('users.destroy');

    Route::get('categories', [CategoryController::class, 'index'])->middleware('can:view categories')->name('categories.index');
    Route::get('categories/create', [CategoryController::class, 'create'])->middleware('can:create categories')->name('categories.create');
    Route::post('categories', [CategoryController::class, 'store'])->middleware('can:create categories')->name('categories.store');
    Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->middleware('can:edit categories')->name('categories.edit');
    Route::match(['put', 'patch'], 'categories/{category}', [CategoryController::class, 'update'])->middleware('can:edit categories')->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('can:delete categories')->name('categories.destroy');

    Route::get('saving-providers', [SavingProviderController::class, 'index'])->middleware('can:view saving providers')->name('saving-providers.index');
    Route::get('saving-providers/create', [SavingProviderController::class, 'create'])->middleware('can:create saving providers')->name('saving-providers.create');
    Route::post('saving-providers', [SavingProviderController::class, 'store'])->middleware('can:create saving providers')->name('saving-providers.store');
    Route::get('saving-providers/{saving_provider}/edit', [SavingProviderController::class, 'edit'])->middleware('can:edit saving providers')->name('saving-providers.edit');
    Route::match(['put', 'patch'], 'saving-providers/{saving_provider}', [SavingProviderController::class, 'update'])->middleware('can:edit saving providers')->name('saving-providers.update');
    Route::delete('saving-providers/{saving_provider}', [SavingProviderController::class, 'destroy'])->middleware('can:delete saving providers')->name('saving-providers.destroy');

    Route::get('savings', [SavingController::class, 'index'])->middleware('can:view savings')->name('savings.index');
    Route::get('savings/create', [SavingController::class, 'create'])->middleware('can:create savings')->name('savings.create');
    Route::post('savings', [SavingController::class, 'store'])->middleware('can:create savings')->name('savings.store');
    Route::get('savings/{saving}', [SavingController::class, 'show'])->middleware('can:view savings')->name('savings.show');
    Route::get('savings/{saving}/edit', [SavingController::class, 'edit'])->middleware('can:edit savings')->name('savings.edit');
    Route::match(['put', 'patch'], 'savings/{saving}', [SavingController::class, 'update'])->middleware('can:edit savings')->name('savings.update');
    Route::delete('savings/{saving}', [SavingController::class, 'destroy'])->middleware('can:delete savings')->name('savings.destroy');
    Route::post('savings/{saving}/transactions', [SavingController::class, 'adjust'])->middleware('can:edit savings')->name('savings.transactions.store');
    Route::delete('savings/{saving}/transactions/{transaction}', [SavingController::class, 'destroyTransaction'])->scopeBindings()->middleware('can:edit savings')->name('savings.transactions.destroy');

    Route::get('recurring-expenses', [RecurringExpenseController::class, 'index'])->middleware('can:view recurring expenses')->name('recurring-expenses.index');
    Route::get('recurring-expenses/create', [RecurringExpenseController::class, 'create'])->middleware('can:create recurring expenses')->name('recurring-expenses.create');
    Route::post('recurring-expenses', [RecurringExpenseController::class, 'store'])->middleware('can:create recurring expenses')->name('recurring-expenses.store');
    Route::get('recurring-expenses/{recurring_expense}/edit', [RecurringExpenseController::class, 'edit'])->middleware('can:edit recurring expenses')->name('recurring-expenses.edit');
    Route::match(['put', 'patch'], 'recurring-expenses/{recurring_expense}', [RecurringExpenseController::class, 'update'])->middleware('can:edit recurring expenses')->name('recurring-expenses.update');
    Route::patch('recurring-expenses/{recurring_expense}/toggle', [RecurringExpenseController::class, 'toggle'])->middleware('can:edit recurring expenses')->name('recurring-expenses.toggle');
    Route::delete('recurring-expenses/{recurring_expense}', [RecurringExpenseController::class, 'destroy'])->middleware('can:delete recurring expenses')->name('recurring-expenses.destroy');

    Route::get('budget-plans', [BudgetPlanController::class, 'index'])->middleware('can:view budget plans')->name('budget-plans.index');
    Route::get('budget-plans/create', [BudgetPlanController::class, 'create'])->middleware('can:create budget plans')->name('budget-plans.create');
    Route::post('budget-plans', [BudgetPlanController::class, 'store'])->middleware('can:create budget plans')->name('budget-plans.store');
    Route::get('budget-plans/{budget_plan}', [BudgetPlanController::class, 'show'])->middleware('can:view budget plans')->name('budget-plans.show');
    Route::get('budget-plans/{budget_plan}/edit', [BudgetPlanController::class, 'edit'])->middleware('can:edit budget plans')->name('budget-plans.edit');
    Route::match(['put', 'patch'], 'budget-plans/{budget_plan}', [BudgetPlanController::class, 'update'])->middleware('can:edit budget plans')->name('budget-plans.update');
    Route::delete('budget-plans/{budget_plan}', [BudgetPlanController::class, 'destroy'])->middleware('can:delete budget plans')->name('budget-plans.destroy');

    Route::get('activity', [ActivityLogController::class, 'index'])->middleware('can:view activity logs')->name('activity.index');

    Route::get('roles', [RoleController::class, 'index'])->middleware('can:view roles')->name('roles.index');
    Route::get('roles/create', [RoleController::class, 'create'])->middleware('can:create roles')->name('roles.create');
    Route::post('roles', [RoleController::class, 'store'])->middleware('can:create roles')->name('roles.store');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->middleware('can:edit roles')->name('roles.edit');
    Route::match(['put', 'patch'], 'roles/{role}', [RoleController::class, 'update'])->middleware('can:edit roles')->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('can:delete roles')->name('roles.destroy');

    Route::get('permissions', [PermissionController::class, 'index'])->middleware('can:view permissions')->name('permissions.index');
});

require __DIR__.'/settings.php';
