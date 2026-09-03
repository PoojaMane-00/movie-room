<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageDeleted;
use App\Events\ChatMessageSent;
use App\Events\ChatStatusUpdated;
use App\Models\ChatMessage;
use App\Models\Participant;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChatController extends Controller
{
    public function send(Request $request, string $uuid)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        $room = Room::where('uuid', $uuid)->firstOrFail();

        if ($room->status === 'ended' || ! $room->chat_enabled) {
            abort(403, 'Chat is disabled.');
        }

        $participant = Participant::where(
            'id',
            session('participant_id')
        )
            ->where('room_id', $room->id)
            ->where('status', 'joined')
            ->first();

        if (! $participant) {
            abort(403, 'You are not a participant in this room.');
        }

        if ($participant->is_muted) {
            abort(403, 'You are muted.');
        }

        $chatMessage = ChatMessage::create([
            'room_id' => $room->id,
            'participant_id' => $participant->id,
            'message' => $validated['message'],
        ]);

        $chatMessage->load('participant', 'room');

        event(new ChatMessageSent($chatMessage));

        return response()->json([
            'message' => 'Message sent',
        ]);
    }

    public function delete(string $uuid, int $messageId)
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        $hostParticipant = Participant::where('id', session('participant_id'))
            ->where('room_id', $room->id)
            ->where('status', 'joined')
            ->first();

        if (! $hostParticipant || ! $hostParticipant->user_id) {
            abort(403, 'Only the host can delete messages.');
        }

        $host = User::findOrFail($hostParticipant->user_id);

        Gate::forUser($host)->authorize('controlPlayback', $room);

        $chatMessage = ChatMessage::where('id', $messageId)
            ->where('room_id', $room->id)
            ->firstOrFail();

        $chatMessage->delete();

        event(new ChatMessageDeleted(
            $messageId,
            $room->uuid
        ));

        return response()->json([
            'message' => 'Message deleted',
        ]);
    }

    public function toggle(string $uuid)
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        if ($room->status === 'ended') {
            abort(403, 'This room has ended.');
        }

        $hostParticipant = Participant::where('id', session('participant_id'))
            ->where('room_id', $room->id)
            ->where('status', 'joined')
            ->first();

        if (! $hostParticipant || ! $hostParticipant->user_id) {
            abort(403, 'Only the host can toggle chat.');
        }

        $host = User::findOrFail($hostParticipant->user_id);

        Gate::forUser($host)->authorize('controlPlayback', $room);

        $room->chat_enabled = ! $room->chat_enabled;
        $room->save();

        event(new ChatStatusUpdated(
            $room->uuid,
            $room->chat_enabled
        ));

        return response()->json([
            'message' => 'Chat toggled',
            'chat_enabled' => $room->chat_enabled,
        ]);
    }
}
