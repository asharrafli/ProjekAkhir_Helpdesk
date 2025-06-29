<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $testData;

    /**
     * Create a new event instance.
     */
    public function __construct($testData)
    {
        $this->testData = $testData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('test-channel');
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'test-event';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return $this->testData;
    }
}