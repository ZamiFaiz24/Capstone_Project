<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Models\Data\LogNotifikasi;

class LogPerangkatUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $log;

    public function __construct(LogNotifikasi $log)
    {
        $this->log = $log;
    }

    public function broadcastOn(): \Illuminate\Broadcasting\Channel
    {
        return new Channel('log-perangkat');
    }
}
