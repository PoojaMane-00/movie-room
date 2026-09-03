<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionEnded implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Room $room
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('room.'.$this->room->uuid),
        ];
    }
}
