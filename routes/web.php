<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\SavingPresetController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::post('budgets/copy-previous', [BudgetController::class, 'copyPrevious'])->name('budgets.copy-previous');
    Route::resource('budgets', BudgetController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('expenses', ExpenseController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('incomes', IncomeController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::put('saving-presets/select', [SavingPresetController::class, 'select'])->name('saving-presets.select');
    Route::put('saving-presets/{savingPreset}', [SavingPresetController::class, 'update'])->name('saving-presets.update');
});
