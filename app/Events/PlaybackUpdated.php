<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlaybackUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Room $room;

    public string $action;

    public float $position;

    /**
     * Create a new event instance.
     */
    public function __construct(Room $room, string $action, float $position)
    {
        $this->room = $room;
        $this->action = $action;
        $this->position = $position;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('room.'.$this->room->uuid),
        ];
    }
}
