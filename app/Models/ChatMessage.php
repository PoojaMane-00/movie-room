<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'room_id',
        'participant_id',
        'message',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
