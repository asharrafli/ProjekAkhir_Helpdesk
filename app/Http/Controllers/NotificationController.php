<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc') // Secondary sort untuk tie-breaking
            ->limit($perPage)
            ->get();

        $unreadCount = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'total' => Auth::user()->notifications()->count()
        ]);
    }

    public function markAsRead(Request $request)
    {
        $notification = Auth::user()->notifications()->find($request->id);
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false]);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if ($notification) {
            $notification->delete();
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function apiIndex()
    {
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->take(50)
            ->get();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'count' => $notifications->count()
        ]);
    }

    public function apiTestNotification()
    {
        try {
            $user = Auth::user();
            
            // Create a test notification directly in database
            $user->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => 'App\Notifications\TestNotification',
                'data' => [
                    'title' => 'Test Notification',
                    'message' => 'This is a test notification sent at ' . now()->format('Y-m-d H:i:s'),
                    'type' => 'success'
                ],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test notification created successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create test notification: ' . $e->getMessage()
            ], 500);
        }
    }

    public function unreadCount()
    {
        $count = Auth::user()->unreadNotifications()->count();
        return response()->json(['count' => $count]);
    }
}