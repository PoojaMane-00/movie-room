<?php

namespace Tests\Feature;

use App\Events\ChatMessageDeleted;
use App\Events\ChatMessageSent;
use App\Events\ChatStatusUpdated;
use App\Models\ChatMessage;
use App\Models\Participant;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to create a room and host.
     */
    private function createRoom(array $roomAttributes = []): Room
    {
        $host = User::create([
            'name' => 'Host User',
            'email' => 'host-'.Str::uuid().'@example.com',
            'password' => bcrypt('password'),
        ]);

        $room = Room::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Chat Room',
            'video_url' => 'https://example.com/movie.mp4',
            'host_id' => $host->id,
        ]);

        if (isset($roomAttributes['status'])) {
            $room->status = $roomAttributes['status'];
        }

        if (isset($roomAttributes['chat_enabled'])) {
            $room->chat_enabled = $roomAttributes['chat_enabled'];
        }

        $room->save();

        Participant::create([
            'room_id' => $room->id,
            'user_id' => $host->id,
            'display_name' => 'Host User',
            'status' => 'joined',
            'is_muted' => false,
            'joined_at' => now(),
        ]);

        return $room;
    }

    /**
     * Helper to create a participant in a room.
     */
    private function createParticipant(Room $room, array $attributes = []): Participant
    {
        return Participant::create(array_merge([
            'room_id' => $room->id,
            'user_id' => null,
            'display_name' => 'Test Participant',
            'status' => 'joined',
            'is_muted' => false,
            'joined_at' => now(),
        ], $attributes));
    }

    /**
     * Test 1: Participant can send a message.
     */
    public function test_participant_can_send_a_message(): void
    {
        Event::fake([ChatMessageSent::class]);

        $room = $this->createRoom();
        $participant = $this->createParticipant($room);

        $response = $this->withSession(['participant_id' => $participant->id])
            ->postJson("/rooms/{$room->uuid}/chat", [
                'message' => 'Hello, movie night!',
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Message sent',
            ]);

        $this->assertDatabaseHas('chat_messages', [
            'room_id' => $room->id,
            'participant_id' => $participant->id,
            'message' => 'Hello, movie night!',
        ]);

        Event::assertDispatched(ChatMessageSent::class, function (ChatMessageSent $event) use ($room, $participant) {
            return $event->chatMessage->room_id === $room->id
                && $event->chatMessage->participant_id === $participant->id
                && $event->chatMessage->message === 'Hello, movie night!';
        });
    }

    /**
     * Test 2: Non-participant cannot send a message.
     */
    public function test_non_participant_cannot_send_a_message(): void
    {
        Event::fake([ChatMessageSent::class]);

        $room = $this->createRoom();

        // No participant_id set in session
        $response = $this->postJson("/rooms/{$room->uuid}/chat", [
            'message' => 'Sneaking into chat',
        ]);

        $response->assertForbidden();

        // Participant from another room
        $otherRoom = $this->createRoom();
        $otherParticipant = $this->createParticipant($otherRoom);

        $responseWithOtherSession = $this->withSession(['participant_id' => $otherParticipant->id])
            ->postJson("/rooms/{$room->uuid}/chat", [
                'message' => 'Cross-room message',
            ]);

        $responseWithOtherSession->assertForbidden();

        $this->assertDatabaseCount('chat_messages', 0);
        Event::assertNotDispatched(ChatMessageSent::class);
    }

    /**
     * Test 3: Muted participant cannot send messages.
     */
    public function test_muted_participant_cannot_send_messages(): void
    {
        Event::fake([ChatMessageSent::class]);

        $room = $this->createRoom();
        $mutedParticipant = $this->createParticipant($room, ['is_muted' => true]);

        $response = $this->withSession(['participant_id' => $mutedParticipant->id])
            ->postJson("/rooms/{$room->uuid}/chat", [
                'message' => 'Can anyone hear me?',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseCount('chat_messages', 0);
        Event::assertNotDispatched(ChatMessageSent::class);
    }

    /**
     * Test 4: Disabled chat cannot receive messages.
     */
    public function test_disabled_chat_cannot_receive_messages(): void
    {
        Event::fake([ChatMessageSent::class]);

        $room = $this->createRoom(['chat_enabled' => false]);
        $participant = $this->createParticipant($room);

        $response = $this->withSession(['participant_id' => $participant->id])
            ->postJson("/rooms/{$room->uuid}/chat", [
                'message' => 'Chat is supposed to be off',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseCount('chat_messages', 0);
        Event::assertNotDispatched(ChatMessageSent::class);
    }

    /**
     * Test 5: Ended room cannot receive messages.
     */
    public function test_ended_room_cannot_receive_messages(): void
    {
        Event::fake([ChatMessageSent::class]);

        $room = $this->createRoom(['status' => 'ended']);
        $participant = $this->createParticipant($room);

        $response = $this->withSession(['participant_id' => $participant->id])
            ->postJson("/rooms/{$room->uuid}/chat", [
                'message' => 'Message after session ended',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseCount('chat_messages', 0);
        Event::assertNotDispatched(ChatMessageSent::class);
    }

    /**
     * Test 6: Empty/invalid message is rejected.
     */
    public function test_empty_message_is_rejected(): void
    {
        $room = $this->createRoom();
        $participant = $this->createParticipant($room);

        $response = $this->withSession(['participant_id' => $participant->id])
            ->postJson("/rooms/{$room->uuid}/chat", [
                'message' => '',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);

        $this->assertDatabaseCount('chat_messages', 0);
    }

    /**
     * Test 7: Message over 500 characters is rejected.
     */
    public function test_message_over_500_characters_is_rejected(): void
    {
        $room = $this->createRoom();
        $participant = $this->createParticipant($room);

        $response = $this->withSession(['participant_id' => $participant->id])
            ->postJson("/rooms/{$room->uuid}/chat", [
                'message' => str_repeat('a', 501),
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);

        $this->assertDatabaseCount('chat_messages', 0);
    }

    /**
     * Test 8: Rate limit returns 429 after exceeding threshold.
     */
    public function test_rate_limit_returns_429(): void
    {
        $room = $this->createRoom();
        $participant = $this->createParticipant($room);

        // Allowed 10 requests per minute
        for ($i = 0; $i < 10; $i++) {
            $response = $this->withSession(['participant_id' => $participant->id])
                ->postJson("/rooms/{$room->uuid}/chat", [
                    'message' => "Message number {$i}",
                ]);

            $response->assertOk();
        }

        // 11th request exceeds throttle
        $throttledResponse = $this->withSession(['participant_id' => $participant->id])
            ->postJson("/rooms/{$room->uuid}/chat", [
                'message' => 'One too many messages',
            ]);

        $throttledResponse->assertStatus(429);
    }

    /**
     * Test 9: Host can toggle chat.
     */
    public function test_host_can_toggle_chat(): void
    {
        Event::fake([ChatStatusUpdated::class]);

        $room = $this->createRoom(['chat_enabled' => true]);
        $hostParticipant = Participant::where('room_id', $room->id)->whereNotNull('user_id')->first();

        $response = $this->withSession(['participant_id' => $hostParticipant->id])
            ->postJson("/rooms/{$room->uuid}/chat/toggle");

        $response->assertOk()
            ->assertJson([
                'message' => 'Chat toggled',
                'chat_enabled' => false,
            ]);

        $room->refresh();
        $this->assertFalse((bool) $room->chat_enabled);

        Event::assertDispatched(ChatStatusUpdated::class, function (ChatStatusUpdated $event) use ($room) {
            return $event->roomUuid === $room->uuid && $event->chatEnabled === false;
        });
    }

    /**
     * Test 10: Non-host cannot toggle chat.
     */
    public function test_non_host_cannot_toggle_chat(): void
    {
        Event::fake([ChatStatusUpdated::class]);

        $room = $this->createRoom(['chat_enabled' => true]);
        $guestParticipant = $this->createParticipant($room);

        $response = $this->withSession(['participant_id' => $guestParticipant->id])
            ->postJson("/rooms/{$room->uuid}/chat/toggle");

        $response->assertForbidden();

        $room->refresh();
        $this->assertTrue((bool) $room->chat_enabled);

        Event::assertNotDispatched(ChatStatusUpdated::class);
    }

    /**
     * Test 11: Host can delete a message.
     */
    public function test_host_can_delete_a_message(): void
    {
        Event::fake([ChatMessageDeleted::class]);

        $room = $this->createRoom();
        $hostParticipant = Participant::where('room_id', $room->id)->whereNotNull('user_id')->first();
        $guestParticipant = $this->createParticipant($room);

        $chatMessage = ChatMessage::create([
            'room_id' => $room->id,
            'participant_id' => $guestParticipant->id,
            'message' => 'Inappropriate message',
        ]);

        $response = $this->withSession(['participant_id' => $hostParticipant->id])
            ->deleteJson("/rooms/{$room->uuid}/chat/{$chatMessage->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Message deleted',
            ]);

        $this->assertDatabaseMissing('chat_messages', [
            'id' => $chatMessage->id,
        ]);

        Event::assertDispatched(ChatMessageDeleted::class, function (ChatMessageDeleted $event) use ($chatMessage, $room) {
            return $event->messageId === $chatMessage->id
                && $event->roomUuid === $room->uuid;
        });
    }

    /**
     * Test 12: Non-host cannot delete a message.
     */
    public function test_non_host_cannot_delete_a_message(): void
    {
        Event::fake([ChatMessageDeleted::class]);

        $room = $this->createRoom();
        $guestParticipant = $this->createParticipant($room);

        $chatMessage = ChatMessage::create([
            'room_id' => $room->id,
            'participant_id' => $guestParticipant->id,
            'message' => 'Regular message',
        ]);

        $response = $this->withSession(['participant_id' => $guestParticipant->id])
            ->deleteJson("/rooms/{$room->uuid}/chat/{$chatMessage->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('chat_messages', [
            'id' => $chatMessage->id,
        ]);

        Event::assertNotDispatched(ChatMessageDeleted::class);
    }
}
