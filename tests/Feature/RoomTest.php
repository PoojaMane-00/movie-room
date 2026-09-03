<?php

namespace Tests\Feature;

use App\Events\ParticipantJoined;
use App\Events\PlaybackUpdated;
use App\Events\SessionEnded;
use App\Models\Participant;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to create a room with an associated host user and host participant.
     */
    private function createRoomWithHost(string $status = 'waiting'): array
    {
        $host = User::create([
            'name' => 'Host User',
            'email' => 'host@example.com',
            'password' => bcrypt('password'),
        ]);

        $room = Room::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Movie Room',
            'video_url' => 'https://example.com/movie.mp4',
            'host_id' => $host->id,
        ]);

        if ($status !== 'waiting') {
            $room->status = $status;
            $room->save();
        }

        $hostParticipant = Participant::create([
            'room_id' => $room->id,
            'user_id' => $host->id,
            'display_name' => 'Host User',
            'status' => 'joined',
            'is_muted' => false,
            'joined_at' => now(),
        ]);

        return [$room, $host, $hostParticipant];
    }

    /**
     * Test 1: Host can control playback.
     */
    public function test_host_can_control_playback(): void
    {
        Event::fake([PlaybackUpdated::class]);

        [$room, $host, $hostParticipant] = $this->createRoomWithHost();

        $response = $this->withSession(['participant_id' => $hostParticipant->id])
            ->postJson("/rooms/{$room->uuid}/playback", [
                'action' => 'play',
                'position' => 15.5,
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Playback updated',
                'action' => 'play',
                'position' => 15.5,
            ]);

        Event::assertDispatched(PlaybackUpdated::class, function (PlaybackUpdated $event) use ($room) {
            return $event->room->id === $room->id
                && $event->action === 'play'
                && (float) $event->position === 15.5;
        });
    }

    /**
     * Test 2: Non-host cannot control playback.
     */
    public function test_non_host_cannot_control_playback(): void
    {
        Event::fake([PlaybackUpdated::class]);

        [$room, $host, $hostParticipant] = $this->createRoomWithHost();

        $guestParticipant = Participant::create([
            'room_id' => $room->id,
            'user_id' => null,
            'display_name' => 'Guest User',
            'status' => 'joined',
            'is_muted' => false,
            'joined_at' => now(),
        ]);

        $response = $this->withSession(['participant_id' => $guestParticipant->id])
            ->postJson("/rooms/{$room->uuid}/playback", [
                'action' => 'pause',
                'position' => 10.0,
            ]);

        $response->assertForbidden();

        Event::assertNotDispatched(PlaybackUpdated::class);
    }

    /**
     * Test 3: Ended room rejects playback.
     */
    public function test_ended_room_rejects_playback(): void
    {
        Event::fake([PlaybackUpdated::class]);

        [$room, $host, $hostParticipant] = $this->createRoomWithHost('ended');

        $response = $this->withSession(['participant_id' => $hostParticipant->id])
            ->postJson("/rooms/{$room->uuid}/playback", [
                'action' => 'play',
                'position' => 0,
            ]);

        $response->assertForbidden();

        Event::assertNotDispatched(PlaybackUpdated::class);
    }

    /**
     * Test 4: Host can end the session.
     */
    public function test_host_can_end_the_session(): void
    {
        Event::fake([SessionEnded::class]);

        [$room, $host, $hostParticipant] = $this->createRoomWithHost();

        $response = $this->withSession(['participant_id' => $hostParticipant->id])
            ->postJson("/rooms/{$room->uuid}/end");

        $response->assertOk()
            ->assertJson([
                'message' => 'Session ended',
                'status' => 'ended',
            ]);

        $room->refresh();
        $this->assertSame('ended', $room->status);
        $this->assertNotNull($room->ended_at);
        $this->assertFalse((bool) $room->chat_enabled);

        Event::assertDispatched(SessionEnded::class, function (SessionEnded $event) use ($room) {
            return $event->room->id === $room->id;
        });
    }

    /**
     * Test 5: Non-host cannot end the session.
     */
    public function test_non_host_cannot_end_the_session(): void
    {
        Event::fake([SessionEnded::class]);

        [$room, $host, $hostParticipant] = $this->createRoomWithHost();

        $guestParticipant = Participant::create([
            'room_id' => $room->id,
            'user_id' => null,
            'display_name' => 'Guest User',
            'status' => 'joined',
            'is_muted' => false,
            'joined_at' => now(),
        ]);

        $response = $this->withSession(['participant_id' => $guestParticipant->id])
            ->postJson("/rooms/{$room->uuid}/end");

        $response->assertForbidden();

        $room->refresh();
        $this->assertNotSame('ended', $room->status);

        Event::assertNotDispatched(SessionEnded::class);
    }

    /**
     * Test 6: A participant cannot join an ended room.
     */
    public function test_participant_cannot_join_an_ended_room(): void
    {
        [$room, $host, $hostParticipant] = $this->createRoomWithHost('ended');

        $initialParticipantCount = Participant::where('room_id', $room->id)->count();

        $response = $this->post("/rooms/{$room->uuid}/join", [
            'display_name' => 'New Participant',
        ]);

        $response->assertRedirect(route('rooms.show', $room->uuid));

        $this->assertDatabaseMissing('participants', [
            'room_id' => $room->id,
            'display_name' => 'New Participant',
        ]);

        $this->assertSame(
            $initialParticipantCount,
            Participant::where('room_id', $room->id)->count()
        );
    }

    /**
     * Test 7: ParticipantJoined event is dispatched when participant joins.
     */
    public function test_participant_joined_event_is_dispatched_on_join(): void
    {
        Event::fake([ParticipantJoined::class]);

        [$room, $host, $hostParticipant] = $this->createRoomWithHost();

        $response = $this->post("/rooms/{$room->uuid}/join", [
            'display_name' => 'Alice Watcher',
        ]);

        $response->assertRedirect(route('rooms.show', $room->uuid));

        $this->assertDatabaseHas('participants', [
            'room_id' => $room->id,
            'display_name' => 'Alice Watcher',
            'status' => 'joined',
        ]);

        Event::assertDispatched(ParticipantJoined::class, function (ParticipantJoined $event) use ($room) {
            return $event->participant->room_id === $room->id
                && $event->participant->display_name === 'Alice Watcher';
        });
    }

    /**
     * Test 8: Can get playback status.
     */
    public function test_can_get_playback_status(): void
    {
        [$room, $host, $hostParticipant] = $this->createRoomWithHost();

        $this->withSession(['participant_id' => $hostParticipant->id])
            ->postJson("/rooms/{$room->uuid}/playback", [
                'action' => 'play',
                'position' => 30.0,
            ]);

        $response = $this->getJson("/rooms/{$room->uuid}/playback");

        $response->assertOk()
            ->assertJsonStructure([
                'action',
                'position',
                'current_position',
            ]);

        $this->assertSame('play', $response->json('action'));
        $this->assertEquals(30.0, $response->json('position'));
        $this->assertGreaterThanOrEqual(30.0, $response->json('current_position'));
    }

    /**
     * Test 9: Host can send playback sync action.
     */
    public function test_host_can_send_playback_sync(): void
    {
        Event::fake([PlaybackUpdated::class]);

        [$room, $host, $hostParticipant] = $this->createRoomWithHost();

        $response = $this->withSession(['participant_id' => $hostParticipant->id])
            ->postJson("/rooms/{$room->uuid}/playback", [
                'action' => 'sync',
                'position' => 45.0,
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Playback updated',
                'action' => 'sync',
                'position' => 45.0,
            ]);

        Event::assertDispatched(PlaybackUpdated::class, function (PlaybackUpdated $event) {
            return $event->action === 'sync' && (float) $event->position === 45.0;
        });
    }
}
