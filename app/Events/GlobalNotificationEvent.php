<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GlobalNotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    /**
     * Create a new event instance.
     */
    public function __construct(array $notification)
    {
        $this->notification = $notification;

        Log::info('🌍 GlobalNotificationEvent created', [
            'notification_id' => $notification['id'],
            'title' => $notification['title'],
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return [
            new Channel('notifications.global'), // ✅ Public channel - semua user
            new Channel('test-notifications'),   // ✅ Test channel
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'notification.global';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        $data = [
            'id' => $this->notification['id'],
            'title' => $this->notification['title'],
            'message' => $this->notification['message'],
            'type' => $this->notification['type'],
            'created_at' => $this->notification['created_at'],
            'user' => $this->notification['user'] ?? null,
            'timestamp' => now()->toISOString(),
        ];

        Log::info('📡 Broadcasting global notification', $data);

        return $data;
    }
}
