<?php

namespace App\Http\Controllers;

use App\Events\ParticipantJoined;
use App\Events\ParticipantMuted;
use App\Models\Participant;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ParticipantController extends Controller
{
    public function join(Request $request, string $uuid)
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'min:2', 'max:50'],
        ]);

        $room = Room::where('uuid', $uuid)->firstOrFail();

        if ($room->status === 'ended') {
            return redirect()->route('rooms.show', $room->uuid);
        }

        $participant = Participant::create([
            'room_id' => $room->id,
            'user_id' => null,
            'display_name' => $validated['display_name'],
            'status' => 'joined',
            'is_muted' => false,
            'joined_at' => now(),
        ]);

        session([
            'participant_id' => $participant->id,
        ]);

        $participant->load('room');

        event(new ParticipantJoined($participant));

        return redirect()->route('rooms.show', $room->uuid);
    }

    public function mute(string $uuid, int $participantId)
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
            abort(403, 'Only the host can mute participants.');
        }

        $host = User::findOrFail($hostParticipant->user_id);

        Gate::forUser($host)->authorize('controlPlayback', $room);

        $participant = Participant::where('id', $participantId)
            ->where('room_id', $room->id)
            ->where('status', 'joined')
            ->firstOrFail();

        // Don't allow the host to mute themselves.
        if ($participant->id === $hostParticipant->id) {
            abort(403, 'Host cannot mute themselves.');
        }

        $participant->is_muted = ! $participant->is_muted;
        $participant->save();

        $participant->load('room');

        event(new ParticipantMuted($participant));

        return response()->json([
            'message' => $participant->is_muted
                ? 'Participant muted'
                : 'Participant unmuted',
            'is_muted' => $participant->is_muted,
        ]);
    }
}
