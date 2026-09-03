<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $messageId,
        public string $roomUuid
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('room.'.$this->roomUuid),
        ];
    }
}
