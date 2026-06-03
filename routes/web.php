<?php

use App\Http\Controllers\Accounting\BudgetController;
use App\Http\Controllers\Accounting\ReportController;
use App\Http\Controllers\Accounting\TransactionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Assistant\AssistantController;
use App\Http\Controllers\Assistant\TaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Distribution\MediaController;
use App\Http\Controllers\Distribution\PublishController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('assistant')->name('assistant.')->group(function () {
        Route::get('/chat', [AssistantController::class, 'index'])->name('chat');
        Route::post('/chat', [AssistantController::class, 'store'])->name('chat.store');
        Route::resource('tasks', TaskController::class)->except('show');
    });

    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::resource('transactions', TransactionController::class)->except('show');
        Route::resource('budgets', BudgetController::class)->except('show');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    Route::prefix('distribution')->name('distribution.')->group(function () {
        Route::resource('media', MediaController::class)->except('show');
        Route::get('/publish', [PublishController::class, 'index'])->name('publish.index');
        Route::get('/publish/create', [PublishController::class, 'create'])->name('publish.create');
        Route::post('/publish', [PublishController::class, 'store'])->name('publish.store');
        Route::delete('/publish/{publish}', [PublishController::class, 'destroy'])->name('publish.destroy');
    });
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except('show');
    Route::resource('roles', RoleController::class)->except('show');
});

Route::middleware(['auth', 'role:admin'])->get('/ui-kit', function () {
    return view('ui-kit');
})->name('ui-kit');

require __DIR__.'/auth.php';
