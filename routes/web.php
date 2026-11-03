<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BudgetPlanController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RecurringExpenseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::patch('recurring-expenses/{recurring_expense}/toggle', [RecurringExpenseController::class, 'toggle'])->name('recurring-expenses.toggle');
    Route::resource('recurring-expenses', RecurringExpenseController::class)->except(['show']);
    Route::resource('budget-plans', BudgetPlanController::class);
    Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');

    Route::middleware(['role:super admin'])->group(function () {
        Route::resource('roles', RoleController::class);
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    });
});

require __DIR__.'/settings.php';
