<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\ManagerDashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TestNotificationController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Broadcasting auth routes
Broadcast::routes(['middleware' => ['auth']]);

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Notification routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // Test notification routes (for debugging)
    Route::prefix('test')->name('test.')->group(function () {
        Route::get('/notifications', function () {
            return view('test-notifications');
        })->name('notifications');
        Route::post('/broadcast', [TestNotificationController::class, 'testBroadcast'])->name('broadcast');
        Route::post('/pusher', [TestNotificationController::class, 'testPusher'])->name('pusher');
        Route::get('/status', [TestNotificationController::class, 'status'])->name('status');
    });

    // Ticket routes
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index')->middleware('can:view-tickets');
        Route::get('/create', [TicketController::class, 'create'])->name('create')->middleware('can:create-tickets');
        Route::post('/', [TicketController::class, 'store'])->name('store')->middleware('can:create-tickets');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show')->middleware('can:view-tickets');
        Route::get('/{ticket}/edit', [TicketController::class, 'edit'])->name('edit')->middleware('can:edit-tickets');
        Route::put('/{ticket}', [TicketController::class, 'update'])->name('update')->middleware('can:edit-tickets');
        Route::patch('/{ticket}/status', [TicketController::class, 'updateStatus'])->name('update-status')->middleware('can:edit-tickets');
        Route::delete('/{ticket}', [TicketController::class, 'destroy'])->name('destroy')->middleware('can:delete-tickets');
        Route::post('/{ticket}/claim', [TicketController::class, 'claim'])->name('claim')->middleware('can:claim-tickets');
        Route::post('/{ticket}/assign', [TicketController::class, 'assign'])->name('assign')->middleware('can:assign-tickets');
        Route::post('/bulk-update', [TicketController::class, 'bulkUpdate'])->name('bulk-update')->middleware('can:bulk-ticket-operations');
        Route::get('/attachments/{attachment}/download', [TicketController::class, 'downloadAttachment'])->name('attachments.download');
        Route::delete('/attachments/{attachment}', [TicketController::class, 'deleteAttachment'])->name('attachments.delete');
        Route::post('/{ticket}/attachments', [TicketController::class, 'storeAttachments'])->name('attachments.store')->middleware('can:upload-ticket-attachments');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware(['can:view-admin-dashboard'])->group(function () {
        
        // User management
        Route::prefix('users')->name('users.')->middleware(['can:view-users'])->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create')->middleware('can:create-users');
            Route::post('/', [UserController::class, 'store'])->name('store')->middleware('can:create-users');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit')->middleware('can:edit-users');
            Route::put('/{user}', [UserController::class, 'update'])->name('update')->middleware('can:edit-users');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy')->middleware('can:delete-users');
        });

        // Category management
        Route::prefix('categories')->name('categories.')->middleware(['can:manage-categories'])->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('/create', [CategoryController::class, 'create'])->name('create');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        });

        // Role management
        Route::prefix('roles')->name('roles.')->middleware(['can:view-roles'])->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/create', [RoleController::class, 'create'])->name('create')->middleware('can:create-roles');
            Route::post('/', [RoleController::class, 'store'])->name('store')->middleware('can:create-roles');
            Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit')->middleware('can:edit-roles');
            Route::put('/{role}', [RoleController::class, 'update'])->name('update')->middleware('can:edit-roles');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy')->middleware('can:delete-roles');
        });

        // Permission management
        Route::prefix('permissions')->name('permissions.')->middleware(['can:view-permissions'])->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::get('/create', [PermissionController::class, 'create'])->name('create')->middleware('can:create-permissions');
            Route::post('/', [PermissionController::class, 'store'])->name('store')->middleware('can:create-permissions');
            Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('edit')->middleware('can:edit-permissions');
            Route::put('/{permission}', [PermissionController::class, 'update'])->name('update')->middleware('can:edit-permissions');
            Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy')->middleware('can:delete-permissions');
        });

        // Subcategory management
        Route::prefix('subcategories')->name('subcategories.')->middleware(['can:manage-categories'])->group(function () {
            Route::get('/', [SubcategoryController::class, 'index'])->name('index');
            Route::get('/create', [SubcategoryController::class, 'create'])->name('create');
            Route::post('/', [SubcategoryController::class, 'store'])->name('store');
            Route::get('/{subcategory}/edit', [SubcategoryController::class, 'edit'])->name('edit');
            Route::put('/{subcategory}', [SubcategoryController::class, 'update'])->name('update');
            Route::delete('/{subcategory}', [SubcategoryController::class, 'destroy'])->name('destroy');
            Route::get('/by-category', [SubcategoryController::class, 'getByCategory'])->name('by-category');
        });

        // Activity logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs')->middleware('can:view-activity-logs');
    });

    // Manager Dashboard Routes
    Route::prefix('manager')->name('manager.')->middleware(['can:view-manager-dashboard'])->group(function () {
        Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/chart-data', [ManagerDashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    });

    // Report Routes
    Route::prefix('reports')->name('reports.')->middleware(['can:generate-reports'])->group(function () {
        Route::get('/manager-dashboard/export', [ReportController::class, 'exportManagerDashboard'])->name('manager.export');
        Route::get('/tickets/export', [ReportController::class, 'exportTicketReport'])->name('tickets.export');
        Route::get('/technician-performance/export', [ReportController::class, 'exportTechnicianPerformance'])->name('technician.export');
    });

    // Manager Dashboard Export (additional route for easier access)
    Route::get('/manager/dashboard/export', [ReportController::class, 'exportManagerDashboard'])
        ->name('manager.dashboard.export')
        ->middleware(['auth', 'can:generate-reports']);
});
