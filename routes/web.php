<?php

use App\Http\Controllers\Accounting\BudgetController;
use App\Http\Controllers\Accounting\ReportController;
use App\Http\Controllers\Accounting\TransactionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Assistant\AssistantController;
use App\Http\Controllers\Assistant\TaskController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Distribution\MediaController;
use App\Http\Controllers\Distribution\PublishController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('assistant')->name('assistant.')->group(function () {
        Route::get('/chat', [AssistantController::class, 'index'])->name('chat');
        Route::post('/chat', [AssistantController::class, 'store'])->name('chat.store')->middleware('throttle:30,1');
        Route::post('/chat/clear', [AssistantController::class, 'clearChat'])->name('chat.clear');
        Route::post('/chat/resolve/{message}', [AssistantController::class, 'resolveConflict'])->name('chat.resolve');
        
        // Task alert and response endpoints
        Route::get('/tasks/upcoming-alerts', [TaskController::class, 'upcomingAlerts'])->name('tasks.upcoming-alerts');
        Route::post('/tasks/{task}/auto-reschedule', [TaskController::class, 'autoReschedule'])->name('tasks.auto-reschedule');
        Route::post('/tasks/{task}/cancel', [TaskController::class, 'cancelTask'])->name('tasks.cancel');
        Route::post('/tasks/{task}/reschedule-to', [TaskController::class, 'rescheduleTo'])->name('tasks.reschedule-to');

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
        Route::get('/publish/{publish}/monitor', [PublishController::class, 'monitor'])->name('publish.monitor');
        Route::get('/publish/{publish}/status', [PublishController::class, 'statusJson'])->name('publish.status');
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

// SuperAdmin Portal — full access to all accounts, features, and system
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', App\Http\Controllers\SuperAdmin\DashboardController::class)->name('dashboard');

    Route::get('/users', [App\Http\Controllers\SuperAdmin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [App\Http\Controllers\SuperAdmin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [App\Http\Controllers\SuperAdmin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [App\Http\Controllers\SuperAdmin\UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [App\Http\Controllers\SuperAdmin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\SuperAdmin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\SuperAdmin\UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/reset-password', [App\Http\Controllers\SuperAdmin\UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{user}/impersonate', [App\Http\Controllers\SuperAdmin\UserController::class, 'impersonate'])->name('users.impersonate');
    Route::post('/users/leave-impersonation', [App\Http\Controllers\SuperAdmin\UserController::class, 'leaveImpersonation'])->name('users.leave-impersonation');

    Route::get('/apis', [App\Http\Controllers\SuperAdmin\ApiController::class, 'index'])->name('apis.index');
    Route::get('/apis/test/{provider}', [App\Http\Controllers\SuperAdmin\ApiController::class, 'test'])->name('apis.test');

    Route::get('/services', [App\Http\Controllers\SuperAdmin\ServiceController::class, 'index'])->name('services.index');
    Route::post('/services/clear-cache', [App\Http\Controllers\SuperAdmin\ServiceController::class, 'clearCache'])->name('services.clear-cache');
    Route::post('/services/optimize', [App\Http\Controllers\SuperAdmin\ServiceController::class, 'optimize'])->name('services.optimize');
    Route::post('/services/retry-failed-jobs', [App\Http\Controllers\SuperAdmin\ServiceController::class, 'retryFailedJobs'])->name('services.retry-failed-jobs');
    Route::post('/services/purge-failed-jobs', [App\Http\Controllers\SuperAdmin\ServiceController::class, 'purgeFailedJobs'])->name('services.purge-failed-jobs');

    Route::get('/training', [App\Http\Controllers\SuperAdmin\TrainingController::class, 'index'])->name('training.index');
    Route::post('/training/export', [App\Http\Controllers\SuperAdmin\TrainingController::class, 'export'])->name('training.export');

    Route::get('/logs', [App\Http\Controllers\SuperAdmin\LogController::class, 'index'])->name('logs.index');
    Route::post('/logs/clear', [App\Http\Controllers\SuperAdmin\LogController::class, 'clear'])->name('logs.clear');
});

require __DIR__.'/auth.php';
