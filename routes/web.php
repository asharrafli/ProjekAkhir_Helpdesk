<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
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
use App\Http\Controllers\ChatController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TestNotificationController;
use App\Http\Controllers\TestTicketController;
use App\Http\Controllers\TestBroadcastController;
use App\Livewire\Admin\Users\ShowUser;
use App\Models\Tickets;
use Illuminate\Notifications\Events\NotificationSent;

Route::get('/', function () {
    return Auth::check() 
        ? redirect()->route('home') 
        : redirect()->route('login');
});

Auth::routes();

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
    //Chat Routes
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::get('/rooms', [ChatController::class, 'getUserChats'])->name('rooms');
        Route::post('/customer-support', [ChatController::class, 'createCustomerSupportRoom'])->name('customer.create');
        Route::post('/chat/technician-room', [ChatController::class, 'createTechnicianRoom']); // Pastikan route ini ada
        Route::post('/technician-room', [ChatController::class, 'createTechnicianRoom'])->name('technician.create');
        Route::post('/message', [ChatController::class, 'sendMessage'])->name('message.send');
        Route::get('/room/{roomId}/messages', [ChatController::class, 'getRoomMessages'])->name('room.messages');
        Route::get('/admin/users', [ChatController::class, 'getAllUsersForAdmin'])->name('admin.users');

    });

    // Test notification routes (for debugging)
    Route::prefix('test')->name('test.')->group(function () {
        Route::get('/notifications', function () {
            return view('test-notifications');
        })->name('notifications');
        Route::get('/broadcast-auth', function () {
            return view('test-broadcast-auth');
        })->name('broadcast-auth');
        Route::post('/broadcast', [TestNotificationController::class, 'testBroadcast'])->name('broadcast');
        Route::post('/pusher', [TestNotificationController::class, 'testPusher'])->name('pusher');
        Route::get('/status', [TestNotificationController::class, 'status'])->name('status');
        Route::post('/create-notification', [TestNotificationController::class, 'createTestNotification'])->name('create-notification');
        
        // Test Broadcasting routes
        Route::get('/broadcast-debug', [TestBroadcastController::class, 'testBroadcast'])->name('broadcast-debug');
        Route::get('/pusher-test', [TestBroadcastController::class, 'testPusherConnection'])->name('pusher-test');
        Route::get('/config-debug', [TestBroadcastController::class, 'debugBroadcastConfig'])->name('config-debug');
        Route::get('/debug-dashboard', function () {
            return view('test-broadcast-debug');
        })->name('debug-dashboard');
        Route::get('/trigger-notification', function () {
            try {
                $user = Auth::user();
                
                // Create a test notification
                $notification = [
                    'id' => time(),
                    'title' => 'Test Notification',
                    'message' => 'This is a test notification from the backend',
                    'type' => 'success',
                    'created_at' => now(),
                    'read_at' => null,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name
                    ]
                ];
                
                // Save to database
                $user->notifications()->create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'type' => 'App\Notifications\TestNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $user->id,
                    'data' => json_encode($notification),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // ✅ Broadcast to GLOBAL channel - semua user dapat menerima
                broadcast(new \App\Events\GlobalNotificationEvent($notification));
                
                // ✅ Broadcast to specific user channel
                broadcast(new \App\Events\NotificationEvent($user, $notification));
                
                Log::info('✅ Test notification broadcasted to all channels', [
                    'user_id' => $user->id,
                    'notification_id' => $notification['id']
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Test notification created and broadcasted globally',
                    'notification' => $notification
                ]);
                
            } catch (\Exception $e) {
                Log::error('❌ Test notification failed', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine()
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
        })->name('trigger-notification');
        
        Route::get('/local-notification', function () {
            return response()->json([
                'success' => true,
                'notification' => [
                    'id' => time(),
                    'title' => 'Local Test Notification',
                    'message' => 'This is a local test notification',
                    'type' => 'info',
                    'created_at' => now()
                ]
            ]);
        })->name('local-notification');
    });

    // Ticket routes
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index')->middleware('can:view-tickets');
        Route::get('/create', [TicketController::class, 'create'])->name('create')->middleware('can:create-tickets');
        Route::post('/', [TicketController::class, 'store'])->name('store')->middleware('can:create-tickets');
        Route::get('/assigned', [TicketController::class, 'assigned'])->name('assigned')->middleware('can:view-assigned-tickets');
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
        Route::post('/{ticket}/comments', [TicketController::class, 'storeComment'])->name('comments.store')->middleware('can:comment-on-tickets');
    });

    // Admin routes
    Route::prefix('admin')->name('admin.')->middleware(['can:view-admin-dashboard'])->group(function () {

        // User management
        Route::prefix('users')->name('users.')->middleware(['can:view-users'])->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/{user}/show', ShowUser::class)->name('show');
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
    Route::prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/chart-data', [ManagerDashboardController::class, 'getChartData'])->name('dashboard.chart-data');
        Route::post('/dashboard/export-pdf', [ManagerDashboardController::class, 'exportPdf'])->name('dashboard.export-pdf');
    });

    // Test route for chart API (temporary)
    Route::get('/test-chart-api', [ManagerDashboardController::class, 'getChartData'])->name('test.chart.api');

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

    // Debug routes (remove in production)
    Route::get('/test-notification', function () {
        $user = Auth::user();
        $ticket = Tickets::first();

        if (!$ticket) {
            return 'No tickets found to test with';
        }

        \Illuminate\Support\Facades\Log::info('Testing notification for user: ' . $user->id);

        $user->notify(new \App\Notifications\TicketCreated($ticket));

        return response()->json([
            'message' => 'Test notification sent!',
            'user' => $user->name,
            'ticket' => $ticket->ticket_number
        ]);
    })->middleware('auth');

    Route::get('/test-events', function () {
        $user = Auth::user();

        // Ambil ticket terbaru
        $ticket = Tickets::latest()->first();

        if ($ticket) {
            Log::info('🔥 Manual test - dispatching TicketCreated event', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number
            ]);

            // Test event
            event(new \App\Events\TicketCreated($ticket));

            Log::info('✅ TicketCreated event dispatched successfully');

            return response()->json([
                'message' => 'TicketCreated event dispatched',
                'ticket' => $ticket->ticket_number,
                'ticket_id' => $ticket->id,
                'user' => $user->name,
                'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
                'event_dispatched' => true
            ]);
        }

        return response()->json(['error' => 'No ticket found']);
    })->middleware('auth');

    Route::get('/test-broadcast', function () {
        \Illuminate\Support\Facades\Log::info('🧪 Testing broadcast manually');

        // Test broadcast langsung
        broadcast(new \App\Events\TicketCreated(Tickets::find(29)));

        return response()->json([
            'message' => 'Broadcast test sent',
            'timestamp' => now()
        ]);
    });

     Route::get('/test/create-ticket', [TestTicketController::class, 'createTestTicket'])
            ->name('test.create-ticket');


    Route::get('/test-broadcast-complete', function () {
        try {
            Log::info('🔬 Starting complete broadcast test');

            $ticket = Tickets::find(29);
            if (!$ticket) {
                return response()->json(['error' => 'Ticket not found'], 404);
            }

            // Test 1: Dispatch event
            Log::info('📡 Dispatching TicketCreated event', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number
            ]);

            $event = new \App\Events\TicketCreated($ticket);
            broadcast($event);

            Log::info('✅ Event dispatched successfully');

            // Test 2: Manual channel broadcast
            Log::info('📺 Testing manual channel broadcast');

            $channelData = [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'title' => $ticket->title ?? $ticket->title_ticket,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'created_at' => $ticket->created_at->toISOString(),
                'user' => [
                    'id' => $ticket->user->id,
                    'name' => $ticket->user->name
                ]
            ];

            // Broadcast to all relevant channels
            broadcast(new \App\Events\TicketCreated($ticket))
                ->toOthers();

            Log::info('📡 Manual broadcast sent to channels', $channelData);

            return response()->json([
                'success' => true,
                'message' => 'Complete broadcast test executed',
                'ticket' => $channelData,
                'channels_tested' => [
                    'notifications.1', // Admin
                    'notifications.5', // User who created
                    'tickets.global'   // Global tickets channel
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Complete broadcast test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    })->middleware('auth');

// Update test route untuk test semua user
Route::get('/test-all-users-broadcast', function () {
    try {
        Log::info('🔬 Testing broadcast to ALL users');

        $ticket = Tickets::find(29);
        if (!$ticket) {
            return response()->json(['error' => 'Ticket not found'], 404);
        }

        // Get all users
        $allUsers = \App\Models\User::all();
        
        // Send notifications to all users
        foreach ($allUsers as $user) {
            $user->notify(new \App\Notifications\TicketCreated($ticket));
        }

        // Dispatch event
        event(new \App\Events\TicketCreated($ticket));

        Log::info('✅ Broadcast sent to ALL users', [
            'ticket_id' => $ticket->id,
            'users_count' => $allUsers->count(),
            'users' => $allUsers->pluck('name')->toArray()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Broadcast sent to ALL users',
            'recipients_count' => $allUsers->count(),
            'recipients' => $allUsers->pluck('name')->toArray(),
            'ticket' => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'title' => $ticket->title ?? $ticket->title_ticket,
            ]
        ]);
    } catch (\Exception $e) {
        Log::error('❌ All users broadcast test failed', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
})->middleware('auth');

    Route::get('/test-simple-broadcast', function () {
        Log::info('🧪 Testing simple broadcast - Event only');

        try {
            // Test dengan Event yang sudah ada dan terbukti
            $ticket = Tickets::find(29);
            if (!$ticket) {
                return response()->json(['error' => 'Ticket not found', 404]);
            }

            // Dispatch event
            $event = new \App\Events\TicketCreated($ticket);
            broadcast($event);

            Log::info('✅ TicketCreated event dispatched successfully', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event broadcast test completed',
                'ticket' => [
                    'id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'title' => $ticket->title ?? $ticket->title_ticket,
                    'status' => $ticket->status,
                    'priority' => $ticket->priority,
                ],
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Event broadcast test failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    });

    // Test notification dashboard
    Route::get('/test-notifications-dashboard', function () {
        return view('test-notifications-dashboard');
    })->name('test.notifications.dashboard');

    Route::get('/test-pusher', function () {
        $user = \App\Models\User::first();
        $ticket = \App\Models\Tickets::first();

        if ($user && $ticket) {
            // Test database notification dulu
            $user->notify(new \App\Notifications\TicketCreated($ticket));

            // Cek apakah tersimpan di database
            $notification = $user->notifications()->latest()->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Database notification sent',
                'user' => $user->name,
                'ticket' => $ticket->ticket_number,
                'notification_saved' => $notification ? true : false,
                'notification_data' => $notification ? $notification->data : null
            ]);
        }

        return 'No user or ticket found';
    });
    Route::get('/notification-test', function () {
        return view('test-notification');
    });

    // API routes for notifications
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'apiIndex'])->name('notifications.index');
        Route::post('/test-notification', [NotificationController::class, 'apiTestNotification'])->name('notifications.test');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    });
});