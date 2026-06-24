<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MealLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MussleLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::redirect('/dashboard', '/');

    Route::resource('materials', MaterialController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::resource('mussle-log', MussleLogController::class)
        ->parameters(['mussle-log' => 'mussleLog'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('/weight-matrix', [MussleLogController::class, 'matrix'])->name('weight-matrix');

    Route::resource('meal-log', MealLogController::class)
        ->parameters(['meal-log' => 'mealLog'])
        ->only(['store', 'update', 'destroy']);

    Route::get('/foods', [FoodController::class, 'index'])->name('foods.index');
    Route::get('/menu', [MenuController::class, 'index'])->name('menu');
    Route::post('/menu', [MenuController::class, 'store'])->name('menu.store');
    Route::patch('/menu/{weeklyMenu}', [MenuController::class, 'update'])->name('menu.update');
    Route::delete('/menu/{weeklyMenu}', [MenuController::class, 'destroy'])->name('menu.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
