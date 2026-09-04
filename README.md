# Movie Room

A Laravel-based real-time movie room application where a host can create a room, share a unique link, control video playback, and communicate with participants through real-time chat.

## Features

* Create a movie room with:

  * Room name
  * Publicly accessible video URL
* Generate a unique UUID-based room link
* Allow participants to join using the shared link
* Participants enter a display name before joining
* Host-only playback controls:

  * Play
  * Pause
  * Stop
  * End session
* Real-time playback synchronization using Laravel Reverb and Laravel Echo
* Real-time room chat
* Display sender name and message timestamp
* Host moderation:

  * Mute/unmute participants
  * Delete chat messages
  * Enable/disable chat
* Prevent muted participants from sending messages
* Prevent access and chat activity after a room has ended
* Server-side authorization for host-only actions
* Message validation and rate limiting
* XSS-safe message rendering
* Automated feature tests

## Tech Stack

* PHP 8.3+
* Laravel 13
* MySQL
* Laravel Reverb
* Laravel Echo
* Pusher JS
* Blade
* Tailwind CSS
* Vite
* PHPUnit

## Requirements

Make sure the following are installed:

* PHP 8.3+
* Composer
* MySQL 8+
* Node.js and npm

## Installation

Clone the repository and install the dependencies:

```bash
composer install
npm install
```

Create the environment file:

```bash
cp .env.example .env
```

On Windows, you can copy `.env.example` to `.env` manually if `cp` is unavailable.

Generate the application key:

```bash
php artisan key:generate
```

Create a MySQL database named `movie_db`.

Configure the MySQL database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=movie_db
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seed the demo host:

```bash
php artisan migrate --seed
```

Build the frontend assets:

```bash
npm run build
```

## Reverb Configuration

Configure Laravel Reverb in `.env`:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Start the Reverb WebSocket server:

```bash
php artisan reverb:start
```

Start the Laravel application:

```bash
php artisan serve
```

For frontend development, start Vite:

```bash
npm run dev
```

The application will be available at:

```text
http://localhost:8000
```

## Demo Host

The assessment version uses a seeded Laravel user as the demo host.

```text
Name: Pooja
Email: pooja@example.com
Password: password
```

Participants do not require an account. They join using the room link and provide a display name.

> Authentication is intentionally kept minimal for this assessment. A production implementation would use Laravel authentication for host identity.

## Usage

### Host

1. Open `/rooms/create`
2. Enter a room name
3. Enter a publicly accessible video URL
4. Create the room
5. Copy the generated room URL from the browser address bar and share it with participants
6. Use the host controls to control playback
7. Moderate participants and chat when required
8. End the session when finished

### Participant

1. Open the shared room URL
2. Enter a display name
3. Join the room
4. Start watching the video
5. Watch the synchronized video
6. Participate in the room chat

Participants do not receive playback controls.

## Architecture

The application uses a simple Laravel MVC structure with Eloquent relationships and event-based broadcasting.

```text
Browser
  │
  ├── HTTP ──> Laravel Controllers ──> MySQL
  │
  └── WebSocket ──> Laravel Reverb ──> Laravel Echo
```

### Main Models

```text
Room
 ├── belongsTo User (host)
 ├── hasMany Participants
 └── hasMany ChatMessages

Participant
 ├── belongsTo Room
 ├── belongsTo User (optional)
 └── hasMany ChatMessages

ChatMessage
 ├── belongsTo Room
 └── belongsTo Participant
```

### Database Tables

#### `rooms`

Stores room information and session state.

```text
id
uuid
name
video_url
host_id
status
chat_enabled
started_at
ended_at
created_at
updated_at
```

Room status:

```text
waiting
active
ended
```

#### `participants`

Stores users participating in a room.

```text
id
room_id
user_id
display_name
status
is_muted
joined_at
left_at
created_at
updated_at
```

#### `chat_messages`

Stores messages belonging to a room participant.

```text
id
room_id
participant_id
message
created_at
updated_at
```

## Real-Time Communication

Laravel Reverb provides the WebSocket server, while Laravel Echo listens for broadcast events in the browser.

Playback and chat use separate events so different types of real-time activity remain clearly separated.

Examples include:

```text
PlaybackUpdated
ChatMessageSent
ChatMessageDeleted
ParticipantMuted
ChatStatusUpdated
SessionEnded
```

Playback events contain the action and video position so participants can update their local video state.

The application uses `ShouldBroadcastNow` for these real-time events so they are broadcast immediately without requiring a queue worker.

## Authorization

Host-only operations are protected on the server side using Laravel authorization.

The following operations require host authorization:

* Playback control
* Ending a session
* Muting/unmuting participants
* Deleting chat messages
* Enabling/disabling chat

Frontend controls are only a usability layer; authorization is enforced by the backend.

## Security Considerations

* UUIDs are used for shareable room URLs to avoid predictable sequential room identifiers.
* UUIDs provide URL obscurity but are not treated as an authorization mechanism.
* Server-side authorization prevents participants from performing host-only actions.
* Chat messages are validated before storage.
* Messages are rendered as text rather than HTML to prevent XSS through message content.
* Chat sending is rate-limited to 10 requests per minute.
* Ended rooms reject further participation and chat activity.
* Muted participants cannot send chat messages.

## Chat Lifecycle

Chat messages are associated with their room/session.

When a room is ended, chat activity is disabled and participants are redirected to the ended-room screen.

For this assessment, chat is session-scoped and is no longer active after the room ends.

## Testing

The project includes PHPUnit feature tests covering the main room, playback, session, chat, validation, authorization, moderation, and rate-limiting scenarios.

The test suite uses a separate MySQL database named `movie_db_test` so tests do not modify the development database.

Create the test database once:

```sql
CREATE DATABASE movie_db_test;
```

Run the complete test suite:

```bash
php artisan test
```

Current test result:

```text
22 passed

86 assertions
```

Code formatting can be checked or fixed using Laravel Pint:

```bash
vendor\bin\pint
```


## Design Decisions

### Blade instead of a frontend framework

Blade was chosen to keep the implementation focused and minimal while still providing the required real-time interaction through Laravel Echo.

### Automatic participant joining

Participants are automatically joined after providing a display name. This avoids adding an unnecessary approval workflow because the assessment allows automatic joining.

### Anonymous participants

Participants do not require registered accounts. Their room participation is tracked through a participant record and session.

### Seeded host

For the assessment, a seeded Laravel user represents the host. This keeps authentication outside the scope of the core movie-room functionality.

In a production application, the host would be identified through Laravel's normal authentication system.

### Public WebSocket channel

The room uses a public broadcast channel to keep the anonymous participant flow simple. Host-only operations are still protected by server-side authorization.

For a production application, authenticated/private or presence channels could be introduced depending on the required security model.

## Scope / Future Improvements

The implementation intentionally focuses on the core movie-room functionality.

Possible production enhancements include:

* Full Laravel authentication and registration
* Private/presence WebSocket channels
* Room ownership and access policies tied to authenticated users
* Participant join/leave system messages
* Persistent/archived chat history
* More robust video-provider support
* Automated browser/end-to-end tests
* Production Reverb deployment and HTTPS/WSS configuration
* More granular room/session services as the application grows
