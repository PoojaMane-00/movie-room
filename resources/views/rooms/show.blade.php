@extends('layouts.app')

@section('title', '{{ $room->name }} - CINEPHILE')

@section('content')
<!-- Main Content Area -->
<main class="flex-grow flex flex-col lg:flex-row w-full max-w-container-max mx-auto px-margin-mobile lg:px-margin-desktop py-gutter gap-gutter pt-[100px]">
    <!-- Left Column: Video Player & Controls -->
    <section class="flex-grow flex flex-col gap-base lg:w-2/3">
        <!-- Room Header Info -->
        <div class="flex items-center justify-between mb-2">
            <h1 class="font-headline-lg text-headline-lg text-on-background">{{ $room->name }}</h1>
            <div class="flex items-center gap-2 bg-surface-variant/50 px-3 py-1 rounded-full border border-white/10">
                <span class="w-2 h-2 rounded-full bg-secondary-container animate-pulse"></span>
                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase tracking-wider">Status: {{ $room->status }}</span>
            </div>
        </div>

        <!-- Cinematic Video Player Area -->
        <div class="relative w-full aspect-video rounded-xl overflow-hidden glass-panel shadow-2xl flex items-center justify-center group">
            <!-- Video Player -->
            <video
                id="videoPlayer"
                class="w-full h-full object-contain bg-black"
                src="{{ $room->video_url }}"
                @if ($participant->user_id !== $room->host_id)
                controlsList="nodownload noplaybackrate"
                @endif
                @if ($participant->user_id !== $room->host_id)
                disablePictureInPicture
                @endif>
            </video>

            <!-- Inner Gradient for Controls Legibility -->
            <div class="absolute inset-0 video-gradient pointer-events-none" style="background: linear-gradient(to top, #0A0A0A 0%, transparent 40%);"></div>

            <!-- Floating Video Controls (appear on hover) -->
            @if ($participant->user_id === $room->host_id)
            <div class="absolute bottom-0 left-0 right-0 p-6 flex flex-wrap gap-4 items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <div class="flex gap-3">
                    <button type="button" id="playButton" aria-label="Play" class="bg-primary-container text-on-primary-container w-12 h-12 rounded-full flex items-center justify-center glow-button hover:scale-105 transition-transform">
                        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                    </button>
                    <button type="button" id="pauseButton" aria-label="Pause" class="bg-surface/50 backdrop-blur-md border border-white/20 text-on-surface w-12 h-12 rounded-full flex items-center justify-center hover:bg-surface/80 hover:scale-105 transition-all">
                        <span class="material-symbols-outlined">pause</span>
                    </button>
                    <button type="button" id="stopButton" aria-label="Stop" class="bg-surface/50 backdrop-blur-md border border-white/20 text-on-surface w-12 h-12 rounded-full flex items-center justify-center hover:bg-surface/80 hover:scale-105 transition-all">
                        <span class="material-symbols-outlined">stop</span>
                    </button>
                </div>
                <div class="flex gap-3">
                    <button type="button" id="chatToggleButton" class="px-4 py-2 bg-surface/50 backdrop-blur-md border border-white/20 text-on-surface rounded-lg font-label-md flex items-center gap-2 hover:bg-surface/80 transition-all">
                        <span class="material-symbols-outlined text-[18px]">switch_account</span>
                        <span id="chatToggleText">{{ $room->chat_enabled ? 'Disable Chat' : 'Enable Chat' }}</span>
                    </button>
                    <button type="button" id="endButton" class="px-4 py-2 bg-error/20 text-error border border-error/50 rounded-lg font-label-md flex items-center gap-2 hover:bg-error/40 transition-all">
                        <span class="material-symbols-outlined text-[18px]">call_end</span>
                        End Session
                    </button>
                </div>
            </div>
            @endif

            @if ($participant->user_id !== $room->host_id)
            <button
                type="button"
                id="startWatchingButton"
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-primary-container text-on-primary-container font-label-md text-label-md px-8 py-4 rounded-lg shadow-[0_0_15px_rgba(229,9,20,0.4)] hover:shadow-[0_0_25px_rgba(229,9,20,0.6)] hover:bg-[#ff1a25] transition-all duration-300 active:scale-[0.98] flex justify-center items-center gap-2 z-20">
                <span class="material-symbols-outlined">play_arrow</span>
                Start Watching
            </button>
            @endif
        </div>
    </section>

    <!-- Right Column: Participants & Chat -->
    <aside class="flex flex-col gap-gutter lg:w-1/3">
        <!-- Participants Panel -->
        <div class="glass-panel rounded-xl p-4 flex-shrink-0">
            <h2 class="font-headline-md text-[18px] font-semibold text-on-background border-b border-white/10 pb-2 mb-3">Participants</h2>
            <ul id="participantsList" class="flex flex-col gap-3">
                @foreach ($participants as $participantItem)
                <li id="participant-row-{{ $participantItem->id }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-white/5 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-surface overflow-hidden border border-white/20">
                            <div class="w-full h-full bg-surface-variant flex items-center justify-center text-on-surface-variant font-bold">
                                {{ substr($participantItem->display_name, 0, 1) }}
                            </div>
                        </div>
                        <span class="font-label-md text-on-surface">{{ $participantItem->display_name }}</span>
                    </div>
                    @if ($participant->user_id === $room->host_id && $participantItem->user_id !== $room->host_id)
                    <button type="button" id="muteButton-{{ $participantItem->id }}" onclick="toggleMute('{{ $participantItem->id }}')" class="text-xs bg-surface-variant/50 hover:bg-surface-variant px-3 py-1 rounded transition-colors font-label-sm">
                        {{ $participantItem->is_muted ? 'Unmute' : 'Mute' }}
                    </button>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>

        <!-- Chat Panel -->
        <div class="glass-panel rounded-xl flex flex-col flex-grow overflow-hidden">
            <div class="p-4 border-b border-white/10 bg-surface/30">
                <h2 class="font-headline-md text-[18px] font-semibold text-on-background flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-container">chat</span>
                    Live Chat
                </h2>
            </div>
            <!-- Chat Messages Area -->
            <div id="chatMessages" class="flex-grow p-4 overflow-y-auto flex flex-col gap-4">
                @foreach ($chatMessages->reverse() as $chatMessage)
                <div id="chat-message-{{ $chatMessage->id }}" class="text-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <strong class="text-on-surface">{{ $chatMessage->participant->display_name }}</strong>
                            <span class="text-on-surface-variant text-xs ml-2">{{ $chatMessage->created_at->format('H:i') }}</span>
                        </div>
                        @if ($participant->user_id === $room->host_id)
                        <button type="button" onclick="deleteChatMessage('{{ $chatMessage->id }}')" class="text-error hover:text-error/80 transition-colors text-xs">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </button>
                        @endif
                    </div>
                    <p class="text-on-surface text-body-md mt-1">{{ $chatMessage->message }}</p>
                </div>
                @endforeach
            </div>
            <!-- Chat Input Area -->
            <div class="p-3 border-t border-white/10 bg-surface/50">
                <form id="chatForm" class="flex gap-2">
                    <input
                        type="text"
                        id="chatInput"
                        placeholder="{{ $room->chat_enabled ? 'Type a message...' : 'Chat has been disabled' }}"
                        maxlength="500"
                        required
                        {{ !$room->chat_enabled ? 'disabled' : '' }}
                        class="flex-grow bg-surface-container-high/50 border-none rounded-lg px-4 py-2 text-on-surface placeholder:text-on-surface-variant/50 focus:ring-1 focus:ring-primary-container focus:bg-surface-container-high transition-all font-body-md text-[14px] disabled:opacity-50 disabled:cursor-not-allowed" />
                    <button type="submit" {{ !$room->chat_enabled ? 'disabled' : '' }} class="bg-primary-container text-on-primary-container px-4 py-2 rounded-lg font-label-md hover:bg-primary-container/90 transition-all flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed">
                        Send
                        <span class="material-symbols-outlined text-[16px]">send</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>
</main>

<script>
    const video = document.getElementById('videoPlayer');
</script>

@if ($participant->user_id !== $room->host_id)
<script>
    const startWatchingButton = document.getElementById('startWatchingButton');

    startWatchingButton.addEventListener('click', async () => {
        try {
            startWatchingButton.style.display = 'none';

            // Unlock browser audio/video playback
            await video.play();

            // Fetch current host playback status to synchronize immediately
            const response = await fetch('/rooms/{{ $room->uuid }}/playback');
            if (response.ok) {
                const data = await response.json();
                console.log('Synchronizing playback with host:', data);

                if (data.action === 'play') {
                    video.currentTime = data.current_position || data.position;
                    await video.play();
                } else {
                    video.currentTime = data.position || 0;
                    video.pause();
                }
            } else {
                video.pause();
            }

            console.log('Participant playback activated and synced');
        } catch (error) {
            console.error('Unable to activate playback:', error);
        }
    });
</script>
@endif

@if ($participant->user_id === $room->host_id)
<script>
    const playButton = document.getElementById('playButton');
    const pauseButton = document.getElementById('pauseButton');
    const stopButton = document.getElementById('stopButton');
    const chatToggleButton = document.getElementById('chatToggleButton');
    const endButton = document.getElementById('endButton');

    let syncInterval = null;

    function startHostSyncInterval() {
        stopHostSyncInterval();
        syncInterval = setInterval(() => {
            if (video && !video.paused && !video.ended) {
                updatePlayback('sync');
            }
        }, 4000);
    }

    function stopHostSyncInterval() {
        if (syncInterval) {
            clearInterval(syncInterval);
            syncInterval = null;
        }
    }

    playButton.addEventListener('click', async () => {
        video.play();
        await updatePlayback('play');
        startHostSyncInterval();
    });

    pauseButton.addEventListener('click', async () => {
        video.pause();
        stopHostSyncInterval();
        await updatePlayback('pause');
    });

    stopButton.addEventListener('click', async () => {
        video.pause();
        video.currentTime = 0;
        stopHostSyncInterval();
        await updatePlayback('stop');
    });

    chatToggleButton.addEventListener('click', async () => {
        console.log('CHAT TOGGLE BUTTON CLICKED');

        const response = await fetch(
            '/rooms/{{ $room->uuid }}/chat/toggle', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }
        );

        console.log('STATUS:', response.status);

        const data = await response.json();

        console.log('CHAT TOGGLE RESPONSE:', data);

        if (!response.ok) {
            console.error(data);
            return;
        }

        const chatToggleText = document.getElementById('chatToggleText');
        if (chatToggleText) {
            chatToggleText.textContent = data.chat_enabled ?
                'Disable Chat' :
                'Enable Chat';
        }
    });

    endButton.addEventListener('click', async () => {
        console.log('END BUTTON CLICKED');
        stopHostSyncInterval();

        const response = await fetch(
            '/rooms/{{ $room->uuid }}/end', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }
        );

        console.log('STATUS:', response.status);

        const data = await response.json();

        console.log('END SESSION RESPONSE:', data);
    });

    async function updatePlayback(action) {
        const response = await fetch(
            '/rooms/{{ $room->uuid }}/playback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    action: action,
                    position: video.currentTime
                })
            }
        );

        const data = await response.json();

        console.log(data);
    }

    async function toggleMute(participantId) {
        try {
            const response = await fetch(
                `/rooms/{{ $room->uuid }}/participants/${participantId}/mute`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }
            );

            const data = await response.json();

            if (!response.ok) {
                console.error(data);
                return;
            }

            console.log(data);

            const button = document.getElementById(
                `muteButton-${participantId}`
            );

            button.textContent = data.is_muted ? 'Unmute' : 'Mute';
        } catch (error) {
            console.error('Mute error:', error);
        }
    }

    async function deleteChatMessage(messageId) {
        try {
            const response = await fetch(
                '/rooms/{{ $room->uuid }}/chat/' + messageId, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }
            );

            const data = await response.json();

            if (!response.ok) {
                console.error(data);
                return;
            }

            console.log(data);

            // Remove the message from the DOM
            const messageElement = document.getElementById('chat-message-' + messageId);
            if (messageElement) {
                messageElement.remove();
            }
        } catch (error) {
            console.error('Delete message error:', error);
        }
    }
</script>
@endif

<script>
    function setupEcho() {
        if (!window.Echo) {
            setTimeout(setupEcho, 100);
            return;
        }

        console.log('Echo loaded');

        const isHost = '{{ $participant->user_id === $room->host_id ? "true" : "false" }}' === 'true';

        window.Echo.channel('room.{{ $room->uuid }}')
            .listen('PlaybackUpdated', (event) => {
                console.log('Playback event received:', event);

                // If host: ignore broadcast because host already controls video locally without latency
                if (isHost) {
                    return;
                }

                const video = document.getElementById('videoPlayer');

                if (!video) {
                    return;
                }

                if (event.action === 'play') {
                    const drift = Math.abs(video.currentTime - event.position);
                    if (video.paused || drift > 0.5) {
                        video.currentTime = event.position;
                    }
                    video.play().catch(error => {
                        console.warn('Playback waiting for user interaction:', error);
                    });
                } else if (event.action === 'pause') {
                    video.pause();
                    video.currentTime = event.position;
                } else if (event.action === 'stop') {
                    video.pause();
                    video.currentTime = 0;
                } else if (event.action === 'sync') {
                    const drift = Math.abs(video.currentTime - event.position);
                    if (drift > 1.2) {
                        video.currentTime = event.position;
                    }
                    if (video.paused) {
                        video.play().catch(() => {});
                    }
                }
            })

            .listen('ParticipantJoined', (event) => {
                console.log('Participant joined event:', event);

                const participantsList = document.getElementById('participantsList');
                if (!participantsList || !event.participant) {
                    return;
                }

                if (document.getElementById(`participant-row-${event.participant.id}`)) {
                    return;
                }

                const li = document.createElement('li');
                li.id = `participant-row-${event.participant.id}`;
                li.className = 'flex items-center justify-between p-2 rounded-lg hover:bg-white/5 transition-colors';

                const leftDiv = document.createElement('div');
                leftDiv.className = 'flex items-center gap-3';

                const avatar = document.createElement('div');
                avatar.className = 'w-10 h-10 rounded-full bg-surface overflow-hidden border border-white/20';

                const avatarInner = document.createElement('div');
                avatarInner.className = 'w-full h-full bg-surface-variant flex items-center justify-center text-on-surface-variant font-bold';
                avatarInner.textContent = (event.participant.display_name || '').charAt(0).toUpperCase();

                avatar.appendChild(avatarInner);

                const nameSpan = document.createElement('span');
                nameSpan.className = 'font-label-md text-on-surface';
                nameSpan.textContent = event.participant.display_name;

                leftDiv.appendChild(avatar);
                leftDiv.appendChild(nameSpan);
                li.appendChild(leftDiv);

                const hostId = Number('{{ $room->host_id }}');
                if (isHost && event.participant.user_id !== hostId) {
                    const muteBtn = document.createElement('button');
                    muteBtn.type = 'button';
                    muteBtn.id = `muteButton-${event.participant.id}`;
                    muteBtn.className = 'text-xs bg-surface-variant/50 hover:bg-surface-variant px-3 py-1 rounded transition-colors font-label-sm';
                    muteBtn.textContent = event.participant.is_muted ? 'Unmute' : 'Mute';
                    muteBtn.onclick = () => toggleMute(event.participant.id);
                    li.appendChild(muteBtn);
                }

                participantsList.appendChild(li);
            })

            .listen('SessionEnded', (event) => {
                console.log('Session ended:', event);

                const video = document.getElementById('videoPlayer');

                if (video) {
                    video.pause();
                }

                alert('The host has ended the session.');

                window.location.href = '/rooms/{{ $room->uuid }}';
            })

            .listen('ChatMessageSent', (event) => {
                console.log('Chat message received:', event);

                const chatMessages = document.getElementById('chatMessages');

                if (!chatMessages) {
                    return;
                }

                const messageElement = document.createElement('div');
                messageElement.id = 'chat-message-' + event.chatMessage.id;
                messageElement.className = 'text-sm';

                const header = document.createElement('div');
                header.className = 'flex items-start justify-between gap-2';

                const sender = document.createElement('div');

                const senderName = document.createElement('strong');
                senderName.className = 'text-on-surface';
                senderName.textContent =
                    event.chatMessage.participant.display_name;

                const time = document.createElement('span');
                time.className = 'text-on-surface-variant text-xs ml-2';
                time.textContent = new Date(
                    event.chatMessage.created_at
                ).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                sender.appendChild(senderName);
                sender.appendChild(time);

                header.appendChild(sender);

                if (isHost) {
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.className = 'text-error hover:text-error/80 transition-colors text-xs';
                    deleteBtn.onclick = () => deleteChatMessage(event.chatMessage.id);
                    deleteBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">close</span>';
                    header.appendChild(deleteBtn);
                }

                const message = document.createElement('p');
                message.className = 'text-on-surface text-body-md mt-1';
                message.textContent = event.chatMessage.message;

                messageElement.appendChild(header);
                messageElement.appendChild(message);

                chatMessages.appendChild(messageElement);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            })

            .listen('ParticipantMuted', (event) => {
                console.log('Participant mute event:', event);

                const currentParticipantId = Number('{{ $participant->id }}');

                if (event.participant.id !== currentParticipantId) {
                    return;
                }

                const chatInput = document.getElementById('chatInput');
                const chatForm = document.getElementById('chatForm');

                if (event.participant.is_muted) {
                    if (chatInput) {
                        chatInput.disabled = true;
                        chatInput.placeholder = 'You have been muted';
                    }

                    if (chatForm) {
                        const sendButton =
                            chatForm.querySelector('button[type="submit"]');

                        if (sendButton) {
                            sendButton.disabled = true;
                        }
                    }

                    alert('The host has muted you.');
                } else {
                    if (chatInput) {
                        chatInput.disabled = false;
                        chatInput.placeholder = 'Type a message...';
                    }

                    if (chatForm) {
                        const sendButton =
                            chatForm.querySelector('button[type="submit"]');

                        if (sendButton) {
                            sendButton.disabled = false;
                        }
                    }

                    alert('The host has unmuted you.');
                }
            })

            .listen('ChatMessageDeleted', (event) => {
                console.log('Chat message deleted:', event);

                const messageElement = document.getElementById(
                    'chat-message-' + event.messageId
                );

                if (messageElement) {
                    messageElement.remove();
                }
            })

            .listen('ChatStatusUpdated', (event) => {
                console.log('Chat status updated:', event);

                const chatInput =
                    document.getElementById('chatInput');

                const chatForm =
                    document.getElementById('chatForm');

                if (chatInput) {
                    chatInput.disabled = !event.chatEnabled;
                    chatInput.placeholder = event.chatEnabled ?
                        'Type a message...' :
                        'Chat has been disabled';
                }

                if (chatForm) {
                    const sendButton =
                        chatForm.querySelector('button[type="submit"]');

                    if (sendButton) {
                        sendButton.disabled = !event.chatEnabled;
                    }
                }

                const chatToggleText =
                    document.getElementById('chatToggleText');

                if (chatToggleText) {
                    chatToggleText.textContent = event.chatEnabled ?
                        'Disable Chat' :
                        'Enable Chat';
                }

                alert(
                    'The chat has been ' +
                    (event.chatEnabled ? 'enabled' : 'disabled')
                );
            });

        console.log('Subscribed to room.{{ $room->uuid }}');
    }

    setupEcho();
</script>


<script>
    (() => {
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');

        if (!chatForm || !chatInput) {
            return;
        }

        chatForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const message = chatInput.value.trim();

            if (!message) {
                return;
            }

            try {
                const response = await fetch(
                    '/rooms/{{ $room->uuid }}/chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            message: message
                        })
                    }
                );

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 403) {
                        alert(
                            data.message ||
                            'You are not allowed to send messages.'
                        );
                        return;
                    }

                    if (response.status === 429) {
                        alert(
                            'You are sending messages too quickly. Please wait a moment.'
                        );
                        return;
                    }

                    alert(
                        'Something went wrong while sending the message.'
                    );
                    return;
                }

                chatInput.value = '';

            } catch (error) {
                console.error('Chat error:', error);
            }
        });
    })();
</script>

@endsection