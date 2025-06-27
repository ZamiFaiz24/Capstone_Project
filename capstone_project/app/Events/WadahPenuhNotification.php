<?php

namespace App\Events;

use App\Models\Models\Data\LogNotifikasi;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;

class WadahPenuhNotification implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $log;

    /**
     * Create a new event instance.
     */
    public function __construct(LogNotifikasi $log)
    {
        $this->log = $log;
    }

    /**
     * The name of the channel the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('log-wadah');
    }

    /**
     * The name of the event for frontend listener.
     */
    public function broadcastAs(): string
    {
        return 'WadahPenuhNotification';
    }
}
