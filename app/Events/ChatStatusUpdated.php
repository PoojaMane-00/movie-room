<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public string $roomUuid,
        public bool $chatEnabled)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('room.'.$this->roomUuid),
        ];
    }
}
