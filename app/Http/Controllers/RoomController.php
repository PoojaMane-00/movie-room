<?php

namespace App\Http\Controllers;

use App\Events\PlaybackUpdated;
use App\Events\SessionEnded;
use App\Models\Participant;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class RoomController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'video_url' => ['required', 'url', 'max:2048'],
        ]);

        $host = User::firstOrFail();

        [$room, $hostParticipant] = DB::transaction(function () use ($validated, $host) {

            $room = Room::create([
                'uuid' => (string) Str::uuid(),
                'name' => $validated['name'],
                'video_url' => $validated['video_url'],
                'host_id' => $host->id,
                'status' => 'waiting',
                'chat_enabled' => true,
            ]);

            $hostParticipant = Participant::create([
                'room_id' => $room->id,
                'user_id' => $host->id,
                'display_name' => $host->name,
                'status' => 'joined',
                'is_muted' => false,
                'joined_at' => now(),
            ]);

            return [$room, $hostParticipant];
        });

        session([
            'participant_id' => $hostParticipant->id,
        ]);

        return redirect()->route('rooms.show', $room->uuid);
    }

    public function show(string $uuid)
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        if ($room->status === 'ended') {
            return view('rooms.ended', compact('room'));
        }
        // print_r($room);die;
        if ($room->status === 'ended') {
            dd('ENDED', $room->status, $room->id);
        }

        $participantId = session('participant_id');

        if (! $participantId) {
            return view('rooms.join', compact('room'));
        }

        $participant = $room->participants()
            ->where('id', $participantId)
            ->where('status', 'joined')
            ->first();

        if (! $participant) {
            return view('rooms.join', compact('room'));
        }

        $participants = $room->participants()
            ->where('status', 'joined')
            ->get();

        $chatMessages = $room->chatMessages()
            ->with('participant')
            ->latest()
            ->get();

        return view('rooms.show', compact('room', 'participants', 'participant', 'chatMessages'));
    }

    public function getPlayback(string $uuid)
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        if ($room->status === 'ended') {
            abort(403, 'This room has ended.');
        }

        $playback = Cache::get("room:{$room->uuid}:playback", [
            'action' => 'pause',
            'position' => 0.0,
            'updated_at' => microtime(true),
        ]);

        $currentPosition = (float) $playback['position'];
        if ($playback['action'] === 'play') {
            $elapsed = microtime(true) - (float) $playback['updated_at'];
            $currentPosition += max(0, $elapsed);
        }

        return response()->json([
            'action' => $playback['action'],
            'position' => (float) $playback['position'],
            'current_position' => $currentPosition,
        ]);
    }

    public function playback(Request $request, string $uuid)
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        if ($room->status === 'ended') {
            abort(403, 'This room has ended.');
        }

        $participant = Participant::where('id', session('participant_id'))
            ->where('room_id', $room->id)
            ->where('status', 'joined')
            ->first();

        if (! $participant || ! $participant->user_id) {
            abort(403, 'Only the host can control playback.');
        }

        $user = User::findOrFail($participant->user_id);

        Gate::forUser($user)->authorize('controlPlayback', $room);

        $validated = $request->validate([
            'action' => ['required', 'in:play,pause,stop,sync'],
            'position' => ['required', 'numeric', 'min:0'],
        ]);

        Cache::put("room:{$room->uuid}:playback", [
            'action' => $validated['action'],
            'position' => (float) $validated['position'],
            'updated_at' => microtime(true),
        ], now()->addHours(6));

        event(new PlaybackUpdated(
            $room,
            $validated['action'],
            $validated['position']
        ));

        return response()->json([
            'message' => 'Playback updated',
            'action' => $validated['action'],
            'position' => $validated['position'],
        ]);
    }

    public function endSession(string $uuid)
    {
        $room = Room::where('uuid', $uuid)->firstOrFail();

        $participant = Participant::where('id', session('participant_id'))
            ->where('room_id', $room->id)
            ->where('status', 'joined')
            ->first();

        if (! $participant || ! $participant->user_id) {
            abort(403, 'Only the host can end the session.');
        }

        $user = User::findOrFail($participant->user_id);

        Gate::forUser($user)->authorize('controlPlayback', $room);

        $room->status = 'ended';
        $room->ended_at = now();
        $room->chat_enabled = false;
        $room->save();

        Cache::put("room:{$room->uuid}:playback", [
            'action' => 'stop',
            'position' => 0.0,
            'updated_at' => microtime(true),
        ], now()->addHours(6));

        event(new SessionEnded($room));

        return response()->json([
            'message' => 'Session ended',
            'status' => $room->status,
        ]);
    }
}
