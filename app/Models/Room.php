<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'video_url',
        'host_id',
    ];

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
