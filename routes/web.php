<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Ticket routes
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index')->middleware('can:view-tickets');
        Route::get('/create', [TicketController::class, 'create'])->name('create')->middleware('can:create-tickets');
        Route::post('/', [TicketController::class, 'store'])->name('store')->middleware('can:create-tickets');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show')->middleware('can:view-tickets');
        Route::get('/{ticket}/edit', [TicketController::class, 'edit'])->name('edit')->middleware('can:edit-tickets');
        Route::put('/{ticket}', [TicketController::class, 'update'])->name('update')->middleware('can:edit-tickets');
        Route::delete('/{ticket}', [TicketController::class, 'destroy'])->name('destroy')->middleware('can:delete-tickets');
        Route::get('/assigned/me', [TicketController::class, 'assigned'])->name('assigned');
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

        // Activity logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs')->middleware('can:view-activity-logs');
    });
});
