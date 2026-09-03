<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function controlPlayback(User $user, Room $room): bool
    {
        return $user->id === $room->host_id;
    }
}
